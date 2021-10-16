<?php

namespace Pebble\Log;

use Pebble\Log\Base;
use Exception;

/**
 * Stream logging
 */
class Stream extends Base {
 
    /**
     * Create a log 
     * `$log = new Log(['stream' => 'php://stderr'])` or  `$log = new Log(['log_dir' => './logs'])`
     * ]);`
     */
    public function __construct(array $options = [])
    {

        if (!isset($options['stream'])) {
            throw new Exception("The \Pebble\Log\Stream __construct method expects a stream, e.g: 'stream' => 'php://stderr' ");
        }

        $this->options = $options;
    }

    /**
     * Log a message
     * @param string $message
     * @param string $type RFC types are: 'debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'
     * @param string $custom_log_file 
     */
    public function message($message, string $type = 'debug', ?string $custom_log_file = null): void
    {

        $log_message = $this->getMessage($message, $type);
        if (isset($this->options['stream'])) {
            file_put_contents($this->options['stream'], $log_message, FILE_APPEND);
        }
        
        $this->triggerEvents($log_message, $type);

    }   
}
