<?php

namespace App\Services\Backups;

use App\Enums\BackupStatus;
use App\Models\Backup;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use PDO;
use RuntimeException;
use Throwable;
use ZipArchive;

/**
 * Restores the database and the uploaded files from a backup archive.
 */
class BackupRestorer
{
    /**
     * The archive entry that holds the database dump.
     */
    private const DUMP_ENTRY = 'database/database.sql';

    /**
     * The folder inside the archive that holds the uploaded files.
     */
    private const STORAGE_FOLDER = 'storage/app';

    public function __construct(private BackupManager $manager) {}

    /**
     * Restore the given backup, keeping a safety copy of the current state.
     */
    public function restore(Backup $backup): void
    {
        $disk = Storage::disk($backup->disk);

        if ($backup->path === null || ! $disk->exists($backup->path)) {
            throw new RuntimeException('O arquivo de backup não foi encontrado.');
        }

        $zip = new ZipArchive;

        if ($zip->open($disk->path($backup->path)) !== true) {
            throw new RuntimeException('Não foi possível abrir o arquivo de backup.');
        }

        try {
            $this->createSafetyBackup($backup);

            Artisan::call('down', ['--retry' => 30]);

            $this->restoreDatabase($zip);
            $this->restoreFiles($zip);
        } finally {
            $zip->close();

            Artisan::call('up');
        }
    }

    /**
     * Store a copy of the current state before it is overwritten.
     */
    private function createSafetyBackup(Backup $backup): void
    {
        $safety = Backup::query()->create([
            'user_id' => $backup->user_id,
            'disk' => $backup->disk,
            'status' => BackupStatus::Pending,
        ]);

        $safety->markRunning();

        try {
            $path = $this->manager->build($safety);

            $safety->markCompleted($path, (int) Storage::disk($safety->disk)->size($path));
        } catch (Throwable $exception) {
            $safety->markFailed($exception->getMessage());

            throw new RuntimeException('Não foi possível criar o backup de segurança antes de restaurar.', 0, $exception);
        }
    }

    /**
     * Replace the application tables with the ones stored in the dump.
     */
    private function restoreDatabase(ZipArchive $zip): void
    {
        $sql = $zip->getFromName(self::DUMP_ENTRY);

        if ($sql === false) {
            throw new RuntimeException('O backup não contém o dump do banco de dados.');
        }

        $connection = DB::connection();

        if ($connection->getDriverName() !== 'sqlite') {
            throw new RuntimeException(sprintf(
                'A restauração do banco de dados não é suportada para o driver [%s].',
                $connection->getDriverName(),
            ));
        }

        $pdo = $connection->getPdo();

        $pdo->exec('PRAGMA foreign_keys = OFF');

        try {
            foreach ($this->restorableObjects($pdo) as [$type, $name]) {
                $keyword = $type === 'view' ? 'VIEW' : 'TABLE';

                $pdo->exec(sprintf('DROP %s IF EXISTS "%s"', $keyword, str_replace('"', '""', $name)));
            }

            $connection->unprepared($sql);
        } finally {
            $pdo->exec('PRAGMA foreign_keys = ON');
        }
    }

    /**
     * List the tables and views that the restore is allowed to replace.
     *
     * @return array<int, array{0: string, 1: string}>
     */
    private function restorableObjects(PDO $pdo): array
    {
        $preserved = implode(', ', array_map(
            fn (string $table): string => "'".str_replace("'", "''", $table)."'",
            BackupManager::PRESERVED_TABLES,
        ));

        return $pdo->query(
            "SELECT type, name FROM sqlite_master
             WHERE type IN ('table', 'view')
               AND name NOT LIKE 'sqlite_%'
               AND name NOT IN ({$preserved})
             ORDER BY CASE type WHEN 'view' THEN 1 ELSE 2 END"
        )->fetchAll(PDO::FETCH_NUM);
    }

    /**
     * Replace the uploaded files with the ones stored in the archive.
     */
    private function restoreFiles(ZipArchive $zip): void
    {
        $root = $this->manager->storageRoot();

        File::deleteDirectory($root);
        File::ensureDirectoryExists($root);

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $name = $zip->getNameIndex($index);

            if (! is_string($name) || ! str_starts_with($name, self::STORAGE_FOLDER.'/')) {
                continue;
            }

            $relative = substr($name, strlen(self::STORAGE_FOLDER.'/'));

            if ($relative === '' || str_ends_with($name, '/')) {
                continue;
            }

            if ($this->isUnsafePath($relative)) {
                throw new RuntimeException(sprintf('O backup contém um caminho de arquivo inválido [%s].', $relative));
            }

            $contents = $zip->getFromIndex($index);

            if ($contents === false) {
                continue;
            }

            $target = $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);

            File::ensureDirectoryExists(dirname($target));
            File::put($target, $contents);
        }
    }

    /**
     * Determine whether an archive entry points outside the storage folder.
     */
    private function isUnsafePath(string $path): bool
    {
        return str_contains($path, '..') || str_starts_with($path, '/');
    }
}
