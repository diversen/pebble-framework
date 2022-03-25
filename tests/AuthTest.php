<?php

declare(strict_types=1);

use Pebble\Config;
use Pebble\Auth;
use Pebble\DB;
use PHPUnit\Framework\TestCase;

final class AuthTest extends TestCase
{
    private function __setup()
    {
        $this->config = new Config();

        $config_dir = __DIR__ . '/../config';
        $config_dir_locale =  __DIR__ . '/../config-locale';

        $this->config->readConfig($config_dir);
        $this->config->readConfig($config_dir_locale);

        $db_config = $this->config->getSection('DB');
        $this->db = new DB($db_config['url'], $db_config['username'], $db_config['password']);
        $this->auth = new Auth($this->db, $this->config->getSection('Auth'));
    }

    private function __cleanup()
    {
        $this->db->prepareExecute("DELETE FROM `auth` WHERE `email` = :email", ['email' => 'some_email@test.dk']);
        $this->db->prepareExecute("DELETE FROM `auth_cookie`");
    }



    private function __create()
    {
        $res = $this->auth->create('some_email@test.dk', 'some_password');
        return $res;
    }

    private function __verify()
    {
        $row = $this->auth->getByWhere(['email' => 'some_email@test.dk']);
        return $this->auth->verifyKey($row['random']);
    }

    public function test_authenticate()
    {
        $this->__setup();
        $this->__cleanup();
        $this->__create();
        $this->__verify();

        $row = $this->auth->authenticate('some_email@test.dk', 'some_password');
        $rows[] = $row;

        $this->assertEquals(1, count($rows));
    }

    public function test_verify()
    {
        $this->__setup();
        $this->__cleanup();
        $this->__create();

        $row = $this->auth->getByWhere(['email' => 'some_email@test.dk']);
        $this->assertEquals("0", $row['verified']);

        $res = $this->auth->isVerified($row['email']);
        $this->assertEquals(false, $res);

        $res = $this->__verify();
        $this->assertEquals(true, $res);

        $row = $this->auth->getByWhere(['email' => 'some_email@test.dk']);
        $this->assertEquals("1", $row['verified']);
    }

    public function test_create()
    {
        $this->__setup();
        $this->__cleanup();

        $this->assertEquals($this->__create(), true);
    }

    public function test_create_unique_email()
    {
        $this->expectException(PDOException::class);

        $this->__setup();
        $this->__cleanup();

        $this->auth->create('some_email@test.dk', 'some_password');
        $this->auth->create('some_email@test.dk', 'some_password');
    }



    public function test_getByWhere()
    {
        $this->__setup();
        $this->__cleanup();
        $this->__create();

        $row = $this->auth->getByWhere(['email' => 'some_email@test.dk']);

        $rows[] = $row;
        $this->assertEquals(1, count($rows));
    }

    public function test_updatePassword()
    {
        $this->__setup();
        $this->__cleanup();
        $this->__create();

        $row = $this->auth->getByWhere(['email' => 'some_email@test.dk']);
        $this->auth->updatePassword($row['id'], 'new secure password');

        $row = $this->auth->authenticate('some_email@test.dk', 'some_password');
        $this->assertEquals([], $row);

        $row = $this->auth->authenticate('some_email@test.dk', 'new secure password');
        $rows[] = $row;
        $this->assertEquals(1, count($rows));
    }

    public function test_isAuthenticated()
    {
        $this->__setup();
        $this->__cleanup();
        $this->__create();
        $this->__verify();

        $row = $this->auth->authenticate('some_email@test.dk', 'some_password');
        $this->auth->setPermanentCookie($row);


        $res = $this->auth->isAuthenticated();
        $this->assertEquals(true, $res);
    }

    public function test_getAuthId()
    {
        $this->__setup();
        $this->__cleanup();
        $this->__create();
        $this->__verify();

        $row = $this->auth->authenticate('some_email@test.dk', 'some_password');
        $this->auth->setPermanentCookie($row);

        $res = $this->auth->getAuthId();
        $this->assertGreaterThan(0, (int)$res);
    }

    public function test_unlinkCurrentCookie()
    {
        $this->__setup();
        $this->__cleanup();
        $this->__create();
        $this->__verify();

        $row = $this->auth->authenticate('some_email@test.dk', 'some_password');
        $this->auth->setPermanentCookie($row);

        $res = $this->auth->isAuthenticated();
        $this->assertEquals(true, $res);

        $this->auth->unlinkCurrentCookie();
        $res = $this->auth->isAuthenticated();
        $this->assertEquals(false, $res);
    }

    public function test_unlinkAllCookies()
    {
        $this->__setup();
        $this->__cleanup();
        $this->__create();
        $this->__verify();

        $row = $this->auth->authenticate('some_email@test.dk', 'some_password');
        $this->auth->setPermanentCookie($row);

        $res = $this->auth->isAuthenticated();
        $this->assertEquals(true, $res);

        $this->auth->unlinkAllCookies($row['id']);
        $res = $this->auth->isAuthenticated();
        $this->assertEquals(false, $res);
    }

    public function test_setSessionCookie()
    {
        $this->__setup();
        $this->__cleanup();
        $this->__create();
        $this->__verify();

        $row = $this->auth->authenticate('some_email@test.dk', 'some_password');
        $this->auth->setSessionCookie($row);

        $res = $this->auth->isAuthenticated();
        $this->assertEquals(true, $res);
    }

    public function test_setPermanentCookie()
    {
        $this->__setup();
        $this->__cleanup();
        $this->__create();
        $this->__verify();

        $row = $this->auth->authenticate('some_email@test.dk', 'some_password');
        $this->auth->setSessionCookie($row);

        $res = $this->auth->isAuthenticated();
        $this->assertEquals(true, $res);
    }

    /*
    public static function tearDownAfterClass(): void
    {
        $db = DBInstance::get();
        $db->prepareExecute("DELETE FROM `auth` WHERE `email` = :email", ['email' => 'some_email@test.dk']);
        $db->prepareExecute("DELETE FROM `auth_cookie`");
    }*/
}
