<?php

namespace App\Model\Form;

use App\Util\HttpParams;
use App\Model\Constants\DefaultInput;
use App\Model\Constants\Frequency;
use App\Util\Date;
use App\Model\Api;

class Calculator
{
    private $date;
    
    public function __construct(Date $date)
    {
        $this->date = $date;
    }
    
    public function getSanitizedInputs(HttpParams $params)
    {
        $amount = sprintf("%.2f", $params->get('amount', DefaultInput::AMOUNT));

        if ($amount < DefaultInput::MIN_AMOUNT) {
            $amount = DefaultInput::MIN_AMOUNT;
        }
        
        if ($amount > DefaultInput::MAX_AMOUNT) {
            $amount = DefaultInput::MAX_AMOUNT;
        }

        $freq = $params->get('freq', DefaultInput::FREQUENCY);

        if (!in_array($freq, Frequency::ALL)) {
            $freq = DefaultInput::FREQUENCY;
        }
        
        $month = $params->get('month', DefaultInput::MONTH);

        if (!in_array($month, $this->date->getMonths())) {
            $month = DefaultInput::MONTH;
        }

        $day = $params->get('day', DefaultInput::DAY);

        if ($day < 1 || $day > 31) {
            $day = DefaultInput::DAY;
        }
        
        $years = $this->date->getYears(date("Y", strtotime(Api::OLDEST_API_PRICE_DATE)));

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
        
        return [
            'amount' => $amount,
            'month' => $month,
            'day' => $day,
            'year' => $year,
            'freq' => $freq,
        ];
    }
}