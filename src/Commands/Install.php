<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Support\Facades\Process;

class Install extends Command implements Isolatable
{
    /**
     * The command signature and description.
     */
    public function configure(): void
    {
        $this->setName('tenantify:install')
            ->setDescription('Install Tenantify.');
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->installConfig();
        $this->installMigrations();

        $this->components->success('✨️ Tenantify successfully installed.');

        if (! $this->option('no-interaction')) {
            $this->askForSupport();
        }

        return self::SUCCESS;
    }

    /**
     * Install the configuration file.
     */
    protected function installConfig(): void
    {
        $this->publishStep(
            name: 'Publishing config file',
            tag: 'config',
            file: 'config/tenantify.php',
            newLineBefore: true
        );
    }

    /**
     * Install the migration files.
     */
    protected function installMigrations(): void
    {
        $migrationFiles = [
            'database/migrations/0000_01_01_000000_create_tenants_table.php',
            'database/migrations/0000_01_01_000001_create_domains_table.php',
            'database/migrations/0001_01_01_000001_add_tenant_id_to_sessions_table.php',
        ];

        $this->publishStep(
            name: 'Publishing migrations',
            tag: 'migrations',
            files: $migrationFiles,
            warning: 'Migrations already exist'
        );
    }

    /**
     * Execute a publication step.
     */
    protected function publishStep(
        string $name,
        ?string $tag = null,
        ?string $file = null,
        ?array $files = null,
        ?string $warning = null,
        bool $newLineBefore = false,
        bool $newLineAfter = false,
    ): void {
        $name = $this->formatStepName($name, $file);

        if ($this->shouldSkipPublishing($file, $files)) {
            $this->components->warn($warning ?? $this->getExistsWarning($file));

            return;
        }

        $this->handleLineSpacing($newLineBefore);

        $this->components->task($name, fn () => $this->publishFiles($tag));

        $this->displayPublishedFiles($files);

        $this->handleLineSpacing($newLineAfter);
    }

    /**
     * Format the step name with file information.
     */
    private function formatStepName(string $name, ?string $file): string
    {
        return $file !== null ? "$name [$file]" : $name;
    }

    /**
     * Check if files already exist and publishing should be skipped.
     */
    private function shouldSkipPublishing(?string $file, ?array $files): bool
    {
        if ($file !== null) {
            return file_exists(base_path($file));
        }

        if ($files !== null) {
            return collect($files)
                ->contains(fn (string $file) => file_exists(base_path($file)));
        }

        return false;
    }

    /**
     * Get the warning message for existing files.
     */
    private function getExistsWarning(?string $file): string
    {
        return $file !== null
        ? "File [$file] already exists."
        : 'Files already exist.';
    }

    /**
     * Handle line spacing before or after output.
     */
    private function handleLineSpacing(bool $addLine): void
    {
        if ($addLine) {
            $this->newLine();
        }
    }

    /**
     * Publish files using the vendor:publish command.
     */
    private function publishFiles(?string $tag): bool
    {
        if ($tag === null) {
            return true;
        }

        return $this->callSilent('vendor:publish', [
            '--provider' => 'Revoltify\Tenantify\TenantifyServiceProvider',
            '--tag' => $tag,
        ]) === self::SUCCESS;
    }

    /**
     * Display the list of published files.
     */
    private function displayPublishedFiles(?array $files): void
    {
        if ($files !== null) {
            $this->components->bulletList(
                collect($files)->map(fn (string $file) => "[$file]")->toArray()
            );
        }
    }

    /**
     * Ask for GitHub support and open browser if accepted.
     */
    protected function askForSupport(): void
    {
        if (! $this->components->confirm('Would you like to show your support by starring the project on GitHub?', true)) {
            return;
        }

        $commands = [
            'Darwin' => 'open',
            'Windows' => 'start',
            'Linux' => 'xdg-open',
        ];

        $command = $commands[PHP_OS_FAMILY] ?? null;

        if ($command === null) {
            return;
        }

        Process::run([$command, 'https://github.com/revoltify/tenantify']);
    }
}
