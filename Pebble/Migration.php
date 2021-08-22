<?php declare(strict_types=1);

namespace Pebble;

class Migration {

    /**
     * Name of file holding current migration version
     */
    private $migrationFile = '.migration';

    /**
     * Path to dir holding migration files
     */

    private $migrationDir = 'migrations';

    public function __construct(string $migration_dir = null, string $migration_file = null) {
        if ($migration_dir) $this->migrationDir = $migration_dir;
        if ($migration_file) $this->migrationFile = $migration_file;


    }
    
    public function up(string $version) {
        
    }

    public function down(string $version) {

    }

    public function getCurrent() {

    }
}