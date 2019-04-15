<?php

namespace Larangular\Installable\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;
use Larangular\Installable\Contracts\HasInstallable;
use Larangular\Installable\Contracts\Installable;
use Larangular\Installable\Contracts\Publishable;
use Larangular\Installable\CommandTasks\CommandTasks;
use Larangular\Installable\Installer\Installables;
use Larangular\Installable\Installer\RunInstallable;
use Larangular\Installable\Support\InstallableServiceProvider;
use Larangular\Support\Facades\Instance;

class MakeDatabaseCommand extends InstallCommand {

    protected $signature = 'make:database {--dbname} {connection?}';
    protected $description = 'pending';
    //private $UFController;

    private $installer;
    private $installables;
    private $commandTasks;
    private $selectedProvider;


    public function handle() {
        try {
            $dbname = $this->argument('dbname');
            $connection = $this->getConnection();

            if (!$this->databaseExist($dbname, $connection)) {
                DB::connection($connection)
                  ->select('CREATE DATABASE ' . $dbname);
                $this->info("Database '$dbname' created for '$connection' connection");
            } else {
                $this->info("Database $dbname already exists for $connection connection");
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

    }

    private function getConnection() {
        return $this->hasArgument('connection') && $this->argument('connection')
            ? $this->argument('connection')
            : DB::connection()
                ->getPDO()
                ->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    private function databaseExist(string $name, $connection): bool {
        $db = DB::connection($connection)
                ->select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = " . "'" . $name . "'");
        return empty($db);
    }

}
