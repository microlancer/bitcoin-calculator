<?php
namespace App\Util;

class RouteTest extends \PHPUnit_Framework_TestCase
{
    public function testRoute()
    {
        $mockHeaderParams = $this->getMockBuilder(HeaderParams::class)
                ->disableOriginalConstructor()
                ->getMock();
        
        $mockSession = $this->getMockBuilder(Session::class)
                ->disableOriginalConstructor()
                ->getMock();
        
        Di::getInstance()->set(Session::class, $mockSession);
        
        $route = new Route($mockHeaderParams);
        $resources = [
            'user/signup',
            'user/login',
        ];
        $route->addResources($resources);
        $params = [
            'q' => 'user/login'
        ];
        ob_start();
        $route->dispatch($params);
        $output = ob_get_clean();
        $this->assertNotEmpty($output);
    }
}
