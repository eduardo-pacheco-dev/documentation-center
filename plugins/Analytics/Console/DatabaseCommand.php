<?php

namespace Plugins\Analytics\Console;

use Illuminate\Console\Command;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Facades\DB;
use Plugins\Analytics\Models\AnalyticsAflRecord;
use Plugins\Analytics\Models\AnalyticsImport;
use Plugins\Analytics\Models\AnalyticsRecord;
use Plugins\Analytics\Models\AnalyticsSnapshot;
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
        'analytics_snapshots' => AnalyticsSnapshot::class,
        'analytics_records' => AnalyticsRecord::class,
        'analytics_afl_records' => AnalyticsAflRecord::class,
        'analytics_imports' => AnalyticsImport::class,
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

        $defaultConnection = (string) config('database.default');

        $migrator->usingConnection($connection, function () use ($migrator): void {
            if (! $migrator->repositoryExists()) {
                $migrator->getRepository()->createRepository();
            }
        });

        if ($this->option('fresh')) {
            foreach (array_keys(self::TABLES) as $table) {
                AnalyticsDatabase::schema()->dropIfExists($table);
            }

            DB::connection($connection)
                ->table('migrations')
                ->where('migration', 'like', '%_create_analytics_%')
                ->delete();

            $this->components->info('Tabelas de analytics removidas.');
        }

        $tablesExist = collect(array_keys(self::TABLES))
            ->every(fn (string $table): bool => AnalyticsDatabase::schema()->hasTable($table));

        if ($this->option('fresh') || ! $tablesExist) {
            $path = base_path('plugins/Analytics/Database/Migrations');

            $migrator->usingConnection($connection, function () use ($migrator, $path): void {
                $migrator->run([$path], ['pretend' => false]);
            });

            $this->components->info("Migrations executadas na conexão [{$connection}].");
        } else {
            $this->components->info('Tabelas já existem; migrations ignoradas.');
        }

        if ($this->option('copy')) {
            $this->copyFromDefaultConnection($connection, $defaultConnection);
        }

        return self::SUCCESS;
    }

    /**
     * Copy the analytics rows from the application database using Eloquent.
     */
    private function copyFromDefaultConnection(string $connection, string $default): void
    {
        $targetName = $connection;

        foreach (self::TABLES as $table => $model) {
            $source = $model::on($default);
            $target = $model::on($targetName);

            if (! $source->getModel()->getConnection()->getSchemaBuilder()->hasTable($table)
                || ! $target->getModel()->getConnection()->getSchemaBuilder()->hasTable($table)) {
                $this->line("Ignorando {$table}: tabela ausente na origem ou no destino.");

                continue;
            }

            $target->truncate();

            $count = 0;

            $source->orderBy('id')->chunkById(500, function ($rows) use ($target, &$count): void {
                $data = $rows->map(fn ($row): array => $row->getAttributes())->all();

                $target->insert($data);

                $count += count($data);
            });

            if ($target->getModel()->getConnection()->getDriverName() === 'pgsql') {
                $target->getModel()->getConnection()->statement(
                    "SELECT setval(pg_get_serial_sequence('{$table}', 'id'), COALESCE((SELECT MAX(id) FROM {$table}), 1))",
                );
            }

            $this->components->info("{$table}: {$count} registro(s) copiados.");
        }
    }
}
