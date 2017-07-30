<?php

namespace App\Util;

class Config
{
    public static $config;

    /**
     * Get configuration array from file.
     *
     * @return array
     */
    public function getConfig()
    {
        if (!isset(self::$config)) {
            self::$config = include dirname(__FILE__) . '/../../config.php';
        }
        return self::$config;
    }

    /**
     * Get a specific config value, by name.
     *
     * @param string $var
     * @return mixed
     * @throws \Exception
     */
    public function get($var)
    {
        if (!isset($this->getConfig()[$var])) {
            throw new \Exception("Config variable `$var` must be defined in config.php");
        }
        return $this->getConfig()[$var];
    }
}
