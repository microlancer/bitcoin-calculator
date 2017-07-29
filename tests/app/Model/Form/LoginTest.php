<?php
namespace Test\App\Model\Form;

use App\Model\Auth;
use App\Model\Form\Login;
use App\Util\Session;
use App\Util\Validator;

class LoginTest extends \PHPUnit_Framework_TestCase
{
    use \Test\WithMockHelper;
    use WithLoginExpectations;
    
    /** @var LoginForm $form */
    private $form;
    
    /** @var array $params */
    private $params;
    
    /** @var array $expectedErrors */
    private $expectedErrors;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->setUpMocksForExpectations();
        
        $this->form = new Login(
            $this->{Validator::class}, 
            $this->{Session::class}, 
            $this->{Auth::class}
        );        
        
        $this->params = [
            'email' => 'foo@bar.com',
            'password' => 'somepass123',
        ];
        
        $this->expectedErrors = [
            'email' => '',
            'password' => '',
        ];
    }
    
    public function testValidate()
    {   
        $this->expectUtilValidatorPass();
        $this->expectAuthCheckPasswordPass();
        $this->form->validate($this->params);
     
        $this->assertValidationSuccess();
    }
    
    public function testValidateBadEmailChars()
    {
        $this->expectUtilValidatorFailInvalidEmailChars();
        $this->setMockExpectations(Auth::class, []);
        $this->expectedErrors['email'] = 'Incorrect email/password.';
        $this->form->validate($this->params);
        $this->assertValidationFailure();
    }
    
    public function testValidateBadPasswordChars()
    {
        $this->expectUtilValidatorFailInvalidPasswordChars();
        $this->setMockExpectations(Auth::class, []);
        $this->expectedErrors['email'] = 'Incorrect email/password.';
        $this->form->validate($this->params);
        $this->assertValidationFailure();
    }
    
    public function testValidateWrongPassword()
    {
        $this->expectUtilValidatorPass();
        $this->expectAuthCheckPasswordFail();
        $this->expectedErrors['email'] = 'Incorrect email/password.';
        $this->form->validate($this->params);
        $this->assertValidationFailure();
    }
    
    private function assertValidationSuccess()
    {
        $this->assertFalse($this->form->hasErrors());
        $this->assertEquals('foo@bar.com', $this->form->email);
        $this->assertEquals('', $this->form->password);
        $this->assertEquals($this->expectedErrors, $this->form->getErrors());
    }
    
    private function assertValidationFailure()
    {
        $this->assertTrue($this->form->hasErrors());
        $this->assertEquals('foo@bar.com', $this->form->email);
        $this->assertEquals('', $this->form->password);
        $this->assertEquals($this->expectedErrors, $this->form->getErrors());
    }
}
