<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportSchema extends Command
{
    protected $signature = 'db:import-schema {--fresh : Drop existing tables before importing}';
    protected $description = 'Import database/sql/schema.sql into the database';

    public function handle(): int
    {
        $path = database_path('sql/schema.sql');

        if (! file_exists($path)) {
            $this->error("Schema file not found: {$path}");
            return self::FAILURE;
        }

        if ($this->option('fresh')) {
            $this->dropTables();
        }

        $sql = file_get_contents($path);
        $sql = preg_replace('/--[^\n]*\n?/', '', $sql);
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            fn ($s) => $s !== ''
        );

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($statements as $statement) {
            DB::statement($statement);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->info('Schema imported successfully.');
        return self::SUCCESS;
    }

    private function dropTables(): void
    {
        $tables = ['issue_label', 'comments', 'issues', 'labels', 'sprints', 'projects'];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach ($tables as $table) {
            DB::statement("DROP TABLE IF EXISTS `{$table}`");
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->info('Existing tables dropped.');
    }
}
