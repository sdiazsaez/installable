<?php

namespace Larangular\Installable\InstallableConfig;

class InstallableEntryConfig {

    private $attributes = [];
    private $rootPath;

    public function __construct(string $path, array $attributes = []) {
        $this->rootPath = $path;
        $this->attributes = $attributes;
    }

    public function getRootPath(?string $key = ''): string {
        if (!empty($key)) {
            $key = '.' . $key;
        }

        return $this->rootPath . $key;
    }

    public function getAttributes(): array {
        return $this->attributes;
    }

    public function getAttribute(string $key) {
        if (!array_has($this->attributes, $key)) {
            return null;
        }
        return array_get($this->attributes, $key);
    }

    public function setAttribute(string $key, $value, ?bool $write = false): void {
        array_set($this->attributes, $key, $value);
        if ($write) {
            $this->saveAttribute($key, $value);
        }
    }

    public function saveAttributes(): bool {
        return $this->saveAttribute('', $this->attributes);
    }

    private function saveAttribute($key, $value): bool {
        $path = $this->getRootPath($key);
        app('config.extended')->save($path, $value);
        config()->set($path, $value);
        return (config($path) === $value);
    }

    public function getMigrations(): array {
        $response = [];
        $migrationsConfig = array_get($this->attributes, 'config');
        foreach ($migrationsConfig as $key => $value) {
            if (!array_has($value, 'migrations')) {
                continue;
            }
            $response[] = $this->getMigration($value);
        }

        if (array_has($this->attributes, 'global-config.migrations')) {
            $globaConfig = array_get($this->attributes, 'global-config');
            $response[] = $this->getMigration($globaConfig);
        }

        return $response;
    }

    private function getMigration(array $migration): Migration {
        return new Migration([
            'connection'   => $migration['connection'],
            'local_path'   => $migration['migrations']['local_path'],
            'publish_path' => $migration['migrations']['publish_path'],
            'name'         => @$migration['name'],
        ]);
    }



    private function getMigrationProperties(?string $migrationName = null): array {
        $attributes = $this->getAttribute('config.' . $migrationName);
        if (is_null($migrationName) || is_null($attributes)) {
            $attributes = $this->getAttribute('global-config');
        }
        return $attributes;
    }

    private function getMigrationProperty(?string $migrationName = null, string $propertyName) {
        $properties = $this->getMigrationProperties($migrationName);
        if (!array_key_exists($propertyName, $properties)) {
            $properties = $this->getMigrationProperties();
        }

        return $properties[$propertyName];
    }

    public function getConnection(?string $migrationName = null): string {
        $properties = $this->getMigrationProperties($migrationName);
        if (!array_key_exists('connection', $properties)) {
            $properties = $this->getMigrationProperties();
        }

        return $properties['connection'];
    }

    public function getName(?string $migrationName = null): string {
        $properties = $this->getMigrationProperties($migrationName);
        return $properties['name'];
    }

    public function getTimestamp(?string $migrationName = null): bool {
        return $this->getMigrationProperty($migrationName, 'timestamp');
    }


    public function getSeeds(): array {
        $response = [];
        $migrationsConfig = array_get($this->attributes, 'config');
        foreach ($migrationsConfig as $key => $value) {
            if (!array_has($value, 'seed_classes')) {
                continue;
            }
            $response = array_merge($response, $value); //$this->getMigration($value);
        }

        if (array_has($this->attributes, 'global-config.seed_classes')) {
            $value = array_get($this->attributes, 'global-config.seed_classes');
            //$response[] = $value; //$this->getMigration($globaConfig);
            $response = array_merge($response, $value);
        }

        return $response;
    }
}
