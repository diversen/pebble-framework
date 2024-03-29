<?php

declare(strict_types=1);

use Pebble\Service\Container;
use Pebble\Service\DBService;
use PHPUnit\Framework\TestCase;

final class DBTest extends TestCase
{
    /**
     * @return \Pebble\DB
     */
    private function __getDB()
    {
        return (new DBService())->getDB();
    }

    private function __cleanup(): void
    {
        $db = $this->__getDB();
        $db->prepareExecute('DROP TABLE IF EXISTS account_test');
    }

    private function __createTestTable(): bool
    {
        $sql = <<<EOF
CREATE TABLE `account_test` (
    `id` int(10) NOT NULL AUTO_INCREMENT,
    `password` varchar(255) NOT NULL,
    `email` varchar(255) NOT NULL,
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
EOF;

        $db = $this->__getDB();

        $res = $db->prepareExecute($sql);
        return $res;
    }

    public function test_can_get_instance(): void
    {
        $container = new Container();
        $container->unsetAll();

        $db = (new DBService())->getDB();
        $this->assertInstanceOf(Pebble\DB::class, $db);
    }

    public function test_prepareExecuteBadSQL(): void
    {
        $this->expectException(PDOException::class);
        $db = $this->__getDB();

        // Bad SQL
        $bad_sql = "CREATE xxTABLE bogus sql";

        $db->prepareExecute($bad_sql);
    }

    public function test_prepareExecute_GoodSQL(): void
    {
        $this->__cleanup();
        $res = $this->__createTestTable();
        $this->assertEquals(
            $res,
            true
        );
    }

    /**
     * Execute a prepared statement with an array of bound values
     */
    public function test_prepareExecute_WithBoundVariables(): void
    {
        $this->__cleanup();
        $this->__createTestTable();

        $values = array(':email' => 'test@test.dk', ':password' => 'secret');
        $sql = "INSERT INTO account_test (`email`, `password`) VALUES (:email, :password)";

        $db = $this->__getDB();
        $res = $db->prepareExecute($sql, $values);
        $this->assertEquals(
            $res,
            true
        );
    }

    /**
     *  Execute a prepared statement with an array of positional values
     */
    public function test_prepareExecute_WithPositionalValues(): void
    {
        $this->__cleanup();
        $this->__createTestTable();

        $values = array('test@test.dk', 'secret');
        $sql = "INSERT INTO account_test (`email`, `password`) VALUES (?, ?)";

        $db = $this->__getDB();
        $res = $db->prepareExecute($sql, $values);
        $this->assertEquals(
            $res,
            true
        );
    }

    /**
     *  Execute a prepared statement with an array of positional values
     */
    public function test_lastInsertId_string(): void
    {
        $this->__cleanup();
        $this->__createTestTable();

        $values = array('test@test.dk', 'secret');
        $sql = "INSERT INTO account_test (`email`, `password`) VALUES (?, ?)";

        $db = $this->__getDB();
        $db->prepareExecute($sql, $values);
        $last_insert_id = $db->lastInsertId();

        $this->assertIsString($last_insert_id);
        $this->assertGreaterThan(0, (int) $last_insert_id);
    }

    /**
     *  Execute a prepared statement with an array of positional values
     *  Note: This method may not return a meaningful or consistent result across different PDO drivers
     *  MySQL returns "0"
     */
    public function test_lastInsertId_false(): void
    {
        $this->__cleanup();
        $this->__createTestTable();

        $db = $this->__getDB();

        // No insertion prior to his - so we expect 0 (or false)
        $last_insert_id = $db->lastInsertId();

        $this->assertIsString($last_insert_id);
        $this->assertEquals((int) $last_insert_id, 0);
    }

    /**
     * Utils method that just adds three rows
     */
    private function __addRows(): void
    {
        $values = [
            ['test@test.dk', 'secret'],
            ['test2@test.dk', 'secret2'],
            ['test3@test.dk', 'secret3'],
        ];

        $db = $this->__getDB();
        foreach ($values as $value) {
            $sql = "INSERT INTO account_test (`email`, `password`) VALUES (?, ?)";
            $db->prepareExecute($sql, $value);
        }
    }

    public function test_prepareFetchAll(): void
    {
        $this->__cleanup();
        $this->__createTestTable();
        $this->__addRows();

        $db = $this->__getDB();

        $rows = $db->prepareFetchAll("SELECT * FROM account_test LIMIT 0, 2");
        $this->assertIsArray($rows);
        $this->assertEquals(count($rows), 2);
    }

    public function test_getAllQuery(): void
    {
        $this->__cleanup();
        $this->__createTestTable();
        $this->__addRows();

        $db = $this->__getDB();

        $rows = $db->getAllQuery("SELECT * FROM account_test", ['password' => 'secret'], ['email' => 'DESC'], [0, 1]);
        $this->assertIsArray($rows);
        $this->assertEquals(count($rows), 1);
    }

    public function test_prepareFetch(): void
    {
        $this->__cleanup();
        $this->__createTestTable();
        $this->__addRows();

        $db = $this->__getDB();

        $row = $db->prepareFetch("SELECT * FROM account_test");

        $rows[] = $row;

        $this->assertIsArray($row);
        $this->assertEquals(count($rows), 1);
    }

    public function test_getOneQuery(): void
    {
        $this->__cleanup();
        $this->__createTestTable();
        $this->__addRows();

        $db = $this->__getDB();

        $row = $db->getOneQuery("SELECT * FROM account_test", ['password' => 'secret'], ['email' => 'DESC']);

        $rows[] = $row;

        $this->assertIsArray($row);
        $this->assertEquals(count($rows), 1);
    }

    public function test_getTableNumRows(): void
    {
        $this->__cleanup();
        $this->__createTestTable();
        $this->__addRows();

        $db = $this->__getDB();

        $num_rows = $db->getTableNumRows('account_test', 'id', ['password' => 'secret']);
        $this->assertEquals($num_rows, 1);
    }

    public function test_getStmt(): void
    {
        $this->__cleanup();
        $this->__createTestTable();
        $this->__addRows();

        $db = $this->__getDB();

        $stmt = $db->getStmt("SELECT * FROM account_test");
        $this->assertEquals(get_class($stmt), 'PDOStatement');
    }

    public function test_rowCount(): void
    {
        $this->__cleanup();
        $this->__createTestTable();
        $this->__addRows();

        $db = $this->__getDB();
        $db->prepareExecute("UPDATE account_test SET `email` = 'some_test_email@test.dk'");

        $rows_affected = $db->rowCount();
        $this->assertEquals($rows_affected, 3);
    }

    public function test_rollback(): void
    {
        $this->__cleanup();
        $this->__createTestTable();

        $db = $this->__getDB();
        $res = $db->beginTransaction();
        $this->assertEquals(true, $res);

        $this->__addRows();
        $res = $db->rollback();
        $this->assertEquals(true, $res);

        $rows = $db->prepareFetchAll("SELECT * FROM account_test");
        $num_rows = count($rows);

        $this->assertEquals($num_rows, 0);
    }

    public function test_commit(): void
    {
        $this->__cleanup();
        $this->__createTestTable();

        $db = $this->__getDB();
        $res = $db->beginTransaction();
        $this->assertEquals(true, $res);

        $this->__addRows();
        $res = $db->commit();
        $this->assertEquals(true, $res);

        $rows = $db->prepareFetchAll("SELECT * FROM account_test");
        $num_rows = count($rows);
        $this->assertEquals($num_rows, 3);
    }

    public function test_inTransactionExec(): void
    {
        $this->__cleanup();
        $this->__createTestTable();

        $db = $this->__getDB();
        $res = $db->inTransactionExec(function () {
            $this->__addRows();
        });

        $this->assertEquals(null, $res);

        $rows = $db->prepareFetchAll("SELECT * FROM account_test");
        $num_rows = count($rows);
        $this->assertEquals($num_rows, 3);

        // $this->expectException(PDOException::class);
        try {
            $res = $db->inTransactionExec(function () use ($db) {
                $db->insert('account_test', ['email' => 'test4@test', 'password' => 'test']);
                $db->insert('account_tests', ['email' => 'test5@test', 'password' => 'test']);
            });
        } catch (PDOException $e) {
            $this->assertStringContainsString('Base table or view not found', $e->getMessage());
        }

        // Still only 3 rows
        $num_rows = $db->getTableNumRows('account_test', 'id');
        $this->assertEquals($num_rows, 3);



        
    }

    public function test_insert(): void
    {
        $this->__cleanup();
        $this->__createTestTable();

        $db = $this->__getDB();
        $res = $db->insert('account_test', ['email' => 'test4@test.dk', 'password' => 'secret4']);
        $this->assertEquals(true, $res);

        $row = $db->getOne('account_test', ['email' => 'test4@test.dk']);
        $this->assertEquals($row['email'], 'test4@test.dk');
    }

    public function test_update(): void
    {
        $this->__cleanup();
        $this->__createTestTable();
        $this->__addRows();

        $db = $this->__getDB();
        $res = $db->update(
            'account_test',
            ['email' => 'test_update_zxc@test.dk', 'password' => 'update_very_secret'],
            ['email' => 'test@test.dk']
        );

        $this->assertEquals(true, $res);

        $row = $db->getOne('account_test', ['email' => 'test_update_zxc@test.dk']);
        $this->assertEquals($row['email'], 'test_update_zxc@test.dk');
        $this->assertEquals($row['password'], 'update_very_secret');
    }

    public function test_getOne(): void
    {
        $this->__cleanup();
        $this->__createTestTable();

        $db = $this->__getDB();
        $res = $db->insert('account_test', ['email' => 'test4@test.dk', 'password' => 'secret4']);
        $this->assertEquals(true, $res);

        $row = $db->getOne('account_test', ['email' => 'test4@test.dk']);
        $this->assertEquals($row['email'], 'test4@test.dk');
    }

    public function test_getAll(): void
    {
        $this->__cleanup();
        $this->__createTestTable();
        $this->__addRows();

        $db = $this->__getDB();

        $rows = $db->getAll('account_test', ['email' => 'test@test.dk']);
        $this->assertIsArray($rows);
        $row = $rows[0];

        $this->assertEquals($row['email'], 'test@test.dk');
    }

    public function test_getWhereSql(): void
    {
        $db = $this->__getDB();
        $where = $db->getWhereSql(['id' => 100, 'test' => 'this is a test']);
        $this->assertEquals($where, " WHERE  `id`=:id  AND  `test`=:test  ");
    }

    public function test_setPDOFetchMode(): void
    {
        $this->__cleanup();
        $this->__createTestTable();
        $this->__addRows();

        $db = $this->__getDB();

        $db->setPDOFetchMode(PDO::FETCH_ASSOC);
        $this->assertEquals($db->getPDOFetchMode(), PDO::FETCH_ASSOC);

        $db->setPDOFetchMode(PDO::FETCH_OBJ);
        $this->assertEquals($db->getPDOFetchMode(), PDO::FETCH_OBJ);

        $row = $db->getOne('account_test', ['email' => 'test@test.dk']);
        $this->assertIsObject($row);

        $rows = $db->getAll('account_test', ['email' => 'test@test.dk']);

        $this->assertIsArray($rows);
        $this->assertIsObject($rows[0]);

        $db->setPDOFetchMode(PDO::FETCH_ASSOC);
    }
}
