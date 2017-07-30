<?php
namespace App\Util;

/**
 * @codeCoverageIgnore
 */
class HeaderParams
{
    public function set($str)
    {
        header($str);
    }

    public function setResponseCode($code)
    {
        http_response_code($code);
    }
    
    public function redirect($path)
    {
        $config = Di::getInstance()->get(Config::class);
        header('Location: ' . $config->get('baseUrl') . '/' . $path);
    }
}
