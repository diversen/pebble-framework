<?php declare(strict_types=1);

namespace Pebble;

use Pebble\File;
use Pebble\DBInstance;

class Migration {

    /**
     * Name of file holding current migration version
     */
    private $migrationFile = '.migration';

    /**
     * Path to dir holding migration files
     */

    private $migrationDir = 'migrations';

    /**
     * Current version of migrations
     */
    private $version = null;
    public function __construct(string $migration_dir = null, string $migration_file = null) {
        if ($migration_dir) $this->migrationDir = $migration_dir;
        if ($migration_file) $this->migrationFile = $migration_file;


    }

    private function getCurrentVersion () {
        if (file_exists($this->migrationFile)) {
            return (int)file_get_contents($this->migrationFile);
        } else {
            return 0;
        }
    }

    private function executeStatements($sql_statements) {
        $db = DBInstance::get();
        foreach($sql_statements as $sql_Statement) {
            $db->prepareExecute($sql_Statement);
        }       
    }

    private function getSQLStatements(string $file) {
        $sql = file_get_contents($file);
        $sql_statements = explode("\n\n", $sql);
        return $sql_statements;
    }

    private function getVersionFromFile(string $file) {
        $info = pathinfo($file);
        return (int)$info['filename'];
    }
    
    public function up(int $target_version = null) {

        $up_dir = $this->migrationDir . '/' . 'up';
        $sql_files = File::dirToArray($up_dir);
        natsort($sql_files);

        $last_version = 0;
        foreach($sql_files as $file) {
            $current_file = $up_dir . '/' . $file;
            
            $version = $this->getVersionFromFile($current_file);
            
            // Skip if version is larger the specified version
            if ($target_version && $version > $target_version) {
                continue;
            }

            if ($this->getCurrentVersion() >= $version) {
                continue;
            }

            $sql_statements = $this->getSQLStatements($current_file);
            $this->executeStatements($sql_statements);

            $last_version = $version;
            file_put_contents($this->migrationFile, $last_version);

        }
    }

    public function down(int $target_version = null) {

        $up_dir = $this->migrationDir . '/' . 'down';
        $sql_files = File::dirToArray($up_dir);
        natsort($sql_files);

        $sql_files = array_reverse($sql_files);

        $last_version = 0;
        foreach($sql_files as $file) {

            
            $current_file = $up_dir . '/' . $file;
            
            $version = $this->getVersionFromFile($current_file);
            
            // Skip if version is larger the specified version
            if ($target_version && $version < $target_version) {
                // echo "SKipping $version 1";
                continue;
            }

            
            if ($this->getCurrentVersion() < $version) {
                // echo "Skipping $version current version  " . $this->getCurrentVersion() . " version file: $version";
                continue;
            }

            $sql_statements = $this->getSQLStatements($current_file);
            $this->executeStatements($sql_statements);

            $last_version = $version;
            file_put_contents($this->migrationFile, $last_version);

        }

        if (!$target_version) {
            unlink($this->migrationFile);
            // file_put_contents($this->migrationFile, 0);
        }
    }

    public function getCurrent() {

    }
}