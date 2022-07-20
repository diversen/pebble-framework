<?php

declare(strict_types=1);

namespace Pebble\Service;

use Pebble\Service\Container;
use Pebble\Config;
use Pebble\Path;

class ConfigService extends Container
{

    /**
     * @return \Pebble\Config
     */
    public function getConfig()
    {

        if (!$this->has('config')) {
            $base_path = Path::getBasePath();
            $config = new Config();
            $config->readConfig($base_path . '/config');
            $config->readConfig($base_path . '/config-locale');
            $this->set('config', $config);
        }

        return $this->get('config');
    }
}
