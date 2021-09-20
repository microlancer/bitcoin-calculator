<?php

namespace App\Model;

use App\Model\Api;
use App\Model\Constants\Frequency;

class Calculator
{
    private $api;

    public function __construct(Api $api)
    {
        $this->api = $api;
    }

    public function getResults($calcParams)
    {
        $priceData = $this->api->getPriceData();

        // Generate results

        $date = strtotime(sprintf("%s %d, %d", $calcParams['month'], $calcParams['day'], $calcParams['year']));
        $iterMonth = date("m", $date);
        $iterDay = date("d", $date);
        $iterYear = date("Y", $date);

        $plusFreq = [
            Frequency::DAY => 0,
            Frequency::WEEK => 0,
            Frequency::MONTH => 0,
            Frequency::YEAR => 0,
        ];

        $plusFreq[$calcParams['freq']] = 1;

        $results = [];
        $i = 0;
        $totalCoins = 0;
        $totalSpent = 0;
        $lastPrice = 0;
        do {

            $newDate = strtotime(sprintf(
                "%04d-%02d-%02d +%d days %+d weeks %+d months %+d years",
                $iterYear,
                $iterMonth,
                $iterDay,
                $i * $plusFreq[Frequency::DAY],
                $i * $plusFreq[Frequency::WEEK],
                $i * $plusFreq[Frequency::MONTH],
                $i * $plusFreq[Frequency::YEAR]
            ));

            if ($newDate > time()) {
                break;
            }

            $dateCasual = date("F d, Y", $newDate);
            $dateStd = date("Y-m-d", $newDate);

            if (isset($priceData['bpi'][$dateStd])) {
                $price = $priceData['bpi'][$dateStd];
                $lastPrice = $price;
            } else {
                // use previous iteration price (assume unchanged)
                $price = $lastPrice; //$priceData['bpi'][date("Y-m-d", strtotime(date("Y-m-d") . " -1 day"))];
            }

            if ($price > 0) {
                $coinsBought = sprintf("%.8f", $calcParams['amount'] / $price);
            } else {
                $coinsBought = 0;
            }

            $totalCoins += $coinsBought;

            $totalSpent += $calcParams['amount'];

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

        return [
            'results' => $results,
            'totalCoins' => $totalCoins,
            'totalValue' => $totalValue,
        ];
    }
}
