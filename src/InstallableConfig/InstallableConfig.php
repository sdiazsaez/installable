<?php

namespace Larangular\Installable\InstallableConfig;

class InstallableConfig {

    public function config(string $key): InstallableEntryConfig {
        $configPath = 'installable.migrations.' . $key;
        $attributes = config($configPath);
        return new InstallableEntryConfig($configPath, $attributes);
    }

}
