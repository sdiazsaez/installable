<?php
/**
 * Created by PhpStorm.
 * User: simon
 * Date: 2019-01-15
 */

namespace Larangular\Installable\Installer;

use Illuminate\Support\Facades\Artisan;
use Larangular\Installable\Contracts\Publishable;

class Installer implements Publishable {

    private $providerName;

    public function __construct(string $providerName) {
        $this->providerName = $providerName;
    }

    public function vendorPublish(): bool {
        return Artisan::call('installable:publish', [
                'provider' => $this->providerName,
            ]) === 0;
    }
}
