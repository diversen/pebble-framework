<?php

declare(strict_types=1);

namespace Pebble\Service;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Pebble\Service\Container;
use Pebble\Path;
use Pebble\Service\ConfigService;

class LogService extends Container
{

    private $debug_level = Logger::DEBUG;

    /**
     * Returns a logger that will log to logs/main.log
     * Debug level can be set in setting `App.debug_level`
     * If not set `App.debug_level` is `Logger::DEBUG` (100)
     *
     * @return \Monolog\Logger
     */
    public function getLog()
    {
        
        if (!$this->has('log')){
            // Get log from config
            $log = $this->getLogFromConfig();
            if ($log) {
                $this->set('log', $log);
            } else {
                $this->set('log', $this->getDefaultLogger());
            }
        }

        return $this->get('log');

    }

    /**
     * Get a log instance from `config/Log.php`
     */
    private function getLogFromConfig() {
        $config = (new ConfigService())->getConfig();
        if ($config->get('Log.logger')) {
            return $config->get('Log.logger');
        }
    }

    /**
     * The default logger logs to the file logs/main.log
     */
    private function getDefaultLogger() {

        $config = (new ConfigService())->getConfig();
        if ($config->get('Log.level')) {
            $this->debug_level = $config->get('Log.level');
        }

        $logger = new Logger('base');
        $base_path = Path::getBasePath();
        $logger->pushHandler(new StreamHandler($base_path . '/logs/main.log', $this->debug_level));
        return $logger;
    }
}
