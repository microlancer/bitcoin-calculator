<?php

namespace App\Controller;

class HelpController extends ViewController
{
    public function faqAction(array $p)
    {
        $this->view->addVars($p);
        $this->view->render('faq');
    }
}
