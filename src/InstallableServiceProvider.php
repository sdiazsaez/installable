<?php

namespace Larangular\Installable;

use GreyDev\ConfigExtension\ConfigExtensionProvider;
use Illuminate\Support\ServiceProvider;
use Larangular\Installable\Commands\{InstallableAppInstallCommand,
    InstallableConfigEditCommand,
    InstallableMigrateCommand,
    InstallablePublishCommand,
    InstallableSeedCommand,
    InstallCommand,
    MakeDatabaseCommand,
    MigrationConfigWriteCommand,
    MigrationUpdateCommand};
use Larangular\Installable\InstallableConfig\InstallableConfig;
use Larangular\Installable\InstallableMigration\InstallableMigration;
use Larangular\Installable\Installer\Installables;

class InstallableServiceProvider extends ServiceProvider {

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot() {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                InstallablePublishCommand::class,
                InstallableMigrateCommand::class,
                MigrationUpdateCommand::class,
                InstallableConfigEditCommand::class,
                InstallableSeedCommand::class,
                InstallableAppInstallCommand::class,
                MakeDatabaseCommand::class,
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
        $this->app->register(ConfigExtensionProvider::class);

        $this->mergeConfigFrom(__DIR__ . '/../config/installable.php', 'larangular.installable');

        $this->installablesRegister();
        $this->installableConfigRegister();
        $this->installableMigrationsRegister();
    }

    private function installablesRegister(): void {
        $this->app->singleton(Installables::class, function () {
            return new Installables();
        });
    }

    private function installableConfigRegister(): void {
        $this->app->singleton(InstallableConfig::class, function () {
            return new InstallableConfig();
        });
    }

    private function installableMigrationsRegister(): void {
        $this->app->singleton(InstallableMigration::class, function () {
            return new InstallableMigration();
        });

        $this->app->singleton('installable.migrations', function () {
            return app()[InstallableMigration::class];
        });
    }

    public function provides(): array {
        return [
            Installables::class,
            InstallableMigration::class,
            InstallableConfig::class,
        ];
    }
}
