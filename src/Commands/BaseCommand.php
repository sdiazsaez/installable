<?php

namespace Larangular\Installable\Commands;

use Illuminate\Console\Command;
use Larangular\Installable\Contracts\Installable;
use Larangular\Installable\Installer\Installables;
use Larangular\Installable\Support\InstallableServiceProvider;

class BaseCommand extends Command {

    protected $selectedProvider;
    private $selectedProviderInstance;

    public function getSelectedProvider(): string {
        $this->selectedProvider = $this->option('provider');
        if(is_null($this->selectedProvider)) {

            if (!isset($this->selectedProvider)) {
                $this->selectedProvider = $this->selectProvider();
            }
        }
        return $this->selectedProvider;
    }

    public function getSelectedProviderInstance(): InstallableServiceProvider {
        if(is_null($this->selectedProviderInstance)) {
            $this->selectedProviderInstance = app()->getProvider($this->selectedProvider);
        }
        return $this->selectedProviderInstance;
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
