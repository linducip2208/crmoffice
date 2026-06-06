<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class BackupCommand extends Command
{
    protected $signature = 'crmoffice:backup
        {--disk=local : Filesystem disk to write the archive to}
        {--retention=14 : Days of backups to keep on the disk}';

    protected $description = 'Run mysqldump + tar storage/app and write to a backup disk.';

    public function handle(): int
    {
        $disk = $this->option('disk');
        $stamp = now()->format('Y-m-d_H-i-s');
        $tmp = storage_path('app/backup-tmp');

        if (! is_dir($tmp)) {
            mkdir($tmp, 0775, true);
        }

        $sqlFile = "$tmp/db-$stamp.sql";

        $db = config('database.connections.'.config('database.default'));

        if (($db['driver'] ?? null) === 'mysql') {
            $cmd = [
                'mysqldump',
                '--single-transaction',
                '--quick',
                '-h', $db['host'],
                '-P', (string) ($db['port'] ?? 3306),
                '-u', $db['username'],
                $db['database'],
            ];
            $env = $db['password'] ? ['MYSQL_PWD' => $db['password']] : null;

            $process = new Process($cmd, null, $env);
            $process->setTimeout(600);
            $sql = '';
            $process->run(function ($_, $buffer) use (&$sql) {
                $sql .= $buffer;
            });

            if (! $process->isSuccessful()) {
                $this->error('mysqldump failed: '.$process->getErrorOutput());

                return self::FAILURE;
            }
            file_put_contents($sqlFile, $sql);
        } else {
            $this->warn('Backup: non-mysql connection — skipping DB dump.');
        }

        $archive = "backups/crmoffice-$stamp.tar.gz";

        $tar = new Process(['tar', '-czf', storage_path("app/$archive.tmp"),
            '-C', storage_path('app'),
            'public',
            file_exists($sqlFile) ? '../backup-tmp/'.basename($sqlFile) : '',
        ]);
        $tar->setTimeout(900);
        $tar->run();

        if (! $tar->isSuccessful()) {
            $this->error('tar failed: '.$tar->getErrorOutput());

            return self::FAILURE;
        }

        $bytes = file_get_contents(storage_path("app/$archive.tmp"));
        Storage::disk($disk)->put($archive, $bytes);
        unlink(storage_path("app/$archive.tmp"));

        if (file_exists($sqlFile)) {
            unlink($sqlFile);
        }

        $this->info("Backup written to {$disk}://{$archive}");

        $this->pruneOld(Storage::disk($disk), (int) $this->option('retention'));

        return self::SUCCESS;
    }

    private function pruneOld($disk, int $retentionDays): void
    {
        $cutoff = now()->subDays($retentionDays)->getTimestamp();
        foreach ($disk->files('backups') as $file) {
            if ($disk->lastModified($file) < $cutoff) {
                $disk->delete($file);
                $this->line("Pruned $file");
            }
        }
    }
}
