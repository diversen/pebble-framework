<?php

declare(strict_types=1);

namespace Pebble;

use InvalidArgumentException;
use Pebble\Auth;
use Pebble\Exception\ForbiddenException;
use Pebble\DB;

/**
 * Class that can set some access rights based on a
 * - entity (often just a database row)
 * - entity id (often just the id of the row)
 * - the right to the entity with id (e.g. 'read', 'write')
 * - the auth_id (the id of the user trying to access the entity)
 */
class ACL extends Auth
{
    /**
     * @param \Pebble\DB $db
     * @param array<mixed> $auth_cookie_settings
     */
    public function __construct(DB $db, array $auth_cookie_settings)
    {
        parent::__construct($db, $auth_cookie_settings);
    }

    /**
     * Check if user is authenticated or throw a ForbiddenException
     */
    public function isAuthenticatedOrThrow(string $error_message = ''): void
    {
        if (!$this->isAuthenticated()) {
            if (empty($error_message)) {
                $error_message = 'You are not logged in. Please log in.';
            }
            throw new ForbiddenException($error_message);
        }
    }

    /**
     * Create access right ['entity', 'entity_id', 'right', 'auth_id'] row in `acl` table
     * @param array<mixed> $access_rights
     */
    public function setAccessRights(array $access_rights): bool
    {
        $this->validateAccessAry($access_rights);

        // Only need to be set once
        if ($this->hasAccessRights($access_rights)) {
            return true;
        }
        return $this->db->insert('acl', $access_rights);
    }

    /**
     * Remove access right ['entity', 'entity_id', 'right', 'auth_id'] from `acl` table
     * But it could also just be ['entity' => 'blog']
     * @param array<mixed> $where_access_rights
     */
    public function removeAccessRights(array $where_access_rights): bool
    {
        return $this->db->delete('acl', $where_access_rights);
    }

    /**
     * Check for a valid access rights ary
     * @param array<mixed> $access_rights
     */
    protected function validateAccessAry(array $access_rights): void
    {
        if (!isset($access_rights['entity'], $access_rights['entity_id'], $access_rights['right'], $access_rights['auth_id'])) {
            throw new InvalidArgumentException('Invalid data for ACL::validateAccessAry');
        }
    }

    /**
     * Check for valid access right ['entity', 'entity_id', 'right', 'auth_id'] in `acl` table
     * @param array<mixed> $where_access_rights
     */
    private function hasRights(array $where_access_rights): bool
    {
        $row = $this->db->getOne('acl', $where_access_rights);
        if (empty($row)) {
            return false;
        }
        return true;
    }

    /**
     * Get rights as an array from a list, e.g. the string 'owner, user' returns ['owner', 'user']
     * @return array<mixed>
     */
    private function getRightsArray(string $rights_str): array
    {
        $rights_array = explode(',', $rights_str);
        $ret_ary = [];
        foreach ($rights_array as $right) {
            $ret_ary[] = trim($right);
        }
        return $ret_ary;
    }

    /**
     * If a user has the right 'owner', then if we test for 'owner,admin', using e.g. hasAccessRightsOrThrow,
     * then he will be allowed. He just needs one 'right' in a list of rights.
     * Checks array consisting of ['entity', 'entity_id', 'right', 'auth_id']
     * @param array<mixed> $access_rights
     */
    protected function hasAccessRights(array $access_rights): bool
    {
        $this->validateAccessAry($access_rights);
        $rights_ary = $this->getRightsArray($access_rights['right']);
        foreach ($rights_ary as $right) {
            $access_rights['right'] = $right;

            if ($this->hasRights($access_rights)) {
                return true;
            }
        }
        return false;
    }

    /**
     * If a user has the right 'owner', then if we test for 'owner,admin', using e.g. hasAccessRightsOrThrow,
     * then he will be allowed. He just needs one 'right' in a list of rights.
     * Checks array consisting of ['entity', 'entity_id', 'right', 'auth_id']
     * @param array<mixed> $access_rights
     */
    public function hasAccessRightsOrThrow(array $access_rights, string $error_message = null): void
    {
        $has_access_rights = $this->hasAccessRights($access_rights);
        if (!$has_access_rights) {
            if (!$error_message) {
                $error_message = 'You can not access this page';
            }
            throw new ForbiddenException($error_message);
        }
    }
}
