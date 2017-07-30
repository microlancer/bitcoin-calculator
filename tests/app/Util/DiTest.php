<?php
namespace App\Util;

use PHPUnit_Framework_TestCase;

class DiTest extends PHPUnit_Framework_TestCase
{
    public function testGetInstance()
    {
        $di = Di::getInstance();
        $this->assertInstanceOf(Di::class, $di);
    }
    
    public function testGetSimpleClass()
    {
        $di = Di::getInstance();
        $object = $di->get(\XMLReader::class);
        $this->assertInstanceOf(\XMLReader::class, $object);
    }
    
    public function testGetHandlerClass()
    {
        $di = Di::getInstance();
        $di->register('foo', function () {
            return new \XMLWriter();
        });
        $object = $di->get('foo');
        $this->assertInstanceOf(\XMLWriter::class, $object);
    }
}
