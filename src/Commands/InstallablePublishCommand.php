<?php

namespace Larangular\Installable\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Larangular\Installable\Contracts\HasInstallable;
use Larangular\Installable\Contracts\Installable;
use Larangular\Installable\Contracts\Publishable;
use Larangular\Installable\Installer\CommandTasks;
use Larangular\Installable\Installer\Installables;
use Larangular\Installable\Installer\RunInstallable;
use Larangular\Support\Facades\Instance;

class InstallablePublishCommand extends BaseCommand {

    protected $signature = 'installable:publish
                            {--provider= : Full Qualify namespace to class implementing CanMigrate }';
    protected $description = 'Pending description';

    protected $commandTasks;
    private $choices;

    public function __construct(CommandTasks $commandTasks) {
        parent::__construct();
        $this->commandTasks = $commandTasks;
        $this->commandTasks->doNotThrowOnError();
    }

    public function handle() {
        $provider = $this->getSelectedProvider();
        //$provider = $this->argument('provider');
        $this->choices = $this->getPublishableAssets($provider);

        $canPublish = count($this->choices) > 1;
        if(!$canPublish) {
            $this->warn('There are no assets to publish');
            return;
        }

        $publish = $this->choice('Which provider or tag\'s files would you like to publish? (comma-separated)',
                                 $this->choices, null, null, true);

        $selectedAssets = $this->getSelectedAssets($publish);
        if(!empty($selectedAssets)) {
            $this->vendorPublish($selectedAssets);
        }
        /*
        $this->addPublishableAssetsValidation();
        $this->addPublishAssets();

        try {
            $this->commandTasks->runTasks();
            $this->line('success');
        } catch (TaskFailed $e) {
            $this->line('');
            $this->error($e->getMessage());
        }*/
    }

    protected function getPublishableAssets(string $providerName): array {
        $providerName = str_replace('\\', '\\\\', $providerName);
        $tags = preg_grep('/' . $providerName . '/i', ServiceProvider::publishableGroups());
        $provider = preg_grep('/' . $providerName . '/i', ServiceProvider::publishableProviders());

        return array_merge(['<comment>None</comment>'],
                           preg_filter('/^/', '<comment>Provider: </comment>', Arr::sort($provider)),
                           preg_filter('/^/', '<comment>Tag: </comment>', Arr::sort($tags)));
    }

    protected function getSelectedAssets(array $selections): array {
        $response = [];
        foreach($selections as $selection) {
            if($this->noneSelection($selection)) {
                $response = [];
                break;
            }

            $provider = $this->providerSelection($selection);
            if(!empty($provider)) {
                $response['provider'] = $provider;
            }

            $tag = $this->tagSelection($selection);
            if(!empty($tag)) {
                $response['tag'][] = $tag;
            }
        }

        return $response;
    }

    protected function vendorPublish(array $selectedAssets) {
        $arguments = [];
        if(array_key_exists('provider', $selectedAssets)) {
            $arguments['--provider'] = $selectedAssets['provider'];
        }

        if(array_key_exists('tag', $selectedAssets)) {
            $arguments['--tag'] = $selectedAssets['tag'];
        }

        return $this->call('vendor:publish', $arguments);
    }

    private function noneSelection(string $selection): bool {
        $test = '/<comment>None<\/comment>/m';
        preg_match_all($test, $selection, $matches, PREG_SET_ORDER, 0);
        return !empty($matches);
    }

    private function providerSelection(string $selection): string {
        $test = '/<comment>Provider: <\/comment>(.*)/m';
        preg_match_all($test, $selection, $matches, PREG_SET_ORDER, 0);
        return (!empty($matches) && count($matches[0]) == 2) ? $matches[0][1] : '';
    }

    private function tagSelection(string $selection): string {
        $test = '/<comment>Tag: <\/comment>(.*)/m';
        preg_match_all($test, $selection, $matches, PREG_SET_ORDER, 0);
        return (!empty($matches) && count($matches[0]) == 2) ? $matches[0][1] : '';
    }


    protected function addPublishableAssetsValidation() {
        $this->commandTasks->addTask('Assets Validation', function() {
            $canPublish = count($this->choices) > 1;
            if(!$canPublish) {
                $this->warn('There are no assets to publish');
            }

            return $canPublish;
        });
    }

    protected function addPublishAssets() {
        $this->commandTasks->addTask('Publish assets', function(){
            $publish = $this->choice('Which provider or tag\'s files would you like to publish? (comma-separated)',
                                     $this->choices, null, null, true);


        });
    }


}
