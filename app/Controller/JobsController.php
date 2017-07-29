<?php

namespace App\Controller;

use App\Model\Menu;
use App\Util\Config;
use App\Util\View;

class JobsController extends ViewController
{
    public function __construct(Config $config, View $view, Menu $menu)
    {
        parent::__construct($config, $view, $menu);
    }
    
    public function listAction(array $p)
    {
        $this->view->addVars($p);
        $this->view->render('jobs/list');
    }
}
