<?php
namespace App\Util;

use PHPUnit_Framework_TestCase;

class AutoloaderTest extends PHPUnit_Framework_TestCase
{
    public function testRegister()
    {
        $autoloader = new Autoloader();
        $autoloader->register();
    }
}
