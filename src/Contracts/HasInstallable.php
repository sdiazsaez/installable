<?php

namespace Larangular\Installable\Contracts;

interface HasInstallable {

    public function installer(): Installable;

}
