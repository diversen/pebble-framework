<?php

declare(strict_types=1);

use Pebble\ACLRole;
use Pebble\Auth;
use Pebble\DB;
use Pebble\Config;

use Pebble\Exception\ForbiddenException;
use PHPUnit\Framework\TestCase;

final class ACLRoleTest extends TestCase
{
    public $config;
    public $db;

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

        $acl = new ACLRole($this->db, $this->config->getSection('Auth'));
        $acl->removeAccessRights(['entity' => 'test_entity']);
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

    public function createVerifiedLoginUser()
    {
        $this->__setup();
        $this->__cleanup();
        $this->__create();
        $this->__verify();

        $row = $this->auth->authenticate('some_email@test.dk', 'some_password');
        $this->auth->setPermanentCookie($row);
        return $row;
    }

    public function test_setRole_removeRole()
    {
        $row = $this->createVerifiedLoginUser();
        $acl = new ACLRole($this->db, $this->config->getSection('Auth'));

        $role = [
            'right' => 'admin',
            'auth_id' => $row['id'],
        ];

        $res = $acl->setRole($role);
        $this->assertEquals(true, $res);

        $acl->hasRoleOrThrow($role);

        $res = $acl->removeRole(['auth_id' => $row['id']]);
        $this->assertEquals(true, $res);

        $this->expectException(ForbiddenException::class);
        $acl->hasRoleOrThrow($role);
    }

    public function test_hasRoleOrThrow()
    {
        $row = $this->createVerifiedLoginUser();

        $acl = new ACLRole($this->db, $this->config->getSection('Auth'));

        $role = [
            'right' => 'admin',
            'auth_id' => $row['id'],
        ];

        $acl->setRole($role);

        $role = [
            'right' => 'admin', // This is still admin, so ok.
            'auth_id' => $row['id'],
        ];

        $res = $acl->hasRoleOrThrow($role);
        $this->assertEquals(true, $res);
    }

    public function test_hasRoleOrThrow_throw()
    {
        $row = $this->createVerifiedLoginUser();
        $acl = new ACLRole($this->db, $this->config->getSection('Auth'));

        $role = [
            'right' => 'admin', // This is 'admin'
            'auth_id' => $row['id'],
        ];

        $acl->setRole($role);

        $role = [
            'right' => 'super', // This is 'super' now
            'auth_id' => $row['id'],
        ];

        $this->expectException(ForbiddenException::class);
        $acl->hasRoleOrThrow($role);
    }

    /*
    public static function tearDownAfterClass(): void
    {


    }
    */
}
