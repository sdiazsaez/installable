<?php
/**
 * Created by PhpStorm.
 * User: simon
 * Date: 2019-01-05
 */

namespace Larangular\Installable\Tests;

use Larangular\Installable\InstallableServiceProvider;
use Larangular\Installable\Tests\Package\HasInstallableServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase {

    protected function setUp()
    {
        parent::setUp();

        //$this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        //$this->artisan('migrate', ['--database' => 'package_test'])->run();
        //$this->loadLaravelMigrations(['--database' => 'package_test']);
        // and other test setup steps you need to perform
    }

    /*
    protected function getEnvironmentSetUp($app) {
    }*/


    protected function getPackageProviders($app) {
        return [
            InstallableServiceProvider::class,
            HasInstallableServiceProvider::class
        ];
    }

}
