<?php
namespace App\Controller;

use App\Util\Config;

class BaseController
{
    protected $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }
}
