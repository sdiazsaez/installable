<?php
/**
 * Created by PhpStorm.
 * User: simon
 * Date: 2019-02-06
 */

namespace Larangular\Installable\InstallableMigration;

class InstallableMigration {

    private $migrations = [];

    public function addGlobalMigrationConfig($key, $config) {
        $this->migrations[$key]['global-config'] = $config;
    }

    public function addMigrationConfig($key, $migration) {
        $this->migrations[$key]['config'][$migration['name']] = $migration;
    }

    public function writeMigrations(): bool {
        $migrationsKey = 'installable.migrations';
        $config = config($migrationsKey);
        $result = array_merge($this->migrations, $config);
        app('config.extended')->save('installable.migrations', $result);
        config()->set($migrationsKey, $result);
        return (config($migrationsKey) == $result);
    }

    private function writeMigration($key, $value) {
        if(!file_exists(config_path('installable.php'))) {
            return;
        }

        if(!app()['config']->has($key)){
            $childKey = $this->getKeyLastChild($key);
            $parentKey = $this->removeKeyLastChild($key);
            $this->writeMigration($parentKey, [$childKey => []]);
        }

        $config = config($key);
        $result = array_merge($value, $config);
    }

    private function getKeyLastChild($key): string {
        $pieces = explode('.', $key);
        return end($pieces);
    }

    private function removeKeyLastChild($key): string {
        return str_replace('.'.$this->getKeyLastChild($key), '', $key);
    }
}
