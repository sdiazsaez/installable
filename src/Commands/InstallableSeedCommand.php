<?php

namespace Larangular\Installable\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Larangular\Installable\Contracts\HasInstallable;
use Larangular\Installable\Contracts\Installable;
use Larangular\Installable\Contracts\Publishable;
use Larangular\Installable\Facades\InstallableConfig;
use Larangular\Installable\InstallableConfig\Migration;
use Larangular\Installable\CommandTasks\CommandTasks;
use Larangular\Installable\Installer\Installables;
use Larangular\Installable\Installer\RunInstallable;
use Larangular\Installable\Support\PublishableGroups;
use Larangular\Support\Facades\Instance;

class InstallableSeedCommand extends BaseCommand {

    protected $signature   = 'installable:seed
                            {--provider= : Full Qualify namespace to class implementing CanMigrate }';
    protected $description = 'Pending description';
    protected $commandTasks;
    private   $installableConfig;

    public function __construct(CommandTasks $commandTasks) {
        parent::__construct();
        $this->commandTasks = $commandTasks;
        $this->commandTasks->doNotThrowOnError();
    }

    public function handle() {
        $provider = $this->getSelectedProvider();

        $this->installableConfig = InstallableConfig::config($provider);
        $this->runSeeds($this->installableConfig->getSeeds());
    }

    private function runSeeds(array $seeds) {
        foreach ($seeds as $seed) {
            $this->runSeed($seed);
        }
    }

    private function runSeed(string $seed): void {
        $this->info("Starting seed <comment>name</comment>: <comment>connection</comment> - path - {$seed}");
        $this->call('db:seed', [
            //'--database' => $migration->getConnection(),
            '--class' => $seed,
        ]);
    }

}
