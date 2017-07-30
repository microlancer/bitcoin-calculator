<?php

namespace App\Util;

class Date
{
    public function getMonths()
    {
        
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[] = date("F", strtotime("2010-$i-01"));
        }
        
        return $months;
    }
    
    public function getYears($startYear)
    {
        $years = [];
        for ($i = $startYear; $i <= date("Y"); $i++) {
            $years[] = $i;
        }
        return $years;
    }
}