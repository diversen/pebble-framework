<?php

namespace Pebble\Log;

use Pebble\Log;
use Exception;

class File extends Log {
 
        /**
     * Create a log 
     * `$log = new Log(['stream' => 'php://stderr'])` or  `$log = new Log(['log_dir' => './logs'])`
     * ]);`
     */
    public function __construct(array $options = [])
    {

        if (!isset($options['log_dir']) && !isset($options['stream'])) {
            throw new Exception("The \Pebble\Log __construct method expects a log dir -> 'log_dir' => './logs' (log into a file) or a stream, e.g: 'stream' => 'php://stderr' ");
        }

        $this->options = $options;
    }

    /**
     * Get log file from configuration
     */
    private function getLogFile(?string $custom_log_file = null) {
        $log_dir = $this->options['log_dir'] . '/';

        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0777, true);
        }

        // Default log file
        $log_file = $log_dir . '/main.log';
        if ($custom_log_file) {
            $log_file = $log_dir . '/' . $custom_log_file;
        }

        return $log_file;
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
        if (isset($this->options['log_dir'])) {
            $log_file = $this->getLogFile($custom_log_file);
            file_put_contents($log_file, $log_message, FILE_APPEND);
        }

        if (isset($this->options['stream'])) {
            file_put_contents($this->options['stream'], $log_message, FILE_APPEND);
        }
        

        $this->triggerEvents($log_message, $type);

    }
    
}