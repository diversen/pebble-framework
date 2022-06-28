<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Pebble\Migration;
use Pebble\DB;
use Pebble\Service\ConfigService;

final class MigrationTest extends TestCase
{
    public $config;
    public $db;
    public function __setup()
    {
        $this->config = (new ConfigService())->getConfig();
    }

    public function test_get_up_files()
    {
        $this->__setup();

        $db_config = $this->config->getSection('DB');
        $db = new DB($db_config['url'], $db_config['username'], $db_config['password']);
        $pdo_handle = $db->getDbh();

        // Drop all test tables
        $db->prepareExecute('DROP TABLE IF EXISTS table_1_a');
        $db->prepareExecute('DROP TABLE IF EXISTS table_1_b');
        $db->prepareExecute('DROP TABLE IF EXISTS table_2');
        $db->prepareExecute('DROP TABLE IF EXISTS table_3');

        $m = new Migration($pdo_handle, __DIR__ . '/migrations', __DIR__ . '/.migration');

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

        $tables = $db->prepareFetchAll('SHOW TABLES');
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

        $tables = $db->prepareFetchAll('SHOW TABLES');
        $tables = array_column($tables, 'Tables_in_pebble');

        $this->assertContains('table_1_a', $tables);
        $this->assertContains('table_1_b', $tables);
        $this->assertNotContains('table_2', $tables);

        $down_files = $m->getDownFilesToExecute();
        $this->assertEquals(['0001.sql'], $down_files);

        $version = $m->getCurrentVersion();
        $this->assertEquals(0001, $version);

        $db = new DB($db_config['url'], $db_config['username'], $db_config['password']);
        $m = new Migration($pdo_handle, __DIR__ . '/migrations', __DIR__ . '/.migration');
        $m->down();

        $version = $m->getCurrentVersion();

        $this->assertEquals(0, $version);

        $tables = $db->prepareFetchAll('SHOW TABLES');
        $tables = array_column($tables, 'Tables_in_pebble');

        $this->assertNotContains('table_1_a', $tables);
        $this->assertNotContains('table_1_b', $tables);
    }
}
