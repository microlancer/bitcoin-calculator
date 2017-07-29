<?php

namespace App\Controller;

use App\Controller\ViewController;
use App\Util\Config;
use App\Util\View;
use App\Model\Constants\Frequency;
use App\Model\Constants\Api;
use App\Model\Constants\DefaultInput;
use App\Util\HttpParams;

class IndexController extends ViewController
{

    public function __construct(Config $config, View $view)
    {
        parent::__construct($config, $view);
    }

    public function indexAction(HttpParams $params)
    {
        $this->view->addVars($params->toArray());

        ini_set('error_reporting', E_ALL);
        ini_set('display_errors', true);

        $amount = sprintf("%.2f", $params->get('amount', DefaultInput::AMOUNT));

        if ($amount < DefaultInput::MIN_AMOUNT) {
            $amount = DefaultInput::AMOUNT;
        }

        $freq = $params->get('freq', DefaultInput::FREQUENCY);

        if (!in_array($freq, Frequency::ALL)) {
            $freq = DefaultInput::FREQUENCY;
        }

        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[] = date("F", strtotime("2010-$i-01"));
        }

        $month = $params->get('month', DefaultInput::MONTH);

        if (!in_array($month, $months)) {
            $month = DefaultInput::MONTH;
        }

        $day = $params->get('day', DefaultInput::DAY);

        if ($day < 1 || $day > 31) {
            $day = DefaultInput::DAY;
        }

        $years = [];
        $oldestYear = date("Y", strtotime(Api::OLDEST_API_PRICE_DATE));
        for ($i = $oldestYear; $i <= date("Y"); $i++) {
            $years[] = $i;
        }

        $year = $params->get('year', DefaultInput::YEAR);

        if (!in_array($year, $years)) {
            $year = DefaultInput::YEAR;
        }


        $realDate = strtotime(sprintf("%s %d, %d", $month, $day, $year));

        $month = date("F", $realDate);
        $day = date("d", $realDate);
        $year = date("Y", $realDate);

        if ($realDate > time()) {
            $month = date("F");
            $day = date("d");
            $year = date("Y");
        }

        $oldestTime = strtotime(Api::OLDEST_API_PRICE_DATE);

        if ($realDate < $oldestTime) {
            $month = date("F", $oldestTime);
            $day = date("d", $oldestTime);
            $year = date("Y", $oldestTime);
        }

        // Grab any missing price data
        $pricesFile = file_get_contents(__DIR__ . "/../../data/prices.json");
        $priceData = json_decode($pricesFile, true);

        // If yesterday's date is missing, refresh.

        $yesterday = date("Y-m-d", strtotime(date("Y-m-d") . "-1 day"));
        if (!isset($priceData['bpi'][$yesterday])) {
            $lastPriceDate = array_keys($priceData['bpi'])[count($priceData['bpi']) - 1];
            $apiUrl = Api::COINDESK_API_URL . "?start=$lastPriceDate&end=$yesterday";

            $morePrices = file_get_contents($apiUrl);
            //$morePrices = '{"bpi":{"2017-07-23":2762.6263,"2017-07-24":2779.0438,"2017-07-25":2591.2163,"2017-07-26":2550.18,"2017-07-27":2697.4725,"2017-07-28":2805.1788},"disclaimer":"This data was produced from the CoinDesk Bitcoin Price Index. BPI value data returned as USD.","time":{"updated":"Jul 29, 2017 00:03:00 UTC","updatedISO":"2017-07-29T00:03:00+00:00"}}';

            $morePriceData = json_decode($morePrices, true);
            $priceData['bpi'] = array_merge($priceData['bpi'], $morePriceData['bpi']);
            $jsonData = json_encode($priceData);
            file_put_contents("prices.json", $jsonData);
        }

        // Generate results

        $date = strtotime(sprintf("%s %d, %d", $month, $day, $year));
        $iterMonth = date("m", $date);
        $iterDay = date("d", $date);
        $iterYear = date("Y", $date);

        $moreDates = true;

        $plusFreq = [
            Frequency::DAY => 0,
            Frequency::WEEK => 0,
            Frequency::MONTH => 0,
            Frequency::YEAR => 0,
        ];

        $plusFreq[$freq] = 1;

        $results = [];
        $i = 0;
        $totalCoins = 0;
        $totalSpent = 0;
        do {

            $newDate = strtotime(sprintf(
                            "%04d-%02d-%02d +%d days %+d weeks %+d months %+d years", $iterYear, $iterMonth, $iterDay, $i * $plusFreq[Frequency::DAY], $i * $plusFreq[Frequency::WEEK], $i * $plusFreq[Frequency::MONTH], $i * $plusFreq[Frequency::YEAR]
            ));

            if ($newDate > time()) {
                break;
            }

            $dateCasual = date("F d, Y", $newDate);
            $dateStd = date("Y-m-d", $newDate);

            if (isset($priceData['bpi'][$dateStd])) {
                $price = $priceData['bpi'][$dateStd];
            } else {
                // use previous iteration price (assume unchanged)
                $price = $priceData['bpi'][date("Y-m-d", strtotime(date("Y-m-d") . " -1 day"))];
            }

            $coinsBought = sprintf("%.8f", $amount / $price);

            $totalCoins += $coinsBought;

            $totalSpent += $amount;

            $totalValue = sprintf("%.2f", $totalCoins * $price);

            $results[] = [
                'dateCasual' => $dateCasual,
                'totalCoins' => $totalCoins,
                'coinsBought' => $coinsBought,
                'totalValue' => $totalValue,
                'totalSpent' => $totalSpent,
            ];
            $i++;
        } while ($i < 100000);

        krsort($results);

        $this->view->addVars([
            'amount' => $amount,
            'freq' => $freq,
            'frequencies' => Frequency::ALL,
            'months' => $months,
            'years' => $years,
            'day' => $day,
            'month' => $month,
            'year' => $year,
            'results' => $results,
            'totalCoins' => $totalCoins,
            'totalValue' => $totalValue,
        ]);

        $this->view->render('index');
    }

    public function errorAction($p)
    {
        $this->view->addVars($p);
        $this->view->render('error');
    }
}
