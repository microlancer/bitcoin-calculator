<?php

namespace App\Controller;

use App\Model\Menu;
use App\Util\Config;
use App\Util\View;

class BizController extends ViewController
{
    public function __construct(Config $config, View $view, Menu $menu)
    {
        parent::__construct($config, $view, $menu);
    }
    
    public function startAction(array $p)
    {
        $this->view->addVars($p);
        $this->view->render('biz/start');
    }
    
    public function listAction(array $p)
    {
        $this->view->addVars($p);
        $this->view->render('biz/list');
    }
}
