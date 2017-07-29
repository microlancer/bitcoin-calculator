<?php
namespace App\Util;

use App\Model\User;
use App\Model\Users;
use App\Util\Di;
use App\Util\Mysql;
use PHPUnit_Framework_TestCase;

class UsersTest extends PHPUnit_Framework_TestCase
{
    use \Test\WithMockHelper;
    
    /**
     * @group db
     */
    public function testEmailExists()
    {
        $mockSession = $this->getMockBuilder(Session::class)
                ->disableOriginalConstructor()
                ->getMock();
                
        Di::getInstance()->set(Session::class, $mockSession);
        $users = Di::getInstance()->get(Users::class);
        $this->assertTrue($users->emailExists('a@b.com'));
    }
    
    public function testAdd()
    {
        $this->setupMocks([Mysql::class, Config::class, Email::class]);
        
        $users = new Users($this->mocks[Mysql::class], $this->mocks[Config::class], $this->mocks[Email::class]);
        
        $this->mocks[Mysql::class]->expects($this->once())
                ->method('query')
                ->willReturn(1);

        $mockUser = $this->getMockBuilder(User::class)
                ->disableOriginalConstructor()
                ->getMock();
        
        $mockUser->email = 'foo@bar.com';
        $mockUser->password = 'pass1234';
        
        $users->add($mockUser);
    }
    
    /**
     * @group db
     */
    public function testAddMysql()
    {
        /** @var Users $users */
        $users = Di::getInstance()->get(Users::class);
        
        try {
            // Cleanup test
            $users->delete('support@whebsite.com');
        } catch (\Exception $e) {
        }
        
        $user = Di::getInstance()->create(User::class);
        $user->email = 'support@whebsite.com';
        $user->password = 'pass123';
        
        $users->add($user);
        
        // Verify
        $this->assertTrue($users->emailExists('support@whebsite.com'));
        
        // Cleanup test
        $users->delete('support@whebsite.com');
    }
}
