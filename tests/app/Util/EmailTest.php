<?php

namespace App\Util;

class EmailTest extends \PHPUnit_Framework_TestCase
{
    use \Test\WithMockHelper;
    
    public function setUp()
    {
        parent::setUp();
        $this->setupMocks([
            Config::class,
            Session::class,
        ]);
    }
    
    public function testSendWithoutParamsThrowsException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('email and subject required');
        $email = new Email($this->{Config::class}, $this->{Session::class});
        $email->send('verify-code', []);
    }
    
    /**
     * @group email
     */
    public function testSendWithTemplate()
    {
        $mockConfig = $this->getMockBuilder(Config::class)
                ->disableOriginalConstructor()
                ->getMock();
        
        $mockSession = $this->getMockBuilder(Session::class)
                ->disableOriginalConstructor()
                ->getMock();
        
        $email = new Email($mockConfig, $mockSession);
        $params = [
            'email' => 'support@whebsite.com',
            'subject' => 'CoinShare - Please verify your email address',
            'verifyLink' => 'https://for-bitcoin.com/coinshare/user/verify?verifyCode=123&email=' . 'support@whebsite.com',
        ];
        $email->send('verify-code', $params);
    }
}
