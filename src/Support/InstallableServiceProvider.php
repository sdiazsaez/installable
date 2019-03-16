<?php

namespace Larangular\Installable\Support;

use \Illuminate\Support\ServiceProvider;
use Larangular\Installable\{Contracts\Installable, InstallableConfig\InstallableEntryConfig, Installer\Installer};
use Illuminate\Support\Str;
use Larangular\Installable\Facades\InstallableMigration;
use Illuminate\Support\Facades\Config;
use Larangular\Installable\Facades\InstallableConfig;


class InstallableServiceProvider extends ServiceProvider {

    private function makeGroupName(?string $group, ?string $name): string {
        if (!is_null($name)) {
            $name = '.' . $name;
        }
        return get_class($this) . '.' . $group . $name;
    }

    protected function publishesType(array $paths, ?string $group, ?string $name = null): void {
        parent::publishes($paths, $this->makeGroupName($group, $name));
    }

    public function isPublished(?string $group, ?string $name): bool {
        $response = true;
        $paths = parent::pathsToPublish(get_class($this), $this->makeGroupName($group, $name));
        foreach ($paths as $from => $to) {
            $response = file_exists($to);
            if (!$response) break;
        }
        return $response;
    }

    public function installer(): Installable {
        return new Installer(get_class($this));
    }

    protected function declareMigration(array $migration) {
        $migrationName = null;
        if(array_has($migration, 'name')) {
            $migrationName = $migration['name'];
        }

        $migration = $this->addMigrationConfig(is_null($migrationName), $migration);
        if(array_key_exists('migrations', $migration)) {
            $this->publishesType([
                $migration['migrations']['local_path'] => $migration['migrations']['publish_path'],
            ], PublishableGroups::Migrations, $migrationName);
        }


        if (array_key_exists('seeds', $migration)) {
            $this->publishesType([
                $migration['seeds']['local_path'] => $migration['seeds']['publish_path'],
            ], PublishableGroups::Seeds, $migrationName);
        }
    }


    public function addMigrationConfig(bool $isGlobal, array $migration): array {
        $key = get_class($this);
        $params = $this->formatMigration($migration);
        if ($isGlobal) {
            InstallableMigration::addGlobalMigrationConfig($key, $params);
        } else {
            InstallableMigration::addMigrationConfig($key, $params);
        }

        return $params;
    }

    private function formatMigration(array $migration): array {
        if (!array_key_exists('migrations', $migration) && array_key_exists('local_path', $migration)) {
            $migration['migrations'] = [
                'local_path'   => $migration['local_path'],
                'publish_path' => @$migration['publish_path'],
            ];

            unset($migration['local_path'], $migration['publish_path']);
        }

        if (array_key_exists('migrations', $migration)) {
            array_set($migration, 'migrations.publish_path', $this->getDatabasePublishPath('migrations', $migration['migrations']));
        }

        if (array_key_exists('seeds', $migration)) {
            array_set($migration, 'seeds.publish_path', $this->getDatabasePublishPath('seeds', $migration['seeds']));
        }

        return $migration;
    }

    private function getDatabasePublishPath(string $prepend, array $paths): string {
        $path = @$paths['publish_path'];
        if (!array_key_exists('publish_path', $paths) || empty($paths['publish_path'])) {
            $path = database_path($prepend . '/' . $this->getPackageAssetsFolderName());
        }

        return $path;
    }

    private function getPackageAssetsFolderName(): string {
        $className = get_class($this);
        $pieces = explode('\\', $className);
        unset($pieces[count($pieces) - 1]);
        return Str::kebab(implode('', $pieces));
    }

}
