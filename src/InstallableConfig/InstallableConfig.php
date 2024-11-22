<?php

namespace Larangular\Installable\InstallableConfig;

class InstallableConfig {

    public function config(string $key): InstallableEntryConfig {
        $configPath = 'installable.migrations.' . $key;
        $attributes = config($configPath);

        if(!is_array($attributes)) {
            dump([
                $key,
                $configPath,
                $attributes,
                //debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 3),
            ]);
            $trace = debug_backtrace();
            foreach ($trace as $frame) {
                if (isset($frame['function'])) {
                    echo $frame['function'] . '() called at ' . $frame['file'] . ':' . $frame['line'] . "\n";
                }
            }

        }

        return new InstallableEntryConfig($configPath, $attributes);
    }

}
