<?php

class BackupService
{
    public $backupDir = __DIR__ . '/../../.sqlite/backup';
    public $cr;

    function __construct(public bool $forCli = false)
    {
        $this->cr = $forCli ? PHP_EOL : "<br>";
    }

    function createBackup($maxBackups = 6)
    {
        $dbPath = DBFactory::getSqliteLocation(DBFactory::getSqliteDbName());
        $backupFileName = "backup_" . date('Ymd_His') . ".sqlite";
        $backupFile = $this->getBackupFile($backupFileName);

        if (!is_dir($this->backupDir)) {
            if (mkdir($this->backupDir, 0755, true)) {
                echo "Backup directory created" . $this->cr;
            } else {
                echo "Failed to create backup directory.";
                return;
            }
        }

        // backup db
        if (copy($dbPath, $backupFile)) {
            echo "Created $backupFileName" . $this->cr;
        } else {
            echo "Failed to create backup.";
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
                echo "Deleted $name" . $this->cr;
            }
        }
    }

    function getBackups()
    {
        return glob("$this->backupDir/backup_*.sqlite");
    }

    function getBackupFile($filename)
    {
        return "$this->backupDir/$filename";
    }
}