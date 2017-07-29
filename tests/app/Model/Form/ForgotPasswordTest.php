<?php
namespace App\Model\Form;

use App\Model\Users;
use App\Util\Validator;

class ForgotPasswordTest extends \PHPUnit_Framework_TestCase
{
    use \Test\WithMockHelper;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->setupMocks([
            Validator::class,
            Users::class,
        ]);
        
        $this->params = [
            'email' => 'foo@bar.com',
        ];
    }
    
    public function testValidateGoodEmail()
    {
        $this->tryValidate(true, true, false, $this->params);
    }
    
    public function testValidateBadEmailString()
    {
        $this->tryValidate(false, null, true, $this->params);
    }
    
    public function testValidateEmailDoesNotExist()
    {
        $this->tryValidate(true, false, true, $this->params);
    }
    
    public function testNoParams()
    {
        $this->expectException(\Exception::class);
        $this->tryValidate(null, null, null, []);
    }
    
    private function tryValidate($isEmailChars, $emailExists, $hasErrors, array $params)
    {
        $this->setMockExpectations(Validator::class, [
            'isEmailChars' => [$this->any(), ['foo@bar.com'], $isEmailChars],
        ]);
        
        $this->setMockExpectations(Users::class, [
            'emailExists' => [$this->any(), ['foo@bar.com'], $emailExists],
        ]);
        
        $form = new ForgotPassword($this->{Validator::class}, $this->{Users::class});
        
        $form->validate($params);
        
        $this->assertEquals($hasErrors, $form->hasErrors());
        
        if (!$hasErrors) {
            $this->assertEquals('foo@bar.com', $form->email);
            $this->assertEquals(['email' => ''], $form->getErrors());
        } else {
            $this->assertEquals('', $form->email);
            $this->assertEquals(['email' => 'Invalid email address.'], $form->getErrors());
        }
        
    }
}
