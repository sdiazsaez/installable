<?php

namespace Larangular\Installable\Commands;

use Larangular\Installable\CommandTasks\CommandTasks;
use Larangular\Installable\Facades\InstallableConfig;
use Larangular\Installable\InstallableConfig\Migration;
use Larangular\Installable\Support\PublishableGroups;

class InstallableMigrateCommand extends BaseCommand {

    protected $signature   = 'installable:migrate
                            {--provider= : Full Qualify namespace to class implementing CanMigrate }
                            {--operation= : Migrate operation}';
    protected $description = 'Pending description';
    protected $commandTasks;
    private   $installableConfig;
    private   $operation;

    public function __construct(CommandTasks $commandTasks) {
        parent::__construct();
        $this->commandTasks = $commandTasks;
        $this->commandTasks->doNotThrowOnError();
    }

    public function handle() {
        $provider = $this->getSelectedProvider();

        $this->operation = $this->option('operation') ?? '';
        if (!empty($this->operation)) {
            $this->operation = ':' . $this->operation;
        }
        $this->installableConfig = InstallableConfig::config($provider);
        $this->runMigrations($this->installableConfig->getMigrations());
    }

    private function runMigrations(array $migrations) {
        foreach ($migrations as $migration) {
            $this->runMigration($migration);
        }
    }

    private function runMigration(Migration $migration): void {
        $provider = $this->getSelectedProviderInstance();
        $isPublished = $provider->isPublished(PublishableGroups::Migrations, $migration->getName());

        $migrationPath = $isPublished
            ? $migration->getPublishPath()
            : $migration->getLocalPath();

        $name = is_null($migration->getName())
            ? 'global-config'
            : $migration->getName();

        $this->info("Starting migration command: <comment>migrate{$this->operation} --database={$migration->getConnection()} --path={$this->getValidMigrationPath($migrationPath)}</comment> ----- <comment>{$name}</comment>");

        $this->call('migrate' . $this->operation, [
            '--database' => $migration->getConnection(),
            '--path'     => $this->getValidMigrationPath($migrationPath),
        ]);
    }

    private function getValidMigrationPath(string $migrationPath): string {
        return str_replace(base_path(), '', $migrationPath);
    }
}
