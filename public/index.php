<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', true);

require_once '../app/Util/Autoloader.php';
require_once '../app/Util/Di.php';

use App\Util\Autoloader;
use App\Util\Di;
use App\Util\Route;
use App\Util\View;

Di::getInstance()->get(Autoloader::class)->register();

/** @var Route $route */
$route = Di::getInstance()->get(Route::class);

$route->addResources([
    'index/index',
    'index/error',
    'user/login',
    'user/login-submit',
    'user/signup',
    'user/signup-submit',
    'user/logout',
    'user/verify',
    'user/needs-verification',
    'user/resend-verification',
    'user/forgot-password',
    'user/forgot-password-submit',
    'user/password-reset-verify',
    'user/password-reset-submit',
    'help/faq',
    'user/account',
    'biz/start',
    'biz/list',
    'jobs/list',
    'api/v1/users',
    'api/v1/jobs',
    'api/v1/job_ratings',
    'api/v1/job_history',
    'api/v1/btc_transaction_history',
    'markets',
]);

try {
    $route->dispatch($_REQUEST);
} catch (\Exception $e) {
    Di::getInstance()->get(View::class)->render('error');
    throw $e;
}
