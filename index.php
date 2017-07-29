<?php

class DefaultInput
{
  const MIN_AMOUNT = 0.01;
  const AMOUNT = 20;
  const FREQUENCY = 'week';
  const MONTH = 'January';
  const DAY = 1;
  const YEAR = '2011';
}

class Frequency
{
  const DAY = 'day';
  const WEEK = 'week';
  const MONTH = 'month';
  const YEAR = 'year';
  const ALL = [self::DAY, self::WEEK, self::MONTH, self::YEAR];
}

class Config
{
  const OLDEST_API_PRICE_DATE = '2010-07-18';
}

ini_set('error_reporting', E_ALL);
ini_set('display_errors', true);

$amount = sprintf("%.2f", isset($_GET['amount']) ? $_GET['amount'] : 0);

if ($amount < DefaultInput::MIN_AMOUNT) { 
  $amount = DefaultInput::AMOUNT; 
}

$freq = isset($_GET['freq']) ? $_GET['freq'] : DefaultInput::FREQUENCY;

if (!in_array($freq, Frequency::ALL)) {
  $freq = DefaultInput::FREQUENCY;
}

$months = [];
for ($i=1; $i<=12; $i++) {
  $months[] = date("F", strtotime("2010-$i-01"));
}

$month = isset($_GET['month']) ? $_GET['month'] : DefaultInput::MONTH;

if (!in_array($month, $months)) {
  $month = DefaultInput::MONTH;
}

$day = isset($_GET['day']) ? intval($_GET['day']) : DefaultInput::DAY;

if ($day < 1 || $day > 31) {
  $day = DefaultInput::DAY;
}

$years = [];
$oldestYear = date("Y", strtotime(Config::OLDEST_API_PRICE_DATE));
for ($i=$oldestYear; $i<=date("Y"); $i++) {
  $years[] = $i;
}

$year = isset($_GET['year']) ? $_GET['year'] : DefaultInput::YEAR;

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

$oldestTime = strtotime(Config::OLDEST_API_PRICE_DATE);

if ($realDate < $oldestTime) {
  $month = date("F", $oldestTime);
  $day = date("d", $oldestTime);
  $year = date("Y", $oldestTime);
}

/*var_dump($amount);
var_dump($freq);
var_dump($months);
var_dump($month);
var_dump($day);
var_dump($years);
var_dump($year);*/


// Grab any missing price data

$pricesFile = file_get_contents("prices.json");
$priceData = json_decode($pricesFile, true);

//krsort($priceData['bpi']);
//var_dump($priceData['bpi']);

// If yesterday's date is missing, refresh.

$yesterday = date("Y-m-d", strtotime(date("Y-m-d") . "-1 day"));
if (!isset($priceData['bpi'][$yesterday])) {
  $lastPriceDate = array_keys($priceData['bpi'])[count($priceData['bpi'])-1];
  $apiUrl = "https://api.coindesk.com/v1/bpi/historical/close.json?start=$lastPriceDate&end=$yesterday";

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

//var_dump($iterYear, $iterDay, $iterMonth);

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
      "%04d-%02d-%02d +%d days %+d weeks %+d months %+d years", 
      $iterYear, 
      $iterMonth, 
      $iterDay, 
      $i*$plusFreq[Frequency::DAY], 
      $i*$plusFreq[Frequency::WEEK], 
      $i*$plusFreq[Frequency::MONTH], 
      $i*$plusFreq[Frequency::YEAR] 
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

  //var_dump($price);
  //var_dump($amount);

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

?><!DOCTYPE html>
<html lang="en">
<head>
  <title> Build a Bitcoin Nest Egg for your future!  </title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Bootstrap -->
      <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
</head>
<body>
<div class="container">
<h1><img src="bitcoin.png" width='35' style='margin-bottom:5px'>&nbsp;Bitcoin Savings Calculator</h1>


<h3>Savings Plan</h3>

<form method='get' class="form-inline">

What if I saved 

      $ <input class="form-control col-sm-2" style="width:70px" type='text' value='<?=$amount?>' size='6' name='amount'>
as bitcoin every 
<select name='freq' style="width:90px">
<?php foreach (Frequency::ALL as $freqOption) { ?>
<option <?=($freq == $freqOption ? 'selected="true"' : '')?>"><?=$freqOption?></option>
<?php } ?>
</select> 
starting on 
<select name='month' style="width:100px">
<?php foreach ($months as $monthOption) { ?>
<option <?=($month == $monthOption ? 'selected="true"' : '')?>"><?=$monthOption?></option>
<?php } ?>
</select> 
<input name='day' type='text' size='4' value='<?=$day?>' style="width:30px"></input>, 
<select name='year' style="width:70px">
<?php foreach ($years as $yearOption) { ?>
<option <?=($year == $yearOption ? 'selected="true"' : '')?>"><?=$yearOption?></option>
<?php } ?>
</select> 
?

<button type='submit' class='btn btn-primary' value='Calculate'>Calculate</button>
</form>

<h3>Results</h3>

<div class="alert alert-success lead" role="alert">
I would have <b><?=sprintf("%.4f", $totalCoins);?></b> bitcoin (worth $<b><?=number_format($totalValue, 2);?></b>) today.
</div>

<table class="table">
<tr style='background-color:lightblue;font-weight:bold'><td>Date</td><td>Spent So Far</td><td>Amount of Bitcoin I Own</td><td>Value of My Savings</td></tr>
<?php foreach ($results as $result) { ?>
<tr>
  <td><?=$result['dateCasual']?></td>
  <td>$<?=number_format($result['totalSpent'], 2);?></td>
  <td><?=number_format($result['totalCoins'], 4);?></td>
  <td>$<?=number_format($result['totalValue'], 2);?></td></tr>
<?php } ?>
</table>

<p><i>*Note: Does not include transaction fees. All calculations rounded. Price data from CoinDesk API.</i></p>
</div>
<script src="https://code.jquery.com/jquery.js"></script>
 <script src="bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
