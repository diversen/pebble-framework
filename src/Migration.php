<?php

declare(strict_types=1);

namespace Pebble;

use PDO;
use Exception;
use function Safe\scandir;
use function Safe\file_get_contents;

/**
 * Quite primite migration
 */
class Migration
{
    /**
     * Name of file holding current migration version
     */
    private string $migrationFile = '.migration';

    /**
     * Path to dir holding migration files
     */
    private string $migrationDir = 'migrations';

    /**
     * @var \PDO
     */
    private $dbh;
    
    public function __construct(PDO $dbh, string $migration_dir = null, string $migration_file = null) {
        $this->dbh = $dbh;
        if ($migration_dir) {
            $this->migrationDir = $migration_dir;
        }
        if ($migration_file) {
            $this->migrationFile = $migration_file;
        }

        if (!is_dir($this->migrationDir)) {
            throw new Exception("Specified migration dir does not exist: " . $this->migrationDir);
        }
    }

    /**
     * @return int $version
     */
    public function getCurrentVersion(): int
    {
        if (file_exists($this->migrationFile)) {
            return (int)file_get_contents($this->migrationFile);
        } else {
            return 0;
        }
    }

    public function setCurrentVersion(?int $version = 0): void
    {
        file_put_contents($this->migrationFile, $version);
        if (!$version) {
            unlink($this->migrationFile);
        }
    }


    /**
     * Recursively read all file in a dir except '.', '..'
     * From http://php.net/manual/en/function.scandir.php#110570
     * @return array<mixed>
     */
    private function dirToArray(string $dir): array
    {
        $result = array();
        $cdir = scandir($dir);

        foreach ($cdir as $value) {
            if (!in_array($value, array(".", ".."))) {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                    $result[$value] = $this->dirToArray($dir . DIRECTORY_SEPARATOR . $value);
                } else {
                    $result[] = $value;
                }
            }
        }
        return $result;
    }


    private function executeSql(string $sql): bool
    {
        $stmt = $this->dbh->prepare($sql);
        return $stmt->execute();
    }
 
    /**
     * @param array<string> $sql_statements
     */   
    private function executeStatements(array $sql_statements): void
    {
        foreach ($sql_statements as $sql_Statement) {
            $this->executeSql($sql_Statement);
        }
    }

    /**
     * @return array<string>
     */
    private function getSQLStatements(string $file): array
    {
        $sql = file_get_contents($file);
        $sql_statements = explode("\n\n", $sql);
        return $sql_statements;
    }

    private function getVersionFromFile(string $file): int
    {
        $info = pathinfo($file);
        return (int)$info['filename'];
    }

    /**
     * @return array<string>
     */
    public function getUpFilesToExecute(int $target_version = null)
    {
        $up_dir = $this->migrationDir . '/' . 'up';
        $sql_files = $this->dirToArray($up_dir);
        natsort($sql_files);

        $files_to_exec = [];
        $current_version = $this->getCurrentVersion();

        if (!$target_version) {
            foreach ($sql_files as $file) {
                if ($this->getVersionFromFile($file) > $current_version) {
                    $files_to_exec[] = $file;
                }
            }
        } else {
            foreach ($sql_files as $file) {
                if ($this->getVersionFromFile($file) > $current_version  && $this->getVersionFromFile($file) <= $target_version) {
                    $files_to_exec[] = $file;
                }
            }
        }

        return $files_to_exec;
    }

    public function getLatestVersion(): int
    {
        $files = $this->getUpFilesToExecute();
        $last = array_pop($files);
        if (!$last) {
            return 0;
        }

        return $this->getVersionFromFile($last);
    }

    /**
     * @return array<string>
     */
    public function getDownFilesToExecute(int $target_version = null)
    {
        $up_dir = $this->migrationDir . '/' . 'down';
        $sql_files = $this->dirToArray($up_dir);
        natsort($sql_files);
        $sql_files = array_reverse($sql_files);

        $files_to_exec = [];
        $current_version = $this->getCurrentVersion();
        if (!$target_version) {
            foreach ($sql_files as $file) {
                if ($this->getVersionFromFile($file) <= $current_version) {
                    $files_to_exec[] = $file;
                }
            }
        } else {
            foreach ($sql_files as $file) {
                if ($this->getVersionFromFile($file) <= $current_version  && $this->getVersionFromFile($file) > $target_version) {
                    $files_to_exec[] = $file;
                }
            }
        }

        return $files_to_exec;
    }

    /**
     * Executes up to and INCLUDING target_version
     */
    public function up(int $target_version = null): void
    {
        $files = $this->getUpFilesToExecute($target_version);
        if (empty($files)) {
            return;
        }

        if (!$target_version) {
            $version_file = array_pop($files);
            $target_version = $this->getVersionFromFile($version_file);
        }

        foreach ($files as $file) {
            $file_path = $this->migrationDir . '/up/' . $file;
            $sql_statements = $this->getSQLStatements($file_path);
            $this->executeStatements($sql_statements);
        }

        $this->setCurrentVersion($target_version);
    }

    /**
     * Executes down to and EXCLUDING target_version
     */
    public function down(int $target_version = null): void
    {
        $files = $this->getDownFilesToExecute($target_version);
        foreach ($files as $file) {
            $file_path = $this->migrationDir . '/down/' . $file;
            $sql_statements = $this->getSQLStatements($file_path);
            $this->executeStatements($sql_statements);
        }

        $this->setCurrentVersion($target_version);
    }
}
