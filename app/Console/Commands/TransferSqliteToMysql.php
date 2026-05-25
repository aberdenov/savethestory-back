<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TransferSqliteToMysql extends Command
{
    protected $signature = 'db:transfer-sqlite-to-mysql';
    protected $description = 'Transfer data from SQLite to MySQL';

    public function handle()
    {
        DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            $tables = DB::connection('sqlite')
                ->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");

            foreach ($tables as $table) {
                $tableName = $table->name;

                if ($tableName === 'migrations') {
                    $this->info("Skip migrations");
                    continue;
                }

                if (!Schema::connection('mysql')->hasTable($tableName)) {
                    $this->warn("Table {$tableName} does not exist in MySQL, skipped");
                    continue;
                }

                $this->info("Transfer table: {$tableName}");

                DB::connection('mysql')->table($tableName)->truncate();

                DB::connection('sqlite')
                    ->table($tableName)
                    ->orderBy('id')
                    ->chunk(500, function ($rows) use ($tableName) {
                        $data = $rows->map(fn ($row) => (array) $row)->toArray();

                        if (!empty($data)) {
                            DB::connection('mysql')->table($tableName)->insert($data);
                        }
                    });
            }

            $this->info('Transfer completed');
        } finally {
            DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1');
        }

        return self::SUCCESS;
    }
}