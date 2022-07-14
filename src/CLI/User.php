<?php

declare(strict_types=1);

namespace Pebble\CLI;

use Diversen\ParseArgv;
use Pebble\Service\AuthService;
use Diversen\Cli\Utils;
use Exception;

class User
{
    private $auth;


    // Return main commands help
    public function getCommand()
    {
        return
        array(
            'usage' => 'Command to alter auth table (users)',
            'options' => array(
                '--create-user' => 'Create a new user',
            ),

        );
    }


    public function runCommand(ParseArgv $args)
    {
        try {
            $this->auth = (new AuthService())->getAuth();
        } catch (Exception $e) {
            echo "Auth could not be initiated. Maybe there is no database connection?\n";
            return 1;
        }
        
        $utils = new Utils();
        if ($args->getOption('create-user')) {
            $email = trim($utils->readSingleline("Enter email: "));
            $password = trim($utils->readSingleline("Enter password: "));

            if (!empty($email) && !empty($password)) {
                $this->auth->create($email, $password);
                $row = $this->auth->getByWhere(['email' => $email]);
                $res = $this->auth->verifyKey($row['random']);
                if ($res) {
                    $utils->echoStatus('Success', 'notice', 'User has been created');
                    return 0;
                }
            }

            $utils->echoStatus('Error', 'r', 'Something went wrong. Try again');
            return 128;
        }

        return 0;
    }
}
