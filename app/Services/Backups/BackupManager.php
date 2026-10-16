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

        if ($connection->getDriverName() !== 'sqlite') {
            throw new RuntimeException(sprintf(
                'O backup do banco de dados não é suportado para o driver [%s].',
                $connection->getDriverName(),
            ));
        }

        $file = $this->dumpSqlite($connection);

        $temporaryFiles[] = $file;

        $zip->addFile($file, 'database/database.sql');
    }

    /**
     * Write a restorable SQL dump of the SQLite database to a temporary file.
     */
    private function dumpSqlite(Connection $connection): string
    {
        $target = rtrim(sys_get_temp_dir(), '\\/').DIRECTORY_SEPARATOR.'backup-'.uniqid('', true).'.sql';

        $handle = fopen($target, 'w');

        if ($handle === false) {
            throw new RuntimeException('Não foi possível criar o arquivo de dump do banco de dados.');
        }

        $pdo = $connection->getPdo();

        try {
            fwrite($handle, "PRAGMA foreign_keys=OFF;\nBEGIN TRANSACTION;\n");

            $objects = $pdo->query(
                "SELECT sql FROM sqlite_master
                 WHERE sql IS NOT NULL AND name NOT LIKE 'sqlite_%'
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
            "SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'"
        )->fetchAll(PDO::FETCH_COLUMN);
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
        $root = storage_path('app');

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
