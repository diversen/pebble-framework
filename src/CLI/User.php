<?php

declare(strict_types=1);

namespace Pebble\CLI;

use Diversen\ParseArgv;
use Pebble\Service\AuthService;
use Pebble\Service\ACLRoleService;
use Diversen\Cli\Utils;
use Exception;

class User
{   
    /**
     * @var \Pebble\Auth
     */
    private $auth;

    private $utils;

    public function __construct(){
        $this->utils = new Utils();
    }


    /**
     * Return command definition
     * @return array<mixed>
     */
    public function getCommand(): array
    {
        return [
            'usage' => 'Command to alter auth table (users)',
            'options' => [
                '--create-user' => 'Create a new user',
                '--set-admin' => 'Set admin status on user'
            ],

        ];
    }

    private function setAdmin() {

        $this->auth = (new AuthService())->getAuth();

        $email = trim($this->utils->readSingleline("Enter email: "));
        $row = $this->auth->getByWhere(['email' => $email]);
        if (!empty($row)){
            $res = $this->setAdminRole($row['id']);
            if ($res) {
                return 0;
            }
        }

        $this->utils->echoStatus('Error', 'r', 'Could not add admin role. Maybe the user does not exist?');
        return 1;

    }

    private function createUser() {
        
        $this->auth = (new AuthService())->getAuth();

        $email = trim($this->utils->readSingleline("Enter email: "));
        $password = trim($this->utils->readSingleline("Enter password: "));

        $admin = false;
        if ($this->utils->readlineConfirm('Should user be given the role as admin?')) {
            $admin = true;
        }

        if (!empty($email) && !empty($password)) {
            $this->auth->create($email, $password);
            $row = $this->auth->getByWhere(['email' => $email]);
            $res = $this->auth->verifyKey($row['random']);

            if ($admin) {
                $this->setAdminRole($row['id']);
            }

            if ($res) {
                $this->utils->echoStatus('Success', 'notice', 'User has been created');
                return 0;
            }
        }

        $this->utils->echoStatus('Error', 'r', 'Something went wrong. Try again');
        return 128;
    }

    private function setAdminRole(string $auth_id) {
        $acl_role = (new ACLRoleService())->getACLRole();
        return $acl_role->setRole(['right' => 'admin', 'auth_id' => $auth_id]);
    }

    private function init() {
        try {
            $this->auth = (new AuthService())->getAuth();
        } catch (Exception $e) {
            echo "Auth could not be initialized. Maybe there is no database connection?\n";
            return 1;
        }
    }

    private function addACLRole() {

    }


    public function runCommand(ParseArgv $args): int
    {
        

        
        if ($args->getOption('create-user')) {
            return $this->createUser();
        }

        if ($args->getOption('set-admin')) {
            return $this->setAdmin();
        }

        return 0;
    }
}
