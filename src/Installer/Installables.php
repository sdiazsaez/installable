<?php
/**
 * Created by PhpStorm.
 * User: simon
 * Date: 2019-01-15
 */

namespace Larangular\Installable\Installer;

use Larangular\Installable\Contracts\HasInstallable;

class Installables {

    public function getInstallables(): array {
        $declared = $this->getDeclaredClasses();
        $hasInstallable = $this->areImplementing(HasInstallable::class, $declared);
        return $hasInstallable;
    }

    private function getDeclaredClasses(): array {
        $declared = get_declared_classes();
        $deferred = app()->getDeferredServices();
        $providers = array_keys(app()->getLoadedProviders());

        $merged = array_merge($declared, $deferred, $providers);
        $unique = array_unique($merged);

        return $unique;
    }

    private function areImplementing(string $implement, array $classes): array {
        $response = [];
        foreach ($classes as $class) {
            if (is_subclass_of($class, $implement)) {
                $response[] = $class;
            }
        }

        return $response;
    }

}
