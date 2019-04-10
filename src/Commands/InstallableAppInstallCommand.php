<?php

namespace Larangular\Installable\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;
use Larangular\Installable\Contracts\HasInstallable;
use Larangular\Installable\Contracts\Installable;
use Larangular\Installable\Contracts\Publishable;
use Larangular\Installable\Installer\CommandTasks;
use Larangular\Installable\Installer\Installables;
use Larangular\Installable\Installer\RunInstallable;
use Larangular\Installable\Support\InstallableServiceProvider;
use Larangular\Support\Facades\Instance;

class InstallableAppInstallCommand extends InstallCommand {

    protected $signature   = 'installable:app-install';
    protected $description = 'Install every installable in config';
    //private $UFController;

    private $installer;
    private $installables;
    private $commandTasks;
    private $selectedProvider;

    public function __construct(Installables $installables, RunInstallable $runInstallable,
        CommandTasks $commandTasks) {
        parent::__construct();
        $this->installables = $installables;
        $this->runInstallable = $runInstallable;
        $this->commandTasks = $commandTasks;

        $this->commandTasks->doNotThrowOnError();
    }

    public function handle() {
        $this->commandTasks->setOutput($this->output);
        $installables = $this->installables->getInstallables();

        foreach($installables as $installable) {
            $this->silentInstall($installable);
        }

    }

    private function silentInstall($installable): void {
        $this->selectedProvider = $installable;
        $this->installer = $this->getInstaller($this->selectedProvider);
        $this->addMigrationTask();
        $this->addSeedTask();

        try {
            $this->commandTasks->runTasks();
            $this->line('success');
        } catch (TaskFailed $e) {
            $this->line('task failed');
            $this->error($e->getMessage());
        }
    }

}
