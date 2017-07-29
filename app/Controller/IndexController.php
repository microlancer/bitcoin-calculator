<?php
namespace App\Controller;

use App\Controller\ViewController;
use App\Model\Form\Login;
use App\Model\Menu;
use App\Util\Config;
use App\Util\Session;
use App\Util\View;

class IndexController extends ViewController
{
    private $session;
    private $loginForm;
    
    public function __construct(Config $config, View $view, Menu $menu, Session $session, Login $loginForm)
    {
        parent::__construct($config, $view, $menu);
        $this->session = $session;
        $this->loginForm = $loginForm;
    }
    
    public function indexAction(array $unfilteredRequestParams)
    {
        $this->view->addVars($unfilteredRequestParams);
    
        $vars = [
            'message' => $this->session->getOnce('message'),
            'mailMessage' => $this->session->getOnce('mailMessage'),
            'loggedIn' => $this->session->get('loggedIn'),
        ];
        
        $vars += $this->loginForm->getState();
    
        $this->view->render('index', $vars);
    }
  
    public function errorAction($p)
    {
        $this->view->addVars($p);
        $this->view->render('error');
    }
}
