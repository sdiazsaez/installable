<?php

namespace Larangular\Installable\Installer;

use Larangular\Installable\Contracts\HasInstallable;
use Larangular\Installable\Contracts\Installable;
use Larangular\Installable\Contracts\Publishable;
use Larangular\Installable\CommandTasks\CommandTasks;

class RunInstallable {

    private $installer;

    public function __construct(CommandTasks $commandTasks) {
        $this->commandTasks = $commandTasks;
    }

    public function install(HasInstallable $hasInstallable) {
        $this->installer = $this->getInstallable($hasInstallable);
        $this->addVendorPublishTask();
        //->install();


    }

    private function runTasks() {
        try {
            $this->commandTasks->runTasks();
            $this->outputSuccessMessage(array_get($results ?? [], 'id', ''));
        } catch (TaskFailed $e) {
            $this->line('');
            $this->error($e->getMessage());
        }
    }

    public function vendorPublish() {
        if (is_subclass_of($this->installer, Publishable::class)) {
            $this->commandTasks->addTask('Vendor publish', function () {
                $this->installer->vendorPublish();
            });
        }
    }

    private function getInstallable(HasInstallable $hasInstallable): Installable {
        return $hasInstallable->installer();
    }

}
