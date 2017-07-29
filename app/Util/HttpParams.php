<?php

namespace App\Util;

class HttpParams
{   
    private $params;
    
    public function setParams(array $params)
    {
        $this->params = $params;
    }
    
    public function get($name, $default)
    {
        if (isset($this->params[$name])) {
            return $this->params[$name];
        }
        return $default;
    }
    
    public function toArray()
    {
        return $this->params;
    }
}