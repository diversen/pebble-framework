<?php declare (strict_types = 1);

use Pebble\Auth;
use Pebble\Config;
use Pebble\DBInstance;
use PHPUnit\Framework\TestCase;

final class MigrationTest extends TestCase
{

    private function dbConnect()
    {
        $db_config = Config::getSection('DB');
        DBInstance::connect($db_config['url'], $db_config['username'], $db_config['password']);
    }

    /*
    private function cleanup()
    {
        $db = DBInstance::get();
        $db->prepareExecute("DELETE FROM `auth` WHERE `email` = :email", ['email' => 'some_email@test.dk']);
        $db->prepareExecute("DELETE FROM `auth_cookie`");
    }

    private function create() {

        $auth = Auth::getInstance();
        $res = $auth->create('some_email@test.dk', 'some_password');
        return $res;
    }

    private function verify() {
        $auth = Auth::getInstance();
        $row = $auth->getByWhere(['email' => 'some_email@test.dk']);

        return $auth->verifyKey($row['random']);


    }*/


    
    public function test_migrate()
    {
        $this->dbConnect();
        echo "hello world";

    }
    /*
    public function test_verify()
    {

        $this->dbConnect();
        $this->cleanup();
        $this->create();
        

        $auth = Auth::getInstance();


        $row = $auth->getByWhere(['email' => 'some_email@test.dk']);
        $this->assertEquals("0", $row['verified']);

        $res = $auth->isVerified($row['email']);
        $this->assertEquals(false, $res);

        $res = $this->verify();
        $this->assertEquals(true, $res);

        $row = $auth->getByWhere(['email' => 'some_email@test.dk']);
        $this->assertEquals("1", $row['verified']);

    }
    */
}
