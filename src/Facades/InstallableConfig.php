<?php

namespace Larangular\Installable\Facades;

use Illuminate\Support\Facades\Facade;
use Larangular\Installable\InstallableConfig\InstallableConfig as InstallableConfigController;

class InstallableConfig extends Facade {
    protected static function getFacadeAccessor() {
        return InstallableConfigController::class;
    }
}
