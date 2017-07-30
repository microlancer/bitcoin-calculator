<?php

namespace App\Controller;

use App\Controller\ViewController;
use App\Util\Config;
use App\Util\View;
use App\Model\Constants\Frequency;
use App\Util\HttpParams;
use App\Util\Date;
use App\Model\Form\Calculator as CalculatorForm;
use App\Model\Api;
use App\Model\Calculator;

class IndexController extends ViewController
{
    private $date;
    private $calculatorForm;
    private $calculator;

    public function __construct(Config $config, View $view, Date $date, CalculatorForm $calculatorForm, Calculator $calculator)
    {
        parent::__construct($config, $view);
        $this->date = $date;
        $this->calculatorForm = $calculatorForm;
        $this->calculator = $calculator;
    }

    public function indexAction(HttpParams $params)
    {
        $this->view->addVars($params->toArray());

        $calcParams = $this->calculatorForm->getSanitizedInputs($params);
        $calcResults = $this->calculator->getResults($calcParams);
        
        $this->view->addVars([
            'amount' => $calcParams['amount'],
            'freq' => $calcParams['freq'],
            'frequencies' => Frequency::ALL,
            'months' => $this->date->getMonths(),
            'years' => $this->date->getyears(date("Y", strtotime(Api::OLDEST_API_PRICE_DATE))),
            'day' => $calcParams['day'],
            'month' => $calcParams['month'],
            'year' => $calcParams['year'],
            'results' => $calcResults['results'],
            'totalCoins' => $calcResults['totalCoins'],
            'totalValue' => $calcResults['totalValue'],
        ]);

        $this->view->render('index');
    }

    public function errorAction($p)
    {
        $this->view->addVars($p);
        $this->view->render('error');
    }
}
