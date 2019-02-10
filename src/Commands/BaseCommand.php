<?php

namespace Larangular\Installable\Commands;

use Illuminate\Console\Command;
use Larangular\Installable\Contracts\Installable;
use Larangular\Installable\Installer\Installables;
use Larangular\Installable\Support\InstallableServiceProvider;

class BaseCommand extends Command {

    protected $selectedProvider;

    public function getSelectedProvider(): string {
        if(is_null($this->selectedProvider)) {
            $this->selectedProvider = $this->option('provider');

            if (!isset($this->selectedProvider)) {
                $this->selectedProvider = $this->selectProvider();
            }
        }
        return $this->selectedProvider;
    }

    public function getSelectedProviderInstance(): InstallableServiceProvider {
        return app()->getProvider($this->selectedProvider);
    }

    private function selectProvider(): string {
        $installables = resolve(Installables::class)->getInstallables();
        return $this->choice(' Which provider would you like to install?:', $installables);
    }

    private function getInstaller(string $selectedProvider): ?Installable {
        $provider = app()->getProvider($selectedProvider);

        $hasInstallable = is_subclass_of($provider, InstallableServiceProvider::class);
        return $hasInstallable ? $provider->installer() : null;
    }

}
