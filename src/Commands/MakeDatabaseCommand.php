<?php

namespace Larangular\Installable\Commands;

use Illuminate\Support\Facades\DB;
use PDO;

class MakeDatabaseCommand extends InstallCommand {

    protected $signature = 'make:database {dbname} {connection?}';
    protected $description = 'Create database';

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
        return !empty($db);
    }

}
