<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Managers;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobRetryRequested;
use Illuminate\Support\Arr;
use Illuminate\Support\Testing\Fakes\QueueFake;
use ReflectionClass;
use Revoltify\Tenantify\Exceptions\TenantNotFoundException;
use Revoltify\Tenantify\Exceptions\TenantNotFoundInTenantAwareJobException;
use Revoltify\Tenantify\Jobs\NotTenantAware;
use Revoltify\Tenantify\Jobs\TenantAware;
use Revoltify\Tenantify\Models\Contracts\TenantInterface;
use Revoltify\Tenantify\Models\Tenant;
use Throwable;

final readonly class QueueManager
{
    /**
     * QueueManager constructor.
     */
    public function __construct(private \Illuminate\Queue\QueueManager $queue)
    {
        $this->setupPayloadGenerator();
    }

    /**
     * Initialize queue management.
     */
    public function initialize(): void
    {
        $this->registerQueueListeners();
    }

    /**
     * Setup payload generator for queue jobs.
     */
    private function setupPayloadGenerator(): void
    {
        if (! $this->queue instanceof QueueFake) {
            $this->queue->createPayloadUsing(fn (): array => $this->getPayload());
        }
    }

    /**
     * Get payload for queue job.
     *
     * @return array<string, mixed>
     */
    private function getPayload(): array
    {
        /** @var TenantInterface|null $tenant */
        $tenant = tenant();

        if (! $tenant) {
            return [];
        }

        return [
            'tenant_id' => $tenant->getTenantKey(),
        ];
    }

    /**
     * Register queue event listeners.
     */
    private function registerQueueListeners(): void
    {
        resolve(Dispatcher::class)->listen(JobProcessing::class, function (JobProcessing $event): void {
            $this->handleQueueEvent($event);
        });

        resolve(Dispatcher::class)->listen(JobRetryRequested::class, function (JobRetryRequested $event): void {
            $this->handleQueueEvent($event);
        });
    }

    /**
     * Handle queue events.
     */
    private function handleQueueEvent(JobProcessing|JobRetryRequested $event): void
    {
        if ($this->isTenantAware($event)) {

            $this->initializeTenantContext($event);

            return;
        }

        tenantify()->terminate();
    }

    /**
     * Check if the job is tenant aware.
     */
    private function isTenantAware(JobProcessing|JobRetryRequested $event): bool
    {
        $payload = $this->getEventPayload($event);

        try {
            $command = $this->unserializeCommand($payload);
        } catch (Throwable) {
            // Handle deserialization with current tenant context
            if ($tenantId = $this->getTenantIdFromPayload($event)) {
                tenantify()->initialize($tenantId);
            }

            $command = $this->unserializeCommand($payload);
        }

        $job = $this->resolveJob($command);

        return $this->checkJobTenantAwareness($job);
    }

    /**
     * Get event payload.
     *
     * @return array<string, mixed>
     */
    private function getEventPayload(JobProcessing|JobRetryRequested $event): array
    {
        /** @var array<string, mixed> $payload */
        $payload = match (true) {
            $event instanceof JobProcessing => $event->job->payload(),
            $event instanceof JobRetryRequested => $event->payload(),
        };

        return $payload;
    }

    /**
     * Get tenant ID from payload.
     */
    private function getTenantIdFromPayload(JobProcessing|JobRetryRequested $event): int|string|null
    {
        /** @var int|string|null $tenantId */
        $tenantId = Arr::get($this->getEventPayload($event), 'tenant_id');

        return $tenantId;
    }

    /**
     * Unserialize command from payload.
     *
     * @param  array<string, mixed>  $payload
     */
    private function unserializeCommand(array $payload): object
    {
        /** @var array<string, mixed> $data */
        $data = $payload['data'] ?? [];
        /** @var string $command */
        $command = $data['command'] ?? '';

        /** @var object $unserialized */
        $unserialized = unserialize($command);

        return $unserialized;
    }

    /**
     * Resolve job from queueable.
     *
     * @return object
     */
    private function resolveJob(object $queueable)
    {
        /** @var string|null $jobMapping */
        $jobMapping = Arr::get(
            (array) config('tenantify.queue.queueable_to_job', []),
            $queueable::class
        );

        if (! $jobMapping) {
            return $queueable;
        }

        if (method_exists($queueable, $jobMapping)) {
            /** @var object $resolved */
            $resolved = $queueable->{$jobMapping}();

            return $resolved;
        }

        /** @var object $resolvedProperty */
        $resolvedProperty = $queueable->{$jobMapping};

        return $resolvedProperty;
    }

    /**
     * Check if job is tenant aware.
     *
     * @param  object  $job
     */
    private function checkJobTenantAwareness($job): bool
    {
        $reflection = new ReflectionClass($job);
        $jobClass = $reflection->getName();

        // Check interfaces
        if ($reflection->implementsInterface(TenantAware::class)) {
            return true;
        }

        if ($reflection->implementsInterface(NotTenantAware::class)) {
            return false;
        }

        // Check configuration
        if (in_array($jobClass, (array) config('tenantify.queue.tenant_aware_jobs', []))) {
            return true;
        }

        if (in_array($jobClass, (array) config('tenantify.queue.not_tenant_aware_jobs', []))) {
            return false;
        }

        return config('tenantify.queue.tenant_aware_by_default') === true;
    }

    /**
     * Initialize tenant context for the job.
     *
     * @throws TenantNotFoundException
     */
    private function initializeTenantContext(JobProcessing|JobRetryRequested $event): void
    {
        $tenantId = $this->getTenantIdFromPayload($event);

        if (! $tenantId) {
            $this->handleMissingTenant($event, 'No tenant ID set for job');
        }

        $tenant = $this->resolveTenant($tenantId);

        if (! $tenant instanceof TenantInterface) {
            $this->handleMissingTenant($event, 'No tenant found for ID: '.$tenantId);

            return;
        }

        tenantify()->initialize($tenant);
    }

    /**
     * Resolve tenant from ID.
     */
    private function resolveTenant(int|string|null $tenantId): ?TenantInterface
    {
        /** @var class-string<Model> $tenantModel */
        $tenantModel = config('tenantify.models.tenant', Tenant::class);

        /** @var TenantInterface|null $tenant */
        $tenant = $tenantModel::query()->find($tenantId);

        return $tenant;
    }

    /**
     * Handle missing tenant scenario.
     *
     * @throws TenantNotFoundException
     */
    private function handleMissingTenant(JobProcessing|JobRetryRequested $event, string $message): void
    {
        if ($event instanceof JobProcessing) {
            $event->job->delete();
        }

        throw new TenantNotFoundInTenantAwareJobException($message);
    }
}
