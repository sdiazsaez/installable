<?php
/**
 * Created by PhpStorm.
 * User: simon
 * Date: 2019-01-15
 */

namespace Larangular\Installable\Contracts;

interface Publishable extends Installable {

    public function vendorPublish(): bool;

}
