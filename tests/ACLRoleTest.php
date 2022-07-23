<?php

declare(strict_types=1);

use Pebble\ACLRole;
use Pebble\Service\AuthService;
use Pebble\Service\DBService;
use Pebble\Service\ConfigService;
use Pebble\Service\ACLRoleService;
use Pebble\Service\Container;

use Pebble\Exception\ForbiddenException;
use PHPUnit\Framework\TestCase;

final class ACLRoleTest extends TestCase
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

    public function test_can_get_instance(): void
    {

        $container = new Container();
        $container->unsetAll();

        $acl = (new ACLRoleService())->getACLRole();
        $this->assertInstanceOf(Pebble\ACLRole::class, $acl);
    }

    private function __cleanup(): void
    {
        $this->db->prepareExecute("DELETE FROM `auth` WHERE `email` = :email", ['email' => 'some_email@test.dk']);
        $this->db->prepareExecute("DELETE FROM `auth_cookie`");

        $acl = (new ACLRoleService())->getACLRole();
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
    public function createVerifiedLoginUser(): array
    {
        $this->__setup();
        $this->__cleanup();
        $this->__create();
        $this->__verify();

        $row = $this->auth->authenticate('some_email@test.dk', 'some_password');
        $this->auth->setCookie($row);
        return $row;
    }

    public function test_setRole_removeRole(): void
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

    public function test_hasRoleOrThrow(): void
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

    public function test_hasRoleOrThrow_throw(): void
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
}
