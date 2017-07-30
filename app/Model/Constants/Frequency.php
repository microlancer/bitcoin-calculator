<?php

namespace App\Model\Constants;

class Frequency
{
  const DAY = 'day';
  const WEEK = 'week';
  const MONTH = 'month';
  const YEAR = 'year';
  const ALL = [self::DAY, self::WEEK, self::MONTH, self::YEAR];
}
