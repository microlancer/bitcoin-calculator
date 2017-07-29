<?php

namespace App\Util;

class Std
{
    /**
     * Returns true of all array keys exist.
     *
     * @param type $keys
     * @param type $array
     */
    public static function arrayKeysExist(array $keys, array $array)
    {
        foreach ($keys as $key) {
            if (!isset($array[$key])) {
                return false;
            }
        }
        return true;
    }
}
