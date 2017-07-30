<?php

require_once dirname(__FILE__) . '/../app/Util/Autoloader.php';
require_once __DIR__ . '/../app/Util/Di.php';

use App\Util\Autoloader;
use App\Util\Di;

Di::getInstance()->get(Autoloader::class)->register();
Di::getInstance()->get(Autoloader::class)->registerTests();
