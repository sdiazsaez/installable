<?php

namespace Larangular\Installable\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Larangular\Installable\Contracts\HasInstallable;
use Larangular\Installable\Contracts\Installable;
use Larangular\Installable\Contracts\Publishable;
use Larangular\Installable\InstallableMigration\InstallableMigration;
use Larangular\Installable\CommandTasks\CommandTasks;
use Larangular\Installable\Installer\Installables;
use Larangular\Installable\Installer\RunInstallable;
use Larangular\Support\Facades\Instance;

class MigrationUpdateCommand extends BaseCommand {

    protected $signature = 'installable:migration-update';
                            //{--provider= : Full Qualify namespace to class implementing CanMigrate }';
    protected $description = 'Write installable config';

    protected $commandTasks;

    public function __construct(CommandTasks $commandTasks) {
        parent::__construct();
        $this->commandTasks = $commandTasks;
        $this->commandTasks->doNotThrowOnError();
    }

    public function handle() {
        if(app()['installable.migrations']->writeMigrations()){
            $this->line('installable migrations was updated');
        }
    }


}
