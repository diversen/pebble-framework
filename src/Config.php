<?php

declare(strict_types=1);

namespace Pebble;

use Pebble\File;

use Exception;

class Config
{
    /**
     * @param array<string> $config_files
     */
    public function __construct(array $config_files = [])
    {
        foreach ($config_files as $file) {
            $this->readConfig($file);
        }
    }

    /**
     * Var holding all config variables
     * @var array<mixed>
     * 
     */
    private array $variables = [];

    /**
     * Var holding all sections
     * @var array<mixed>
     */
    private array $sections = [];

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
     * @return array<mixed>
     */
    private function getConfigArray(string $dir, string $file): array
    {
        $config_file = $dir . "/$file";
        $config_array = require($config_file);
        return $config_array;
    }

    /**
     * Only php files a valid from a configuration dir.
     * Remove everything that is not a config file.
     * @param array<string> $files
     * @return array<string>
     */
    private function getCleanedFiles(array $files): array
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
    public function readConfig(string $dir): void
    {
        if (!file_exists($dir)) {
            throw new Exception('Config exception. Before reading a config dir, you need to make sure the dir exist: ' . $dir);
        }

        $files = File::dirToArray($dir);
        $files = $this->getCleanedFiles($files);

        foreach ($files as $file) {
            $config_array = $this->getConfigArray($dir, $file);
            if (!is_array($config_array)) {
                throw new Exception("Config exception. The file $dir/$file needs to return an array. Return type is " . gettype($config_array));
            }

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
     * Get a config section. E.g. 'SMTP' will get the configuration from the file 'config/SMTP.php'
     * @param array<mixed> $config_array
     * @return array<mixed>
     */
    private function getSectionByName(string $section, array $config_array): array
    {
        $ret = [];
        foreach ($config_array as $key => $value) {
            $ret[$section . '.' . $key] = $value;
        }
        return $ret;
    }

    /**
     * Get e.g. `Config::get('SMTP.username')`
     * @return mixed 
     */
    public function get(string $key)
    {
        if (isset($this->variables[$key])) {
            return $this->variables[$key];
        }
    }

    /**
     * Get section of configuration, e.g. `Config::get('DB')`
     * @return array<mixed> 
     */
    public function getSection(string $key): array
    {
        if (isset($this->sections[$key])) {
            return $this->sections[$key];
        }
        return [];
    }
}
