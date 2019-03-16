<?php

namespace Larangular\Installable\InstallableConfig;

class Migration {

    private $connection;
    private $localPath;
    private $publishPath;
    private $name;

    public function __construct(array $attributes) {
        $this->connection = $attributes['connection'];
        $this->localPath = $attributes['local_path'];
        $this->publishPath = $attributes['publish_path'];
        $this->name = @$attributes['name'];
    }

    public function getName(): ?string {
        return $this->name;
    }

    public function getConnection(): string {
        return $this->connection;
    }

    public function getLocalPath(): string {
        return $this->getValidMigrationPath($this->localPath);
    }

    public function getPublishPath(): string {
        return $this->getValidMigrationPath($this->publishPath);
    }

    private function getValidMigrationPath(string $migrationPath): string {
        return str_replace(base_path(), '', $migrationPath);
    }

}
