<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', true);

require_once __DIR__ . '/../app/Util/Autoloader.php';
require_once __DIR__ . '/../app/Util/Di.php';

use App\Util\Autoloader;
use App\Util\Di;
use App\Util\Mysql;

Di::getInstance()->get(Autoloader::class)->register();

$mysql = Di::getInstance()->get(Mysql::class);
$mysql->upgradeDb();
