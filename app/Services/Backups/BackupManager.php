<?php

namespace App\Services\Backups;

use App\Models\Backup;
use FilesystemIterator;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use PDO;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Throwable;
use ZipArchive;

/**
 * Builds a full backup archive with the database and the uploaded files.
 */
class BackupManager
{
    /**
     * The folder inside the archive that holds the uploaded files.
     */
    private const STORAGE_FOLDER = 'storage/app';

    /**
     * The number of rows grouped into a single insert statement.
     */
    private const INSERT_BATCH_SIZE = 500;

    /**
     * Tables that are never dumped or restored because they hold ephemeral
     * runtime state (queue, cache, sessions) or the backup log itself.
     *
     * @var array<int, string>
     */
    public const PRESERVED_TABLES = [
        'backups',
        'migrations',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'failed_jobs',
        'sessions',
    ];

    /**
     * Create a new manager, optionally overriding the uploaded files root.
     */
    public function __construct(private readonly ?string $root = null) {}

    /**
     * The absolute path that holds the uploaded files.
     */
    public function storageRoot(): string
    {
        return $this->root ?? storage_path('app');
    }

    /**
     * Build the archive for the given record and return its stored path.
     */
    public function build(Backup $backup): string
    {
        $disk = Storage::disk($backup->disk);

        File::ensureDirectoryExists($disk->path(''));

        $path = $this->makePath($backup);

        $temporaryFiles = [];
        $zip = new ZipArchive;

        if ($zip->open($disk->path($path), ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Não foi possível criar o arquivo de backup.');
        }

        try {
            $this->addDatabase($zip, $temporaryFiles);
            $this->addUploadedFiles($zip);
            $this->addManifest($zip);
        } catch (Throwable $exception) {
            $zip->close();
            $disk->delete($path);

            throw $exception;
        }

        $zip->close();

        foreach ($temporaryFiles as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }

        if (! $disk->exists($path)) {
            throw new RuntimeException('O arquivo de backup não foi criado corretamente.');
        }

        return $path;
    }

    /**
     * Build the stored file name for the backup.
     */
    private function makePath(Backup $backup): string
    {
        return sprintf('backup-%d-%s.zip', $backup->getKey(), now()->format('Y-m-d_H-i-s'));
    }

    /**
     * Add a portable SQL dump of the database to the archive.
     *
     * @param  array<int, string>  $temporaryFiles
     */
    private function addDatabase(ZipArchive $zip, array &$temporaryFiles): void
    {
        $connection = DB::connection();

        $file = match ($connection->getDriverName()) {
            'sqlite' => $this->dumpSqlite($connection),
            'mysql', 'mariadb' => $this->dumpMysql($connection),
            default => throw new RuntimeException(sprintf(
                'O backup do banco de dados não é suportado para o driver [%s].',
                $connection->getDriverName(),
            )),
        };

        $temporaryFiles[] = $file;

        $zip->addFile($file, 'database/database.sql');
    }

    /**
     * Write a restorable SQL dump of the MySQL or MariaDB database to a temporary file.
     */
    private function dumpMysql(Connection $connection): string
    {
        $target = $this->temporaryFile();

        $handle = fopen($target, 'w');

        if ($handle === false) {
            throw new RuntimeException('Não foi possível criar o arquivo de dump do banco de dados.');
        }

        try {
            $tables = $this->mysqlObjectNames($connection, 'BASE TABLE');

            fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");

            foreach ($tables as $table) {
                fwrite($handle, $this->mysqlCreateStatement($connection, $table).";\n\n");
            }

            foreach ($tables as $table) {
                $this->writeMysqlTableRows($handle, $connection, $table);
            }

            foreach ($this->mysqlObjectNames($connection, 'VIEW') as $view) {
                fwrite($handle, $this->mysqlCreateStatement($connection, $view).";\n\n");
            }

            fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        } finally {
            fclose($handle);
        }

        return $target;
    }

    /**
     * List the non preserved tables or views of the current database.
     *
     * @return array<int, string>
     */
    private function mysqlObjectNames(Connection $connection, string $type): array
    {
        $objects = $connection->select(
            'SELECT table_name AS name FROM information_schema.tables
             WHERE table_schema = DATABASE()
               AND table_type = ?
               AND table_name NOT IN ('.$this->preservedList().')
             ORDER BY table_name',
            [$type],
        );

        return array_map(static fn (object $object): string => $object->name, $objects);
    }

    /**
     * Read the "CREATE" statement that recreates the given table or view.
     */
    private function mysqlCreateStatement(Connection $connection, string $name): string
    {
        $row = $connection->selectOne(sprintf('SHOW CREATE TABLE %s', $this->quoteMysqlIdentifier($name)));

        $values = $row === null ? [] : array_values((array) $row);

        if (! isset($values[1]) || ! is_string($values[1])) {
            throw new RuntimeException(sprintf('Não foi possível ler a definição de [%s].', $name));
        }

        return $values[1];
    }

    /**
     * Write the insert statements for every row of the given MySQL table.
     *
     * @param  resource  $handle
     */
    private function writeMysqlTableRows($handle, Connection $connection, string $table): void
    {
        $quotedTable = $this->quoteMysqlIdentifier($table);
        $pdo = $connection->getPdo();

        $columns = null;
        $batch = [];

        foreach ($connection->table($table)->cursor() as $row) {
            $values = (array) $row;

            $columns ??= array_keys($values);

            $batch[] = '('.implode(', ', array_map(
                fn (mixed $value): string => $this->quoteValue($pdo, $value),
                $values,
            )).')';

            if (count($batch) >= self::INSERT_BATCH_SIZE) {
                $this->flushMysqlInsert($handle, $quotedTable, $columns, $batch);
            }
        }

        $this->flushMysqlInsert($handle, $quotedTable, $columns, $batch);
    }

    /**
     * Write a batched insert statement.
     *
     * @param  resource  $handle
     * @param  array<int, string>|null  $columns
     * @param  array<int, string>  $batch
     */
    private function flushMysqlInsert($handle, string $quotedTable, ?array $columns, array &$batch): void
    {
        if ($columns === null || $batch === []) {
            $batch = [];

            return;
        }

        $columnList = implode(', ', array_map(
            fn (string $column): string => $this->quoteMysqlIdentifier($column),
            $columns,
        ));

        fwrite($handle, sprintf(
            "INSERT INTO %s (%s) VALUES\n%s;\n\n",
            $quotedTable,
            $columnList,
            implode(",\n", $batch),
        ));

        $batch = [];
    }

    /**
     * Quote a MySQL or MariaDB identifier with backticks.
     */
    private function quoteMysqlIdentifier(string $name): string
    {
        return '`'.str_replace('`', '``', $name).'`';
    }

    /**
     * Build a unique temporary file path for the dump.
     */
    private function temporaryFile(): string
    {
        return rtrim(sys_get_temp_dir(), '\\/').DIRECTORY_SEPARATOR.'backup-'.uniqid('', true).'.sql';
    }

    /**
     * Write a restorable SQL dump of the SQLite database to a temporary file.
     */
    private function dumpSqlite(Connection $connection): string
    {
        $target = $this->temporaryFile();

        $handle = fopen($target, 'w');

        if ($handle === false) {
            throw new RuntimeException('Não foi possível criar o arquivo de dump do banco de dados.');
        }

        $pdo = $connection->getPdo();

        try {
            fwrite($handle, "PRAGMA foreign_keys=OFF;\nBEGIN TRANSACTION;\n");

            $objects = $pdo->query(
                "SELECT sql FROM sqlite_master
                 WHERE sql IS NOT NULL
                   AND name NOT LIKE 'sqlite_%'
                   AND tbl_name NOT IN (".$this->preservedList().")
                 ORDER BY CASE type
                     WHEN 'table' THEN 1
                     WHEN 'index' THEN 2
                     WHEN 'trigger' THEN 3
                     WHEN 'view' THEN 4
                     ELSE 5
                 END, name"
            )->fetchAll(PDO::FETCH_COLUMN);

            foreach ($objects as $statement) {
                fwrite($handle, $statement.";\n");
            }

            foreach ($this->tableNames($pdo) as $table) {
                $this->writeTableRows($handle, $pdo, $table);
            }

            fwrite($handle, "COMMIT;\n");
        } finally {
            fclose($handle);
        }

        return $target;
    }

    /**
     * List the user defined table names of the database.
     *
     * @return array<int, string>
     */
    private function tableNames(PDO $pdo): array
    {
        return $pdo->query(
            "SELECT name FROM sqlite_master
             WHERE type='table'
               AND name NOT LIKE 'sqlite_%'
               AND name NOT IN (".$this->preservedList().')'
        )->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Build a quoted, comma separated list of the preserved table names.
     */
    private function preservedList(): string
    {
        return implode(', ', array_map(
            fn (string $table): string => "'".str_replace("'", "''", $table)."'",
            self::PRESERVED_TABLES,
        ));
    }

    /**
     * Write the insert statements for every row of the given table.
     *
     * @param  resource  $handle
     */
    private function writeTableRows($handle, PDO $pdo, string $table): void
    {
        $quotedTable = '"'.str_replace('"', '""', $table).'"';

        $statement = $pdo->query("SELECT * FROM {$quotedTable}");

        while (($row = $statement->fetch(PDO::FETCH_ASSOC)) !== false) {
            $columns = implode(', ', array_map(
                fn (string $column): string => '"'.str_replace('"', '""', $column).'"',
                array_keys($row),
            ));

            $values = implode(', ', array_map(
                fn (mixed $value): string => $this->quoteValue($pdo, $value),
                array_values($row),
            ));

            fwrite($handle, sprintf("INSERT INTO %s (%s) VALUES (%s);\n", $quotedTable, $columns, $values));
        }
    }

    /**
     * Quote a single value for use inside an insert statement.
     */
    private function quoteValue(PDO $pdo, mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return $pdo->quote((string) $value);
    }

    /**
     * Add every uploaded file under the storage folder to the archive.
     */
    private function addUploadedFiles(ZipArchive $zip): void
    {
        $root = $this->storageRoot();

        if (! is_dir($root)) {
            return;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($files as $file) {
            if (! $file->isFile()) {
                continue;
            }

            $absolute = $file->getPathname();
            $relative = self::STORAGE_FOLDER.'/'.$this->relativePath($root, $absolute);

            $zip->addFile($absolute, $relative);
        }
    }

    /**
     * Resolve a path relative to the given root using forward slashes.
     */
    private function relativePath(string $root, string $path): string
    {
        $relative = str_replace('\\', '/', substr($path, strlen($root)));

        return ltrim($relative, '/');
    }

    /**
     * Add a manifest describing the backup to the archive.
     */
    private function addManifest(ZipArchive $zip): void
    {
        $connection = DB::connection();

        $manifest = [
            'application' => config('app.name'),
            'generated_at' => now()->toIso8601String(),
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'database' => [
                'driver' => $connection->getDriverName(),
                'name' => $connection->getDatabaseName(),
            ],
            'contents' => [
                'database/database.sql',
                self::STORAGE_FOLDER,
            ],
        ];

        $zip->addFromString(
            'manifest.json',
            json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        );
    }
}
