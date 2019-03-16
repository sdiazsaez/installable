<?php

namespace Larangular\Installable\Facades;

use Illuminate\Support\Facades\Facade;
use Larangular\Installable\InstallableMigration\InstallableMigration as InstallableMigrationController;

class InstallableMigration extends Facade {
    protected static function getFacadeAccessor() {
        return InstallableMigrationController::class;
    }
}
