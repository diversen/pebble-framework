<?php

namespace Pebble\Log;

use Pebble\Log\Base;
use Pebble\DB;
use Pebble\Auth;

class DBLog extends Base {
 
    /**
     * @var \Pebble\DB
     */
    private $db;
    
    /**
     * @var \Pebble\Auth
     */
    private $auth;
    /**
     * Log section, e.g. mail, auth, etc. 
     */
    private $section = 'default';
    public function __construct(DB $db, Auth $auth)
    {
        $this->db = $db;
        $this->auth = $auth;        
    }

    public function setSection(string $section){
        $this->section = $section;
    }

    /**
     * Log a message
     * @param string $message
     * @param string $type RFC types are: 'debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'
     * @param string $category
     */
    public function message($message, string $type = 'debug', $section = 'default'): void
    {

        if (!is_string($message)) {
            $message = var_export($message, true);
        }

        $this->db->insert('log', [
            'message' => $message, 
            'date' => date('Y-m-d H:i:s'),
            'remote_ip' => $_SERVER['REMOTE_ADDR'],
            'request_uri' => $_SERVER['REQUEST_URI'],
            'type' => $type, 
            'section' => $section, 
            'auth_id' => $this->auth->getAuthId()]);
            
        $this->triggerEvents($message, $type);

    }   
}
