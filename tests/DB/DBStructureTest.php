<?php

declare(strict_types=1);

use Pebble\DB\DBStructure;
use Pebble\Service\DBService;
use PHPUnit\Framework\TestCase;

final class DBStructureTest extends TestCase
{
    /**
     * @return \Pebble\DB
     */
    private function __getDB()
    {
        return (new DBService())->getDB();
    }

    public function testDBStructure()
    {

        // https://stackoverflow.com/questions/201621/how-do-i-see-all-foreign-keys-to-a-table-or-column

        $db = (new DBService())->getDB();

        $structure = new DBStructure($db->getDbh());

        $database_name = $structure->getDatabaseName();
        $this->assertEquals('pebble', $database_name);

        $rows = $structure->getTableFields('auth');
        $this->assertContains('id', $rows[0]);

        $keys = array_keys($rows[0]);
        $this->assertContains('COLUMN_TYPE', $keys);

        $keys = $structure->getTableKeys('auth');
        $this->assertEquals('PRIMARY', $keys[0]['Key_name']);

        $foreign_keys = $structure->getForeignKeys();
        $this->assertEquals('auth_cookie', $foreign_keys[0]['TABLE_NAME']);
        $this->assertEquals('id', $foreign_keys[0]['REFERENCED_COLUMN_NAME']);
        $this->assertEquals('auth', $foreign_keys[0]['REFERENCED_TABLE_NAME']);

        $this->assertTrue(true);
    }
}

