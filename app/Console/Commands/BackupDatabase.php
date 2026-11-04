<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

/**
 * Backup database ke storage/app/backups (gzip), simpan N hari terakhir.
 * MySQL memakai mysqldump; SQLite cukup disalin.
 */
class BackupDatabase extends Command
{
    protected $signature = 'store:backup-db {--keep= : Jumlah hari backup yang disimpan}';

    protected $description = 'Backup database harian dan hapus backup lama';

    public function handle(): int
    {
        $dir = storage_path('app/backups');
        File::ensureDirectoryExists($dir);

        $connection = config('database.default');
        $cfg = config("database.connections.{$connection}");
        $stamp = now()->format('Ymd-His');

        try {
            if ($cfg['driver'] === 'sqlite') {
                $target = "{$dir}/db-{$stamp}.sqlite.gz";
                file_put_contents($target, gzencode(file_get_contents($cfg['database']), 6));
            } elseif (in_array($cfg['driver'], ['mysql', 'mariadb'], true)) {
                $sqlFile = "{$dir}/db-{$stamp}.sql";
                $process = new Process([
                    config('store.mysqldump_path', 'mysqldump'),
                    '--host=' . $cfg['host'],
                    '--port=' . $cfg['port'],
                    '--user=' . $cfg['username'],
                    '--single-transaction',
                    '--routines',
                    '--no-tablespaces',
                    '--result-file=' . $sqlFile,
                    $cfg['database'],
                ], null, ['MYSQL_PWD' => (string) $cfg['password']]);
                $process->setTimeout(600)->mustRun();

                $target = $sqlFile . '.gz';
                file_put_contents($target, gzencode(file_get_contents($sqlFile), 6));
                unlink($sqlFile);
            } else {
                $this->error("Driver {$cfg['driver']} belum didukung.");

                return self::FAILURE;
            }
        } catch (\Throwable $e) {
            report($e);
            $this->error('Backup gagal: ' . $e->getMessage());

            return self::FAILURE;
        }

        $keep = (int) ($this->option('keep') ?? config('store.backup_keep_days', 14));
        $deleted = 0;
        foreach (File::files($dir) as $file) {
            if (str_starts_with($file->getFilename(), 'db-') && $file->getMTime() < now()->subDays($keep)->getTimestamp()) {
                File::delete($file->getPathname());
                $deleted++;
            }
        }

        $this->info('Backup tersimpan: ' . basename($target) . " ({$deleted} backup lama dihapus).");

        return self::SUCCESS;
    }
}
