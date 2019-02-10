<?php
/**
 * Created by PhpStorm.
 * User: simon
 * Date: 2019-01-14
 */


namespace Larangular\Installable\Tests\Package;

use Larangular\Installable\Contracts\Installable;

class Installer implements Installable {

    public function install(): bool {
        return true;
    }

}
