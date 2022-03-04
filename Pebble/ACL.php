<?php declare (strict_types = 1);

namespace Pebble;

use InvalidArgumentException;
use Pebble\Auth;
use Pebble\Exception\ForbiddenException;
use Pebble\DB;

class ACL extends Auth
{

    public function __construct(DB $db, array $auth_cookie_settings)
    {
        parent::__construct($db, $auth_cookie_settings);
    }

    /**
     * Check if user is authenticated or throw a ForbiddenException
     */
    public function isAuthenticatedOrThrow()
    {
        if (!$this->isAuthenticated()) {
            throw new ForbiddenException('You can not access this page');
        }
    }

    /**
     * Check if a user can access a page and output JSON error if not
     */
    public function isAuthenticatedOrJSONError(): bool
    {

        $response = [];

        try {
            $this->isAuthenticatedOrThrow();
        } catch (ForbiddenException $e) {
            $response['error'] = $e->getMessage();
            echo json_encode($response);
            return false;
        }

        return true;
    }

    /**
     * Create access right ['entity', 'entity_id', 'right', 'auth_id'] row in `acl` table
     */
    public function setAccessRights(array $access_rights)
    {
        $this->validateAccessAry($access_rights);
        return $this->db->insert('acl', $access_rights);

    }

    /**
     * Remove access right ['entity', 'entity_id', 'right', 'auth_id'] from `acl` table
     * But it could also just be ['entity' => 'blog']
     */
    public function removeAccessRights(array $where_access_rights)
    {
        return $this->db->delete('acl', $where_access_rights);
    }

    /**
     * Check for a valid access rights ary
     */
    protected function validateAccessAry(array $ary)
    {
        if (!isset($ary['entity'], $ary['entity_id'], $ary['right'], $ary['auth_id'])) {
            throw new InvalidArgumentException('Invalid data for ACL::validateAccessAry');
        }
    }

    /**
     * Check for valid access right ['entity', 'entity_id', 'right', 'auth_id'] in `acl` table
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
     */
    protected function hasAccessRights(array $ary)
    {
        $this->validateAccessAry($ary);
        $rights_ary = $this->getRightsArray($ary['right']);
        foreach ($rights_ary as $right) {

            $ary['right'] = $right;

            if ($this->hasRights($ary)) {
                return true;
            }
        }
        return false;
    }

    /**
     * If a user has the right 'owner', then if we test for 'owner,admin', using e.g. hasAccessRightsOrThrow,
     * then he will be allowed. He just needs one 'right' in a list of rights.
     * Checks array consisting of ['entity', 'entity_id', 'right', 'auth_id']
     */
    public function hasAccessRightsOrThrow(array $ary, string $error_message = null)
    {
        $has_access_rights = $this->hasAccessRights($ary);
        if (!$has_access_rights) {
            if (!$error_message) {
                $error_message = 'You can not access this page';
            }
            throw new ForbiddenException($error_message);
        }
    }
}
