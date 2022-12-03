<?php

declare(strict_types=1);

use Pebble\ACL;
use Pebble\Auth;
use Pebble\Service\Container;
use Pebble\Service\ACLService;
use Pebble\Service\AuthService;
use Pebble\Service\DBService;
use Pebble\Service\ConfigService;
use Pebble\Exception\ForbiddenException;
use PHPUnit\Framework\TestCase;

final class ACLTest extends TestCase
{
    /**
     * @var \Pebble\Config
     */
    public $config;

    /**
     * @var \Pebble\DB
     */
    public $db;

    /**
     * @var \Pebble\Auth
     */
    public $auth;

    private function __setup(): void
    {
        $this->config = (new ConfigService())->getConfig();
        $this->auth = (new AuthService())->getAuth();
        $this->db = (new DBService())->getDB();
    }


    private function __cleanup(): void
    {
        $this->db->prepareExecute("DELETE FROM `auth` WHERE `email` = :email", ['email' => 'some_email@test.dk']);
        $this->db->prepareExecute("DELETE FROM `auth_cookie`");

        $acl = new ACL($this->db, $this->config->getSection('Auth'));
        $acl->removeAccessRights(['entity' => 'test_entity']);
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

    /**
     * @return array<string>
     */
    private function __createVerifyLoginUser(): array
    {
        $this->__cleanup();
        $this->__create();
        $this->__verify();

        $auth = new Auth($this->db, $this->config->getSection('Auth'));
        $row = $auth->authenticate('some_email@test.dk', 'some_password');
        $auth->setCookie($row);
        return $row;
    }

    public function test_can_get_service_instance(): void
    {
        $container = new Container();
        $container->unsetAll();

        $acl = (new ACLService())->getACL();
        $this->assertInstanceOf(Pebble\ACL::class, $acl);
    }


    public function test_isAuthenticatedOrThrow_throw(): void
    {
        $this->__setup();
        $this->__cleanup();

        $this->expectException(ForbiddenException::class);
        $acl = new ACL($this->db, $this->config->getSection('Auth'));
        $acl->isAuthenticatedOrThrow();
    }


    public function test_setAccessRights_removeAccessRights(): void
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

        $res = $acl->removeAccessRights(['auth_id' => $row['id']]);
        $this->assertEquals(true, $res);

        $this->expectException(ForbiddenException::class);
        $acl->hasAccessRightsOrThrow($rights);
    }

    public function test_hasAccessRightsOrThrow_throw(): void
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
}
