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

class InstallCommand extends Command {

    protected $signature   = 'vendor:install {--provider= : Full Qualify namespace to class implementing InstallableServiceProvider }';
    protected $description = 'Pending description';

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
        $this->selectedProvider = $this->option('provider');

        if (!isset($this->selectedProvider)) {
            $this->selectedProvider = $this->selectProvider();
        }

        $this->installer = $this->getInstaller($this->selectedProvider);


        $this->addInstallerValidationTask();
        $this->addMigrationUpdateTask();
        $this->addVendorPublishTask();
        $this->addConfigEditTask();

        $this->addMigrationTask();
        $this->addSeedTask();

        try {
            $this->commandTasks->runTasks();
            $this->line('success');
        } catch (TaskFailed $e) {
            $this->line('');
            $this->error($e->getMessage());
        }
    }

    private function selectProvider(): string {
        $installables = $this->installables->getInstallables();
        return $this->choice(' Which provider would you like to install?:', $installables);
    }

    private function getInstaller(string $selectedProvider): ?Installable {
        $provider = app()->getProvider($selectedProvider);

        $hasInstallable = is_subclass_of($provider, InstallableServiceProvider::class);
        return $hasInstallable
            ? $provider->installer()
            : null;
    }

    public function addInstallerValidationTask(): void {
        $this->commandTasks->addTask('Installer validation', function () {
            return (!is_null($this->installer) && is_subclass_of($this->installer, Installable::class));
        });
    }

    public function addVendorPublishTask(): void {
        if (Instance::hasInterface($this->installer, Publishable::class)) {
            $this->commandTasks->addTask('Vendor publish', function () {
                $response = $this->call('installable:publish', [
                    '--provider' => $this->selectedProvider,
                ]);
                //$response = $this->installer->vendorPublish();
                //$this->line(Artisan::output());
                return $response;
            });
        }
    }

    public function getPublishableAssets(string $providerName): array {
        $providerName = str_replace('\\', '\\\\', $providerName);
        $tags = preg_grep('/' . $providerName . '/i', ServiceProvider::publishableGroups());
        $provider = preg_grep('/' . $providerName . '/i', ServiceProvider::publishableProviders());

        return array_merge(['<comment>None</comment>'],
            preg_filter('/^/', '<comment>Provider: </comment>', Arr::sort($provider)),
            preg_filter('/^/', '<comment>Tag: </comment>', Arr::sort($tags)));
    }

    public function addConfigReadyTask(): void {
        $this->commandTasks->addTask('Is your config ready', function () {
            $response = $this->askWithCompletion('Is your config ready?', [
                'y',
                'n',
            ], 'y');
            return ($response == 'y');
        });
    }

    public function addMigrationTask(): void {
        $this->commandTasks->addTask('Migrate', function () {
            $response = $this->call('installable:migrate', [
                '--provider' => $this->selectedProvider,
            ]);
            return true;
        });
    }

    public function addConfigEditTask(): void {
        $this->commandTasks->addTask('Edit configuration', function () {
            $response = $this->call('installable:config-edit', [
                '--provider' => $this->selectedProvider,
            ]);
            return true;
        });
    }

    private function addMigrationUpdateTask(): void {
        $this->commandTasks->addTask('Update migration', function () {
            $response = $this->call('installable:migration-update');
            return true;
        });
    }

    private function addSeedTask(): void {
        $this->commandTasks->addTask('Seed migrations', function () {
            $response = $this->call('installable:seed', [
                '--provider' => $this->selectedProvider
            ]);
            return true;
        });
    }


}
