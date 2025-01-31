<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Managers;

use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobRetryRequested;
use Illuminate\Support\Arr;
use Illuminate\Support\Testing\Fakes\QueueFake;
use Revoltify\Tenantify\Exceptions\TenantNotFoundException;
use Revoltify\Tenantify\Exceptions\TenantNotFoundInTenantAwareJobException;
use Revoltify\Tenantify\Jobs\NotTenantAware;
use Revoltify\Tenantify\Jobs\TenantAware;
use Revoltify\Tenantify\Models\Contracts\TenantInterface;
use Revoltify\Tenantify\Models\Tenant;

class QueueManager
{
    /**
     * @var \Illuminate\Queue\QueueManager
     */
    protected $queue;

    /**
     * QueueManager constructor.
     */
    public function __construct(\Illuminate\Queue\QueueManager $queue)
    {
        $this->queue = $queue;

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
            $this->queue->createPayloadUsing(fn () => $this->getPayload());
        }
    }

    /**
     * Get payload for queue job.
     */
    private function getPayload(): array
    {
        if (! tenant()) {
            return [];
        }

        return [
            'tenant_id' => tenant()->getTenantKey(),
        ];
    }

    /**
     * Register queue event listeners.
     */
    private function registerQueueListeners(): void
    {
        app('events')->listen(JobProcessing::class, function (JobProcessing $event) {
            $this->handleQueueEvent($event);
        });

        app('events')->listen(JobRetryRequested::class, function (JobRetryRequested $event) {
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
        } catch (\Throwable) {
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
     */
    private function getEventPayload(JobProcessing|JobRetryRequested $event): array
    {
        return match (true) {
            $event instanceof JobProcessing => $event->job->payload(),
            $event instanceof JobRetryRequested => $event->payload(),
        };
    }

    /**
     * Get tenant ID from payload.
     */
    private function getTenantIdFromPayload(JobProcessing|JobRetryRequested $event): int|string|null
    {
        return Arr::get($this->getEventPayload($event), 'tenant_id');
    }

    /**
     * Unserialize command from payload.
     */
    private function unserializeCommand(array $payload): object
    {
        return unserialize($payload['data']['command']);
    }

    /**
     * Resolve job from queueable.
     */
    private function resolveJob(object $queueable)
    {
        $jobMapping = Arr::get(
            config('tenantify.queue.queueable_to_job', []),
            $queueable::class
        );

        if (! $jobMapping) {
            return $queueable;
        }

        if (method_exists($queueable, $jobMapping)) {
            return $queueable->{$jobMapping}();
        }

        return $queueable->{$jobMapping};
    }

    /**
     * Check if job is tenant aware.
     */
    private function checkJobTenantAwareness($job): bool
    {
        $reflection = new \ReflectionClass($job);
        $jobClass = $reflection->getName();

        // Check interfaces
        if ($reflection->implementsInterface(TenantAware::class)) {
            return true;
        }

        if ($reflection->implementsInterface(NotTenantAware::class)) {
            return false;
        }

        // Check configuration
        if (in_array($jobClass, config('tenantify.queue.tenant_aware_jobs', []))) {
            return true;
        }

        if (in_array($jobClass, config('tenantify.queue.not_tenant_aware_jobs', []))) {
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

        if (! $tenant) {
            $this->handleMissingTenant($event, "No tenant found for ID: {$tenantId}");
        }

        tenantify()->initialize($tenant);
    }

    /**
     * Resolve tenant from ID.
     */
    private function resolveTenant(int|string|null $tenantId): ?TenantInterface
    {
        $tenantModel = config('tenantify.models.tenant', Tenant::class);

        return $tenantModel::find($tenantId);
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
