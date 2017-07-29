<?php
namespace App\Controller;

use App\Controller\ViewController;
use App\Util\Config;
use App\Util\View;

class IndexController extends ViewController
{
    public function __construct(Config $config, View $view)
    {
        parent::__construct($config, $view);
    }
    
    public function indexAction(array $params)
    {
        $this->view->addVars($params); 
        $this->view->render('index');
    }
  
    public function errorAction($p)
    {
        $this->view->addVars($p);
        $this->view->render('error');
    }
}
