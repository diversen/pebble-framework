<?php

declare(strict_types=1);

namespace Pebble;

use Pebble\ACL;
use Pebble\Exception\ForbiddenException;
use Exception;

class ACLRole extends ACL
{
    /**
     * @param \Pebble\DB $db
     * @param array<mixed> $settings
     */
    public function __construct(\Pebble\DB $db, array $settings)
    {
        parent::__construct($db, $settings);
    }

    /**
     * Sets a user role ['right' => 'admin', 'auth_id' => '1234']
     * `$acl_role->setRole(['right' => 'admin', 'auth_id' => '1234'])`
     * @param array<mixed> $role
     */
    public function setRole(array $role): bool
    {
        $role['entity'] = 'ROLE';
        $role['entity_id'] = '0';

        return $this->setAccessRights($role);
    }

    /**
     * Remove a role
     * `$acl_role->removeRole(['right' => 'admin', 'auth_id' => '1234'])`
     * @param array<mixed> $role
     */
    public function removeRole(array $role): bool
    {
        $role['entity'] = 'ROLE';
        $role['entity_id'] = '0';

        return $this->removeAccessRights($role);
    }

    /**
     * Checks if a user has a role, e.g. ['right' => 'admin', 'auth_id' => '1234']
     * `$acl_role->hasRoleOrThrow(['right' => 'admin', 'auth_id' => '1234'])`
     * @param array<mixed> $role
     */
    public function hasRoleOrThrow(array $role): bool
    {
        $role['entity'] = 'ROLE';
        $role['entity_id'] = '0';

        $has_role = $this->hasAccessRights($role);
        if (!$has_role) {
            throw new ForbiddenException('You can not access this page.');
        }
        return true;
    }

    /**
     * Check if authenticated user has defined role
     */
    public function inSessionHasRole(string $role): bool
    {
        $auth_id = (int)$this->getAuthId();
        if ($auth_id === '0') {
            return false;
        }
        try {
            $this->hasRoleOrThrow(['right' => $role, 'auth_id' => $auth_id]);
            return true;
        } catch (Exception $e) {
            return false;
        }
            
    }
}
