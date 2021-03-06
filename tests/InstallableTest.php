<?php

namespace Larangular\Installable\Tests;

use Larangular\Installable\Installer\Installables;
use Larangular\Installable\Tests\Package\HasInstallableServiceProvider;

class InstallableTest extends TestCase {

    public function testGetInstallables() {
        $installables = [
            HasInstallableServiceProvider::class
        ];

        $this->assertTrue(($installables == (new Installables)->getInstallables()));
    }

    public function testGetInstallablesRand() {
        $installables = [
            HasInstallableServiceProvider::class
        ];

        dd((new Installables)->getInstallables());
        $this->assertTrue(($installables == (new Installables)->getInstallables()));
    }

}
