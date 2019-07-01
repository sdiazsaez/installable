<?php

namespace Larangular\Installable\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Larangular\Installable\Contracts\HasInstallable;
use Larangular\Installable\Contracts\Installable;
use Larangular\Installable\Contracts\Publishable;
use Larangular\Installable\Facades\InstallableConfig;
use Larangular\Installable\CommandTasks\CommandTasks;
use Larangular\Installable\Installer\Installables;
use Larangular\Installable\Installer\RunInstallable;
use Larangular\Installable\Support\PublishableGroups;
use Larangular\Support\Facades\Instance;

class InstallableConfigEditCommand extends BaseCommand {

    protected $signature   = 'installable:config-edit
                            {--provider= : Full Qualify namespace to class implementing Installable }';
    protected $description = 'Edit installable config';
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
        if ($this->wantToEdit()) {
            $groups = $this->getEditableGroups();
            if (count($groups) > 0 && $this->editMigrations($groups)) {
                $this->line('installable migrations was updated');
            }else{
                $this->line('error');
            }
        }

    }

    private function wantToEdit(): bool {
        $response = $this->askWithCompletion('Do you want to edit the installable config?', [
            'y',
            'n',
        ], 'y');
        return ($response == 'y');
    }

    private function editMigrations(array $migrations): bool {
        foreach ($migrations as $migration) {
            $response = $this->askForConnection($migration);
            $this->installableConfig->setAttribute(array_get($migration, 'connection.path'), $response);
        }

        return $this->installableConfig->saveAttributes();
    }

    private function askForConnection($migration) {
        return $this->choice(' Which connection would you like to use for <comment>' . array_get($migration,
                'connection.migration-key') . '</comment>?', $this->getConnectionsOption());
    }

    private function getConnectionsOption() {
        $connections = config('database.connections');
        return array_keys($connections);
    }

    private function getEditableGroups(): array {
        $response = [];

        $group = $this->provideEditableGroup('global-config');
        if (!empty($group)) {
            $response[] = $group;
        }

        $config = $this->installableConfig->getAttribute('config');
        if (!is_null($config)) {
            foreach ($config as $key => $value) {
                $group = $this->provideEditableGroup('config.' . $key);
                if (!empty($group)) {
                    $response[] = $group;
                }
            }
        }
        return $response;
    }

    private function provideEditableGroup(string $path): array {
        $attribute = $this->installableConfig->getAttribute($path);
        if (is_null($attribute)) {
            return [];
        }

        return $this->makeEditableGroup($attribute, $path);
    }

    private function makeEditableGroup(array $migration, string $path): array {
        $response = [];
        $pathPieces = explode('.', $path);
        $migrationKeyName = $pathPieces[count($pathPieces) - 1];

        if (array_key_exists('migrations', $migration)) {
            $response['connection'] = [
                'migration-key' => $migrationKeyName,
                'path'          => $path . '.connection',
                'value'         => '',
            ];
        }
        //TODO implement name
        return $response;
    }

}
