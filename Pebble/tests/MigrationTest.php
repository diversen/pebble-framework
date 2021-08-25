<?php declare (strict_types = 1);

use Pebble\Config;
use Pebble\DBInstance;
use PHPUnit\Framework\TestCase;
use Pebble\Migration;

$config_dir = __DIR__ . '/../../config';
Config::readConfig($config_dir);

final class MigrationTest extends TestCase
{

    private function connect()
    {
        DBInstance::close();
        $db_config = Config::getSection('DB');
        DBInstance::connect($db_config['url'], $db_config['username'], $db_config['password']);
    }


    public function test_get_up_files() {

        $this->connect();

        // Drop all test tables
        DBInstance::get()->prepareExecute('DROP TABLE IF EXISTS table_1_a');
        DBInstance::get()->prepareExecute('DROP TABLE IF EXISTS table_1_b');
        DBInstance::get()->prepareExecute('DROP TABLE IF EXISTS table_2');
        DBInstance::get()->prepareExecute('DROP TABLE IF EXISTS table_3');

        $m = new Migration(__DIR__ . '/migrations', __DIR__ . '/.migration');

        $m->setCurrentVersion(0000);

        $up_files = $m->getUpFilesToExecute(0003);
        $this->assertEquals(['0001.sql', '0002.sql', '0003.sql'], $up_files);

        $up_files = $m->getUpFilesToExecute(0001);
        $this->assertEquals(['0001.sql'], $up_files);

        $m->setCurrentVersion(0001);

        $up_files = $m->getUpFilesToExecute();
        $this->assertEquals(['0002.sql', '0003.sql'], $up_files);

        $m->setCurrentVersion(0001);

        $up_files = $m->getUpFilesToExecute(0002);
        $this->assertEquals(['0002.sql'], $up_files);

        $m->setCurrentVersion(0003);

        $down_files = $m->getDownFilesToExecute(0001);
        $this->assertEquals(['0003.sql', '0002.sql'], $down_files);

        $down_files = $m->getDownFilesToExecute();
        $this->assertEquals(['0003.sql', '0002.sql', '0001.sql'], $down_files);

        $down_files = $m->getDownFilesToExecute();

        // Run the migrations
        $m->setCurrentVersion();
        $m->up(0002);

        $tables = DBInstance::get()->prepareFetchAll('SHOW TABLES');
        $tables = array_column($tables, 'Tables_in_pebble');

        $this->assertContains('table_1_a', $tables);
        $this->assertContains('table_1_b', $tables);
        $this->assertContains('table_2', $tables);
        $this->assertNotContains('table_3', $tables);

        $version = $m->getCurrentVersion();
        $this->assertEquals(0002, $version);

        $down_files = $m->getDownFilesToExecute(0001);
        $this->assertEquals(['0002.sql'], $down_files);
        $m->down(0001);

        $version = $m->getCurrentVersion();
        $this->assertEquals(0001, $version);
        
        $tables = DBInstance::get()->prepareFetchAll('SHOW TABLES');
        $tables = array_column($tables, 'Tables_in_pebble');

        $this->assertContains('table_1_a', $tables);
        $this->assertContains('table_1_b', $tables);
        $this->assertNotContains('table_2', $tables);

        $down_files = $m->getDownFilesToExecute();
        $this->assertEquals(['0001.sql'], $down_files);

        $version = $m->getCurrentVersion();
        $this->assertEquals(0001, $version);
        
        $this->connect();
        $m->down();

        $version = $m->getCurrentVersion();
        $this->assertEquals(0, $version);

        $tables = DBInstance::get()->prepareFetchAll('SHOW TABLES');
        $tables = array_column($tables, 'Tables_in_pebble');

        $this->assertNotContains('table_1_a', $tables);
        $this->assertNotContains('table_1_b', $tables);

    }
}
