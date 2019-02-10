<?php
/**
 * Created by PhpStorm.
 * User: simon
 * Date: 2019-01-14
 */


namespace Larangular\Installable\Tests\Package;

use Illuminate\Support\ServiceProvider;
use Larangular\Installable\Contracts\HasInstallable;
use Larangular\Installable\Contracts\Installable;

class HasInstallableServiceProvider extends ServiceProvider implements HasInstallable {

    public function boot() {

    }

    public function register() {

    }

    public function installer(): Installable {
        return Installer::class;
    }
}
