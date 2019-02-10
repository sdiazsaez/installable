<?php

namespace Larangular\Installable\Support;

use \Illuminate\Support\ServiceProvider;
use Larangular\Installable\{
    Contracts\Installable,
    Installer\Installer
};

use Illuminate\Support\Facades\Config;



class InstallableServiceProvider extends ServiceProvider {

    private function makeGroupName(?string $group, ?string $name): string {
        if(!is_null($name)) {
            $name = '.'.$name;
        }
        return get_class($this) . '.' . $group . $name;
    }

    protected function publishesType(array $paths, ?string $group, ?string $name = null): void {
        parent::publishes($paths, $this->makeGroupName($group, $name));
    }

    public function isPublished(?string $group, ?string $name): bool {
        $response = true;
        $paths = parent::pathsToPublish(get_class($this), $this->makeGroupName($group, $name));
        foreach($paths as $from => $to) {
            $response = file_exists($to);
            if(!$response) break;
        }
        return $response;
    }

    public function installer(): Installable {
        return new Installer(get_class($this));
    }

    protected function declareMigration(array $migration) {
        $migrationName = $migration['name'];
        $this->app['installable.migrations']->addMigrationConfig(get_class($this), $migration);

        $this->publishesType([
            $migration['local_path'] => database_path('migrations/'.$migrationName),
        ], PublishableGroups::Migrations, $migrationName);
    }

}
