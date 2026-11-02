<?php

namespace Plugins\Analytics\Console;

use Illuminate\Console\Command;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Plugins\Analytics\Support\AnalyticsDatabase;
use Throwable;

/**
 * Prepares the dedicated analytics database: runs the plugin migrations on
 * the configured connection and optionally copies the rows that already live
 * on the application database.
 */
class DatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytics:database
                            {--copy : Copy the analytics rows from the default connection}
                            {--fresh : Drop the analytics tables before migrating}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate (and optionally copy the data of) the dedicated analytics database';

    /**
     * The tables managed by the plugin.
     *
     * @var list<string>
     */
    private const TABLES = [
        'analytics_snapshots',
        'analytics_records',
        'analytics_afl_records',
    ];

    /**
     * Execute the console command.
     */
    public function handle(Migrator $migrator): int
    {
        $connection = AnalyticsDatabase::connection();

        if ($connection === null) {
            $this->error('Defina ANALYTICS_DB_CONNECTION no .env para usar o banco dedicado.');

            return self::FAILURE;
        }

        try {
            DB::connection($connection)->getPdo();
        } catch (Throwable $exception) {
            $this->error("Não foi possível conectar na conexão [{$connection}]: {$exception->getMessage()}");

            return self::FAILURE;
        }

        $migrator->setConnection($connection);

        if (! $migrator->repositoryExists()) {
            $migrator->getRepository()->createRepository();
        }

        if ($this->option('fresh')) {
            foreach (self::TABLES as $table) {
                AnalyticsDatabase::schema()->dropIfExists($table);
            }

            DB::connection($connection)
                ->table('migrations')
                ->where('migration', 'like', '%_create_analytics_%')
                ->delete();

            $this->components->info('Tabelas de analytics removidas.');
        }

        $path = base_path('plugins/Analytics/Database/Migrations');

        $migrator->run([$path], ['pretend' => false]);

        $this->components->info("Migrations executadas na conexão [{$connection}].");

        if ($this->option('copy')) {
            $this->copyFromDefaultConnection($connection);
        }

        return self::SUCCESS;
    }

    /**
     * Copy the analytics rows from the application database.
     */
    private function copyFromDefaultConnection(string $connection): void
    {
        $source = DB::connection();
        $target = DB::connection($connection);

        foreach (self::TABLES as $table) {
            if (! Schema::connection(null)->hasTable($table) || ! AnalyticsDatabase::schema()->hasTable($table)) {
                $this->line("Ignorando {$table}: tabela ausente na origem ou no destino.");

                continue;
            }

            $target->table($table)->truncate();

            $count = 0;

            $source->table($table)->orderBy('id')->chunk(500, function ($rows) use ($target, $table, &$count): void {
                $data = $rows->map(fn ($row): array => (array) $row)->all();

                $target->table($table)->insert($data);

                $count += count($data);
            });

            if ($target->getDriverName() === 'pgsql') {
                $target->statement(
                    "SELECT setval(pg_get_serial_sequence('{$table}', 'id'), COALESCE((SELECT MAX(id) FROM {$table}), 1))",
                );
            }

            $this->components->info("{$table}: {$count} registro(s) copiados.");
        }
    }
}
