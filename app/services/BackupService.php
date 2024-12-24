<?php

class BackupService
{
    const MAX_BACKUPS = 6;
    public string $backupDir;
    public Outputable $out;

    function __construct(bool $forCli = false, public string|null $dbPath = null)
    {
        $this->forCli = $forCli;
        $this->out = $forCli ? new CliOuput : new ToastOutput;
        $this->dbPath ??= DB::getInstance()->sqlitePath;
        $this->backupDir = dirname($this->dbPath) . "/backup";
    }

    function createBackup($maxBackups = self::MAX_BACKUPS)
    {
        $backupFileName = basename(dirname($this->dbPath)) . "_" . date('Ymd_His') . ".sqlite";
        $backupFile = $this->getBackupFile($backupFileName);

        if (!is_dir($this->backupDir)) {
            if (mkdir($this->backupDir, 0755, true)) {
                $this->out->success("Backup directory created");
            } else {
                $this->out->error("Failed to create backup directory.");
                return;
            }
        }

        // backup db
        if (copy($this->dbPath, $backupFile)) {
            $this->out->success("Created $backupFileName");
        } else {
            $this->out->error("Failed to create backup.");
            return;
        }

        // retention
        $backups = $this->getBackups();
        if (count($backups) > $maxBackups) {
            // Sort backups by file modification time (oldest first)
            usort($backups, function ($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            // Delete oldest backups if exceeding the limit
            foreach (array_slice($backups, 0, count($backups) - $maxBackups) as $oldBackup) {
                unlink($oldBackup);
                $name = basename($oldBackup);
                $this->out->warning("Deleted $name");
            }
        }
    }

    function getBackups()
    {
        return glob("$this->backupDir/*.sqlite");
    }

    function getBackupFile($filename)
    {
        return "$this->backupDir/$filename";
    }
}

// TODO - this is an experiment. move this code into their own file some day

interface Outputable
{
    function info($m);
    function error($m);
    function success($m);
    function warning($m);

}

class CliOuput implements Outputable
{

    private Cli $cli;

    function __construct()
    {
        $this->cli = new Cli;
    }
    function info($m)
    {
        $this->cli->out($m);
    }
    function error($m)
    {
        $this->cli->error($m);
    }
    function success($m)
    {
        $this->cli->ok($m);
    }
    function warning($m)
    {
        $this->cli->warning($m);
    }
}

class ToastOutput implements Outputable
{
    function info($m)
    {
        Toast::info($m);
    }
    function error($m)
    {
        Toast::error($m);
    }
    function success($m)
    {
        Toast::success($m);
    }
    function warning($m)
    {
        Toast::warning($m);
    }
}