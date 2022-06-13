<?php

declare(strict_types=1);

namespace Pebble;

use Pebble\File;

use Exception;

class Config
{
    public function __construct(array $config_files = [])
    {
        foreach ($config_files as $file) {
            $this->readConfig($file);
        }
    }

    /**
     * Var holding all config variables
     */
    public $variables = [];

    /**
     * Var holding all sections
     */
    public $sections = [];

    /**
     * Get filename without extension
     */
    private function getFilename(string $file): string
    {
        $info = pathinfo($file);
        return $info['filename'];
    }

    /**
     * Get config array from a dir and a file
     */
    private function getConfigArray($dir, $file)
    {
        $config_file = $dir . "/$file";
        $config_array = require($config_file);
        return $config_array;
    }

    /**
     * Only php files a vlid from a configuration dir.
     * Remove everything that is not a config file.
     */
    private function getCleanedFiles($files)
    {
        $files_ret = [];
        foreach ($files as $file) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if ($ext !== 'php') {
                continue;
            }
            $files_ret[] = $file;
        }
        return $files_ret;
    }

    /**
     * Read all configuration files (php files) from dir.
     */
    public function readConfig(string $dir)
    {
        if (!file_exists($dir)) {
            throw new Exception('Before reading a config dir, you need to make sure the dir exist: ' . $dir);
        }

        $files = File::dirToArray($dir);
        $files = $this->getCleanedFiles($files);

        foreach ($files as $file) {
            $config_array = $this->getConfigArray($dir, $file);
            $filename = $this->getFilename($file);

            if (isset($this->sections[$filename])) {
                $this->sections[$filename] = array_merge($this->sections[$filename], $config_array);
            } else {
                $this->sections[$filename] = $config_array;
            }

            $this->variables = array_merge($this->variables, $this->getSectionByName($filename, $config_array));
        }
    }

    /**
     * get a config section. E.g. 'SMTP' will get the configuration from the file 'config/SMTP.php'
     */
    private function getSectionByName(string $section, array $configAry): array
    {
        $ret = [];
        foreach ($configAry as $key => $value) {
            $ret[$section . '.' . $key] = $value;
        }
        return $ret;
    }

    /**
     * Get e.g. `Config::get('SMTP.username')`
     */
    public function get(string $key)
    {
        if (isset($this->variables[$key])) {
            return $this->variables[$key];
        }
        return null;
    }

    /**
     * Get section of configuration, e.g. `Config::get('DB')`
     */
    public function getSection(string $key): array
    {
        if (isset($this->sections[$key])) {
            return $this->sections[$key];
        }
        return [];
    }
}
