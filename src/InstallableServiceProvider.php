<?php

namespace Larangular\Installable;

use \GreyDev\ConfigExtension\ConfigExtensionFacade;
use \GreyDev\ConfigExtension\ConfigExtensionProvider;
use \Illuminate\Support\ServiceProvider;
use Larangular\Installable\Commands\InstallableMigrateCommand;
use Larangular\Installable\Commands\InstallablePublishCommand;
use Larangular\Installable\Commands\InstallCommand;
use Larangular\Installable\Commands\MigrationConfigWriteCommand;
use Larangular\Installable\Commands\MigrationUpdateCommand;
use Larangular\Installable\Installer\Installables;
use Larangular\UFScraper\UFScraperServiceProvider;
use Larangular\UnidadFomento\Commands\UnidadFomento;

class InstallableServiceProvider extends ServiceProvider {

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot() {
        if($this->app->runningInConsole()) {

        }
        $this->commands([
                            InstallCommand::class,
                            InstallablePublishCommand::class,
                            InstallableMigrateCommand::class,
                            MigrationUpdateCommand::class,
                        ]);


        $this->publishes([
            __DIR__ . '/../config/installable.php' => config_path('installable.php'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register() {
        if ($this->app->environment() === 'local') {
            //$this->app->register(ConfigServiceProvider::class);
        }


        $this->app->register(ConfigExtensionProvider::class);
        //$this->app->alias(ConfigExtensionFacade::class, 'config');

        $this->mergeConfigFrom(__DIR__ . '/../config/installable.php', 'larangular.installable');

        $this->app->singleton(Installables::class, function() {
            return new Installables();
        });

        $this->app->singleton('installable.migrations', function(){
            return new InstallableMigration\InstallableMigration();
        });
    }

    public function provides() {
        return [
            Installables::class,
        ];
    }
}
