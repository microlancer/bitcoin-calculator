<?php
namespace App\Util;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testGetConfig()
    {
        $config = new Config();
        $configArray = $config->getConfig();
        $this->assertNotEmpty($configArray);
    }
    
    public function testGet()
    {
        $config = new Config();
        $dbname = $config->get('dbName');
        $this->assertNotEmpty($dbname);
    }
    
    public function testGetVariableNotDefined()
    {
        $config = new Config();
        $this->expectException(\Exception::class);
        $config->get('somethingNotDefined');
    }
}
