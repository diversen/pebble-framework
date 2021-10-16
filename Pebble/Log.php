<?php declare (strict_types = 1);

namespace Pebble;

class Log
{

    /**
     * Create log message
     */
    public function getMessage($message, string $type): string
    {
        if (!is_string($message)) {
            $message = var_export($message, true);
        }

        // Add REMOTE_ADDR and REMOTE_PORT
        $remote_addr = $_SERVER['REMOTE_ADDR'] ?? 'NO_REMOTE_ADDR';
        $remote_port = $_SERVER['REMOTE_PORT'] ?? 'NO_REMOTE_PORT';
        $remote = $remote_addr . ':' . $remote_port;

        // Generate message
        $time_stamp = date('Y-m-d H:i:s');
        $log_message = "[$time_stamp]" . ' ' . "$remote" . ' ' . strtoupper($type) . ' ' . $message . PHP_EOL;
        return $log_message;
    }

    /**
     * Trigger special log events
     */
    public function triggerEvents($log_message, $type)
    {
        foreach ($this->events as $event) {
            if (in_array($type, $event['types'])) {
                $callable = $event['method'];
                $callable($log_message);
            }
        }
    }

    /**
     * Varaible hold $events
     */
    public $events = [];

    /**
     * Add an event to a log type, e.g. 'alert' or 'emergency' using a callable
     */
    public function on(array $types = [], callable $method = null)
    {

        $event = [
            'types' => $types,
            'method' => $method,
        ];

        $this->events[] = $event;
    }
}
