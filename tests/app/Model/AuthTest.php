<?php
namespace App\Util;

use App\Model\Auth;
use App\Util\Di;

class AuthTest extends \PHPUnit_Framework_TestCase
{
    public function testCheckPassword()
    {
        $mockMysql = $this->getMockBuilder(Mysql::class)
                ->disableOriginalConstructor()
                ->getMock();
        
        $rows = [
            [
                'password' => '$2y$10$cYAmNXFNvE6tQDEDLRO/qujLsnEQTy8DnytyOM6VdcVhBo4e6dhs2'
            ]
        ];
        
        $mockMysql->expects($this->once())
                ->method('query')
                ->willReturn($rows);
        
        $auth = new Auth($mockMysql);
        
        $result = $auth->checkPassword('a@b.com', 'pass123');
        
        $this->assertTrue($result);
    }
    
    /**
     * @group db
     */
    public function testCheckPasswordMysql()
    {
        $auth = Di::getInstance()->get(Auth::class);
        $result = $auth->checkPassword('a@b.com', 'pass123');
        $this->assertTrue($result);
    }
}
