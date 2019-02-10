<?php

namespace Larangular\Installable\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Larangular\Installable\Contracts\HasInstallable;
use Larangular\Installable\Contracts\Installable;
use Larangular\Installable\Contracts\Publishable;
use Larangular\Installable\Installer\CommandTasks;
use Larangular\Installable\Installer\Installables;
use Larangular\Installable\Installer\RunInstallable;
use Larangular\Installable\Support\PublishableGroups;
use Larangular\Support\Facades\Instance;

class InstallableMigrateCommand extends BaseCommand {

    protected $signature = 'installable:migrate
                            {--provider= : Full Qualify namespace to class implementing CanMigrate }';
    protected $description = 'Pending description';

    protected $commandTasks;

    public function __construct(CommandTasks $commandTasks) {
        parent::__construct();
        $this->commandTasks = $commandTasks;
        $this->commandTasks->doNotThrowOnError();
    }

    public function handle() {
        $provider = $this->getSelectedProvider();
        $this->updateMigrations();

        $providerMigrations = $this->getProviderMigrations();
        if($this->wantToEdit()) {
            $this->editMigrations($providerMigrations);
        }

        $this->runMigrations($this->getProviderMigrations());
    }

    private function updateMigrations() {
        $this->call('installable:migration-update');
    }

    private function getProviderMigrationsPath() {
        return 'installable.migrations.'.$this->selectedProvider;
    }

    private function getProviderMigrations(): array {
        return config($this->getProviderMigrationsPath());
    }

    private function wantToEdit(): bool {
        $response = $this->askWithCompletion('Do you want to edit the migration config?', [
            'y',
            'n',
        ], 'y');
        return ($response == 'y');
    }

    private function editMigrations($migrations) {
        foreach($migrations as $migration) {
            $response = $this->askForConnection($migration);
            $this->writeConnection($migration['name'], $response);
        }
    }

    private function askForConnection($migration) {
        return $this->choice(' Which connection would you like to use for <comment>'.$migration['name'].'</comment>?', $this->getConnectionsOption());
    }

    private function getConnectionsOption() {
        $connections = config('database.connections');
        return array_keys($connections);
    }

    private function writeConnection(string $migrationName, string $connection) {
        $path = $this->getProviderMigrationsPath().'.'.$migrationName.'.connection';
        app('config.extended')->save($path, $connection);
        config()->set($path, $connection);
    }

    private function runMigrations(array $migrations) {
        $provider = $this->getSelectedProviderInstance();

        foreach($migrations as $migration) {
            $isPublished = $provider->isPublished(PublishableGroups::Migrations, $migration['name']);
            $migrationPath = $this->getValidMigrationPath(($isPublished ? $migration['publish_path'] : $migration['local_path']));

            $this->info("Starting migration <comment>{$migration['name']}</comment>: <comment>{$migration['connection']}</comment> - {$migrationPath}");

            $this->call('migrate:refresh', [
                '--database' => $migration['connection'],
                '--path' => $migrationPath
            ]);
        }
    }

    private function getValidMigrationPath(string $migrationPath): string {
        return str_replace(base_path(), '', $migrationPath);
    }
}
