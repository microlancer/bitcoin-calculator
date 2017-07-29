<?php

namespace App\Controller;

use App\Util\Config;
use App\Util\HeaderParams;

class ApiController extends BaseController
{
    const CONTENT_TYPE_JSON = 'Content-type: application/json';

    private $headers;

    public function __construct(Config $config, HeaderParams $headers)
    {
        parent::__construct($config);
        $this->headers = $headers;
    }

    public function usersAction(array $unfilteredRequestParams)
    {
        $this->headers->set(self::CONTENT_TYPE_JSON);
        echo json_encode($unfilteredRequestParams);
    }
}
