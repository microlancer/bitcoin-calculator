<?php
namespace Test\App\Model\Form;

use App\Model\Form\Signup;
use App\Model\Users;
use App\Util\Session;
use App\Util\Validator;

class SignupTest extends \PHPUnit_Framework_TestCase
{
    use \Test\WithMockHelper;
    use WithSignupExpectations;
    
    /** @var LoginForm $form */
    private $form;
    
    /** @var array $params */
    private $params;
    
    /** @var array $expectedFormValues */
    private $expectedFormValues;
    
    /** @var array $expectedErrors */
    private $expectedErrors;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->setUpMocksForExpectations();
        
        $this->form = new Signup(
            $this->{Validator::class}, 
            $this->{Users::class}, 
            $this->{Session::class}
        );
        
        $this->params = [
            'email' => 'foo@bar.com',
            'password' => 'pass123',
        ];
        
        $this->expectedErrors = [
            'email' => '',
            'password' => '',
        ];
        
        $this->expectedFormValues = [
            'email' => 'foo@bar.com',
            'password' => 'pass123',
        ];
    }
    
    public function testValidateSuccess()
    {   
        $this->expectUtilValidatorPass();
        $this->expectEmailDoesNotExist();
        $this->form->validate($this->params);
        $this->assertValidateSuccess();
    }
    
    public function testValidateFailureBadEmailChars()
    {   
        $this->expectUtilValidatorFailBadEmailChars();
        $this->setMockExpectations(Users::class, []);
        $this->expectedFormValues['email'] = '';
        $this->expectedFormValues['password'] = '';
        $this->expectedErrors['email'] = 'Email must be between 5 and 128 characters with an @ symbol.';
        $this->form->validate($this->params);
        $this->assertValidateFailure();
    }
    
    public function testValidateFailureBadPasswordChars()
    {   
        $this->expectUtilValidatorFailBadPasswordChars();
        $this->expectEmailDoesNotExist();
        $this->expectedFormValues['email'] = 'foo@bar.com';
        $this->expectedFormValues['password'] = '';
        $this->expectedErrors['password'] = 'Password must be between 5 and 128 characters.';
        $this->form->validate($this->params);
        $this->assertValidateFailure();
    }
    
    public function testValidateEmailExistsAndBadPasswordChars()
    {
        $this->expectUtilValidatorFailBadPasswordChars();
        $this->expectEmailExists();
        $this->expectedFormValues['email'] = '';
        $this->expectedFormValues['password'] = '';
        $this->expectedErrors['email'] = 'An account already exists with that email address.';
        $this->expectedErrors['password'] = 'Password must be between 5 and 128 characters.';
        $this->form->validate($this->params);
        $this->assertValidateFailure();
    }
    
    private function assertValidateSuccess()
    {
        $this->assertFalse($this->form->hasErrors());
        $this->assertEquals($this->expectedFormValues, $this->form->toArray());
        $this->assertEquals($this->expectedErrors, $this->form->getErrors());
    }
    
    private function assertValidateFailure()
    {
        $this->assertTrue($this->form->hasErrors());
        $this->assertEquals($this->expectedFormValues, $this->form->toArray());
        $this->assertEquals($this->expectedErrors, $this->form->getErrors());
    }
}
