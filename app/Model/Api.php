<?php

namespace App\Model;

class Api
{
    const OLDEST_API_PRICE_DATE = '2010-07-18';
    const COINDESK_API_URL = 'https://api.coindesk.com/v1/bpi/historical/close.json';
    const PRICE_FILE = '../../data/prices.json';
    
    public function getPriceData()
    {
        $priceFile = __DIR__ . '/' . self::PRICE_FILE;
        
        // Grab any missing price data
        $pricesFile = file_get_contents($priceFile);
        $priceData = json_decode($pricesFile, true);

        // If yesterday's date is missing, refresh.

        $yesterday = date("Y-m-d", strtotime(date("Y-m-d") . "-1 day"));
        if (!isset($priceData['bpi'][$yesterday])) {

          if (count($priceData['bpi']) == 0) {
            $lastPriceDate = self::OLDEST_API_PRICE_DATE;
            $priceData['bpi'] = [];
          } else {
            $lastPriceDate = array_keys($priceData['bpi'])[count($priceData['bpi']) - 1];
           }
            

            $apiUrl = self::COINDESK_API_URL . "?start=$lastPriceDate&end=$yesterday";

            $morePrices = file_get_contents($apiUrl);
            //$morePrices = '{"bpi":{"2017-07-23":2762.6263,"2017-07-24":2779.0438,"2017-07-25":2591.2163,"2017-07-26":2550.18,"2017-07-27":2697.4725,"2017-07-28":2805.1788},"disclaimer":"This data was produced from the CoinDesk Bitcoin Price Index. BPI value data returned as USD.","time":{"updated":"Jul 29, 2017 00:03:00 UTC","updatedISO":"2017-07-29T00:03:00+00:00"}}';


            $morePriceData = json_decode($morePrices, true);
            $priceData['bpi'] = array_merge($priceData['bpi'], $morePriceData['bpi']);
            $jsonData = json_encode($priceData);
            file_put_contents($priceFile, $jsonData);
        }
        
        return $priceData;
    }
}
