<?php

declare(strict_types=1);

namespace Pebble;

use Pebble\ACL;
use Pebble\Exception\ForbiddenException;
use Pebble\DB;

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
     * `$aclr->setRole(['right' => 'admin', 'auth_id' => '1234'])`
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
     * `$aclr->removeRole(['right' => 'admin', 'auth_id' => '1234'])`
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
     * `$aclr->hasRoleOrThrow(['right' => 'admin', 'auth_id' => '1234'])`
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
}
