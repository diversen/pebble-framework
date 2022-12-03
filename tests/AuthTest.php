<?php

declare(strict_types=1);

use Pebble\Service\Container;
use Pebble\Service\AuthService;
use Pebble\Service\DBService;
use PHPUnit\Framework\TestCase;

final class AuthTest extends TestCase
{
    /**
     * @var \Pebble\Auth
     */
    private $auth;

    /**
     * @var \Pebble\DB
     */
    private $db;
    private function __setup(): void
    {
        $this->auth = (new AuthService())->getAuth();
        $this->db = (new DBService())->getDB();
    }

    private function __cleanup(): void
    {
        $this->db->prepareExecute("DELETE FROM `auth` WHERE `email` = :email", ['email' => 'some_email@test.dk']);
        $this->db->prepareExecute("DELETE FROM `auth_cookie`");
    }

    private function __create(): bool
    {
        $res = $this->auth->create('some_email@test.dk', 'some_password');
        return $res;
    }

    private function __verify(): bool
    {
        $row = $this->auth->getByWhere(['email' => 'some_email@test.dk']);
        return $this->auth->verifyKey($row['random']);
    }

    public function test_can_get_service_instance(): void
    {
        $container = new Container();
        $container->unsetAll();

        $auth = (new AuthService())->getAuth();
        $this->assertInstanceOf(Pebble\Auth::class, $auth);
    }

    public function test_authenticate(): void
    {
        $this->__setup();
        $this->__cleanup();
        $this->__create();
        $this->__verify();

        $row = $this->auth->authenticate('some_email@test.dk', 'some_password');
        $rows[] = $row;

        $this->assertEquals(1, count($rows));
    }

    public function test_verify(): void
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

    public function test_create(): void
    {
        $this->__setup();
        $this->__cleanup();

        $this->assertEquals($this->__create(), true);
    }

    public function test_create_unique_email(): void
    {
        $this->expectException(PDOException::class);

        $this->__setup();
        $this->__cleanup();

        $this->auth->create('some_email@test.dk', 'some_password');
        $this->auth->create('some_email@test.dk', 'some_password');
    }



    public function test_getByWhere(): void
    {
        $this->__setup();
        $this->__cleanup();
        $this->__create();

        $row = $this->auth->getByWhere(['email' => 'some_email@test.dk']);

        $rows[] = $row;
        $this->assertEquals(1, count($rows));
    }

    public function test_updatePassword(): void
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

    public function test_isAuthenticated(): void
    {
        $this->__setup();
        $this->__cleanup();
        $this->__create();
        $this->__verify();

        $row = $this->auth->authenticate('some_email@test.dk', 'some_password');
        $this->auth->setCookie($row);


        $res = $this->auth->isAuthenticated();
        $this->assertEquals(true, $res);
    }

    public function test_getAuthId(): void
    {
        $this->__setup();
        $this->__cleanup();
        $this->__create();
        $this->__verify();

        $row = $this->auth->authenticate('some_email@test.dk', 'some_password');
        $this->auth->setCookie($row);

        $res = $this->auth->getAuthId();
        $this->assertGreaterThan(0, (int)$res);
    }

    public function test_unlinkCurrentCookie(): void
    {
        $this->__setup();
        $this->__cleanup();
        $this->__create();
        $this->__verify();

        $row = $this->auth->authenticate('some_email@test.dk', 'some_password');
        $this->auth->setCookie($row);

        $res = $this->auth->isAuthenticated();
        $this->assertEquals(true, $res);

        $this->auth->unlinkCurrentCookie();
        $res = $this->auth->isAuthenticated();
        $this->assertEquals(false, $res);
    }

    public function test_unlinkAllCookies(): void
    {
        $this->__setup();
        $this->__cleanup();
        $this->__create();
        $this->__verify();

        $row = $this->auth->authenticate('some_email@test.dk', 'some_password');
        $this->auth->setCookie($row);

        $res = $this->auth->isAuthenticated();
        $this->assertEquals(true, $res);

        $this->auth->unlinkAllCookies($row['id']);
        $res = $this->auth->isAuthenticated();
        $this->assertEquals(false, $res);
    }

    public function test_setCookie(): void
    {
        $this->__setup();
        $this->__cleanup();
        $this->__create();
        $this->__verify();

        $row = $this->auth->authenticate('some_email@test.dk', 'some_password');
        $this->auth->setCookie($row);

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
