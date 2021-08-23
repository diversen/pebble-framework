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
        $db_config = Config::getSection('DB');
        DBInstance::connect($db_config['url'], $db_config['username'], $db_config['password']);
    }


    public function test_get_up_files() {

        $this->connect();
        $m = new Migration(__DIR__ . '/migrations', __DIR__ . '/.migration');

        $m->setCurrentVersion(0000);

        $up_files = $m->getUpFilesToExecute(0002);
        $this->assertEquals(['0001.sql', '0002.sql'], $up_files);

        $up_files = $m->getUpFilesToExecute(0001);
        $this->assertEquals(['0001.sql'], $up_files);

        $up_files = $m->getUpFilesToExecute(0000);
        $this->assertEquals([], $up_files);

        $m->setCurrentVersion(0001);

        $up_files = $m->getUpFilesToExecute(0002);
        $this->assertEquals(['0002.sql'], $up_files);

        $m->setCurrentVersion(0001);

        $up_files = $m->getUpFilesToExecute(0002);
        $this->assertEquals(['0002.sql'], $up_files);

        $m->setCurrentVersion(0000);
        $m->up(0002);

        $tables = DBInstance::get()->prepareFetchAll('SHOW TABLES');
        $tables = array_column($tables, 'Tables_in_pebble');

        $this->assertContains('table_1', $tables);
        $this->assertContains('table_2', $tables);
        $this->assertContains('table_3', $tables);
        $this->assertContains('table_4', $tables);

        $version = $m->getCurrentVersion();
        $this->assertEquals(0002, $version);

        $m->setCurrentVersion(null);




    }
}
