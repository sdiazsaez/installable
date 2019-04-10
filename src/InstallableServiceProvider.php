<?php

namespace Larangular\Installable;

use \GreyDev\ConfigExtension\ConfigExtensionProvider;
use \Illuminate\Support\ServiceProvider;
use Larangular\Installable\Commands\InstallableAppInstallCommand;
use Larangular\Installable\Commands\InstallableConfigEditCommand;
use Larangular\Installable\Commands\InstallableMigrateCommand;
use Larangular\Installable\Commands\InstallablePublishCommand;
use Larangular\Installable\Commands\InstallableSeedCommand;
use Larangular\Installable\Commands\InstallCommand;
use Larangular\Installable\Commands\MigrationConfigWriteCommand;
use Larangular\Installable\Commands\MigrationUpdateCommand;
use Larangular\Installable\Contracts\Installable;
use Larangular\Installable\InstallableConfig\InstallableConfig;
use Larangular\Installable\Installer\Installables;
use Larangular\UFScraper\UFScraperServiceProvider;
use Larangular\UnidadFomento\Commands\UnidadFomento;
use Larangular\Installable\InstallableMigration\InstallableMigration;

class InstallableServiceProvider extends ServiceProvider {

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot() {
        if($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                InstallablePublishCommand::class,
                InstallableMigrateCommand::class,
                MigrationUpdateCommand::class,
                InstallableConfigEditCommand::class,
                InstallableSeedCommand::class,
                InstallableAppInstallCommand::class
            ]);
        }


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

        $this->installablesRegister();
        $this->installableConfigRegister();
        $this->installableMigrationsRegister();
    }

    public function provides(): array {
        return [
            Installables::class,
            InstallableMigration::class,
            InstallableConfig::class,
        ];
    }

    private function installablesRegister(): void {
        $this->app->singleton(Installables::class, function() {
            return new Installables();
        });
    }

    private function installableConfigRegister(): void {
        $this->app->singleton(InstallableConfig::class, function(){
            return new InstallableConfig();
        });
    }
    private function installableMigrationsRegister(): void {
        $this->app->singleton(InstallableMigration::class, function() {
            return new InstallableMigration();
        });

        $this->app->singleton('installable.migrations', function(){
            return app()[InstallableMigration::class];
        });
    }
}
