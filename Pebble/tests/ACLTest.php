<?php

declare(strict_types=1);

use Pebble\ACL;
use Pebble\Auth;
use Pebble\DB;
use Pebble\Config;
use Pebble\Exception\ForbiddenException;
use PHPUnit\Framework\TestCase;

final class ACLTest extends TestCase
{
    public $config;
    public $db;

    private function __setup()
    {
        $this->config = new Config();

        $config_dir = __DIR__ . '/../../config';
        $config_dir_locale =  __DIR__ . '/../../config-locale';

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

        $acl = new ACL($this->db, $this->config->getSection('Auth'));
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

    private function __createVerifyLoginUser()
    {
        $this->__cleanup();
        $this->__create();
        $this->__verify();

        $auth = new Auth($this->db, $this->config->getSection('Auth'));
        $row = $auth->authenticate('some_email@test.dk', 'some_password');
        $auth->setPermanentCookie($row);
        return $row;
    }

    public function test_isAuthenticatedOrThrow_throw()
    {
        $this->__setup();
        $this->__cleanup();

        $this->expectException(ForbiddenException::class);
        $acl = new ACL($this->db, $this->config->getSection('Auth'));
        $acl->isAuthenticatedOrThrow();
    }

    public function test_isAuthenticatedOrThrow()
    {
        $this->__setup();
        $this->__createVerifyLoginUser();

        $acl = new ACL($this->db, $this->config->getSection('Auth'));

        $res = $acl->isAuthenticatedOrThrow();

        $this->assertEquals(null, $res);
    }

    public function test_isAuthenticatedOrJSONError_throw()
    {
        $this->__setup();
        $this->__cleanup();

        $acl = new ACL($this->db, $this->config->getSection('Auth'));
        $res = $acl->isAuthenticatedOrJSONError();
        $this->assertEquals(false, $res);
        $this->expectOutputString('{"error":"You can not access this page"}');
    }

    public function test_isAuthenticatedOrJSONError()
    {
        $this->__setup();
        $this->__createVerifyLoginUser();

        $acl = new ACL($this->db, $this->config->getSection('Auth'));
        $res = $acl->isAuthenticatedOrJSONError();
        $this->assertEquals(true, $res);
    }

    public function test_setAccessRights_removeAccessRights()
    {
        $this->__setup();
        $row = $this->__createVerifyLoginUser();

        $acl = new ACL($this->db, $this->config->getSection('Auth'));

        $rights = [
            'entity' => 'test_entity',
            'entity_id' => 42,
            'right' => 'read',
            'auth_id' => $row['id'],
        ];

        $res = $acl->setAccessRights($rights);
        $this->assertEquals(true, $res);

        $res = $acl->hasAccessRightsOrThrow($rights);
        $this->assertEquals(null, $res);

        $res = $acl->removeAccessRights(['auth_id' => $row['id']]);
        $this->assertEquals(true, $res);

        $this->expectException(ForbiddenException::class);
        $acl->hasAccessRightsOrThrow($rights);
    }

    public function test_hasAccessRightsOrThrow()
    {
        $this->__setup();
        $row = $this->__createVerifyLoginUser();

        $acl = new ACL($this->db, $this->config->getSection('Auth'));
        ;

        $rights = [
            'entity' => 'test_entity',
            'entity_id' => 42,
            'right' => 'read',
            'auth_id' => $row['id'],
        ];

        $acl->setAccessRights($rights);

        $rights = [
            'entity' => 'test_entity',
            'entity_id' => 42,
            'right' => 'read,write', // It has read among others, so it is ok.
            'auth_id' => $row['id'],
        ];

        $res = $acl->hasAccessRightsOrThrow($rights);
        $this->assertEquals(null, $res);
    }

    public function test_hasAccessRightsOrThrow_throw()
    {
        $this->__setup();
        $row = $this->__createVerifyLoginUser();

        $acl = new ACL($this->db, $this->config->getSection('Auth'));

        $rights = [
            'entity' => 'test_entity',
            'entity_id' => 42,
            'right' => 'read', // This is read
            'auth_id' => $row['id'],
        ];

        $acl->setAccessRights($rights);

        $rights = [
            'entity' => 'test_entity',
            'entity_id' => 42,
            'right' => 'write', // But we are testing for write
            'auth_id' => $row['id'],
        ];

        $this->expectException(ForbiddenException::class);
        $acl->hasAccessRightsOrThrow($rights);
    }

    /*
    public static function tearDownAfterClass(): void
    {
        $db = DBInstance::get();
        $db->prepareExecute("DELETE FROM `auth` WHERE `email` = :email", ['email' => 'some_email@test.dk']);
        $db->prepareExecute("DELETE FROM `auth_cookie`");

        $acl = new ACL();
        $acl->removeAccessRights(['entity' => 'test_entity']);
    }*/
}
