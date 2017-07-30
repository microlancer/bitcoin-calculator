<?php
namespace App\Controller;

use App\Util\Config;
use App\Util\View;

abstract class ViewController extends BaseController
{
    protected $view;

    public function __construct(Config $config, View $view)
    {
        parent::__construct($config);
        $this->view = $view;
        $this->view->addVars($this->config->getConfig());
    }
}
