<?php

namespace Larangular\Installable\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Larangular\Installable\Contracts\HasInstallable;
use Larangular\Installable\Contracts\Installable;
use Larangular\Installable\Contracts\Publishable;
use Larangular\Installable\Facades\InstallableConfig;
use Larangular\Installable\InstallableConfig\Migration;
use Larangular\Installable\CommandTasks\CommandTasks;
use Larangular\Installable\Installer\Installables;
use Larangular\Installable\Installer\RunInstallable;
use Larangular\Installable\Support\PublishableGroups;
use Larangular\Support\Facades\Instance;

class InstallableMigrateCommand extends BaseCommand {

    protected $signature   = 'installable:migrate
                            {--provider= : Full Qualify namespace to class implementing CanMigrate }';
    protected $description = 'Pending description';
    protected $commandTasks;
    private   $installableConfig;

    public function __construct(CommandTasks $commandTasks) {
        parent::__construct();
        $this->commandTasks = $commandTasks;
        $this->commandTasks->doNotThrowOnError();
    }

    public function handle() {
        $provider = $this->getSelectedProvider();

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

        $this->info("Starting migration <comment>{$name}</comment>: <comment>{$migration->getConnection()}</comment> - {$migrationPath}");

        $this->call('migrate:refresh', [
            '--database' => $migration->getConnection(),
            '--path'     => $this->getValidMigrationPath($migrationPath),
        ]);
    }

    private function getValidMigrationPath(string $migrationPath): string {
        return str_replace(base_path(), '', $migrationPath);
    }
}
