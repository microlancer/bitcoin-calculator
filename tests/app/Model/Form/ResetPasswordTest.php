<?php
namespace Test\App\Model\Form;

use App\Model\Form\ResetPassword;
use App\Model\Users;
use App\Util\Di;
use App\Util\Validator;
use Test\WithMockHelper;

class ResetPasswordTest extends \PHPUnit_Framework_TestCase
{
    use WithMockHelper;
    use WithResetPasswordExpectations;
    
    /** @var ResetPassword $form */
    private $form;
    
    /** @var array $expectedErrors */
    private $expectedErrors;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->setUpMocksForExpectations();
        
        $this->params = [
            'email' => 'foo@bar.com',
            'password' => 'pass123',
            'confirmPassword' => 'pass123',
            'passwordResetCode' => 'abcd1234',
        ];
        
        $this->expectedErrors = [
            'email' => '', 
            'password' => '', 
            'confirmPassword' => '',
            'passwordResetCode' => '',
        ];
        
        $this->form = new ResetPassword($this->{Validator::class}, $this->{Users::class}, $this->{Di::class});
    }
    
    public function testValidateSuccess()
    {
        $this->expectUtilValidatorPass();
        $this->expectUserExists();
        $this->form->validate($this->params);
        $this->assertValidationSuccess();
    }
    
    public function testValidatePasswordInvalid()
    {
        $this->expectUtilValidatorFailPasswordInvalid();
        $this->expectUserExists();
        $this->form->validate($this->params);
        $this->expectedErrors['password'] = 'Password must be between 5 and 128 characters.';
        $this->assertValidationFailure();
    }
    
    public function testValidatePasswordsDontMatch()
    {
        $this->expectUtilValidatorPass();
        $this->expectUserExists();
        $this->params['confirmPassword'] = 'somethingElse123';
        $this->form->validate($this->params);
        $this->expectedErrors['confirmPassword'] = 'Passwords do not match.';
        $this->assertValidationFailure();
    }
    
    public function testValidatePasswordInvalidAndPasswordsDontMatch()
    {
        $this->expectUtilValidatorFailPasswordInvalid();
        $this->expectUserExists();
        $this->params['confirmPassword'] = 'somethingElse123';
        $this->form->validate($this->params);
        $this->expectedErrors['password'] = 'Password must be between 5 and 128 characters.';
        $this->expectedErrors['confirmPassword'] = 'Passwords do not match.';
        $this->assertValidationFailure();
    }
    
    public function testValidateEmailDoesNotExist()
    {
        $this->expectUtilValidatorPass();
        $this->expectUserDoesNotExist();
        $this->form->validate($this->params);
        $this->expectedErrors['passwordResetCode'] = 'Link expired';
        $this->assertValidationLinkFailure();
    }
    
    public function testValidateCodeDoesNotMatch()
    {
        $this->expectUtilValidatorPass();
        $this->expectUserExistsButDifferentResetCode();
        $this->form->validate($this->params);
        $this->expectedErrors['passwordResetCode'] = 'Link expired';
        $this->assertValidationLinkFailure();
    }
    
    public function testValidateUserAleadyReset()
    {
        $this->expectUtilValidatorPass();
        $this->expectUserExistsButNoResetCode();
        $this->form->validate($this->params);
        $this->expectedErrors['passwordResetCode'] = 'Link expired';
        $this->assertValidationLinkFailure();
    }
    
    public function testNoParams()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Missing form parameters');
        $this->form->validate([]);
    }
    
    private function assertValidationSuccess()
    {
        $this->assertFalse($this->form->hasErrors(), json_encode($this->form->getErrors()));
        $this->assertEquals('foo@bar.com', $this->form->email);
        $this->assertEquals('pass123', $this->form->password);
        $this->assertEquals('pass123', $this->form->confirmPassword);
        $this->assertEquals('abcd1234', $this->form->passwordResetCode);
        
        $errors = [
            'email' => '', 
            'password' => '', 
            'confirmPassword' => '',
            'passwordResetCode' => '',
        ];
        
        $this->assertEquals($errors, $this->form->getErrors());
    }
    
    private function assertValidationFailure()
    {
        $this->assertTrue($this->form->hasErrors(), json_encode($this->form->getErrors()));
        $this->assertEquals('foo@bar.com', $this->form->email);
        $this->assertEquals('', $this->form->password);
        $this->assertEquals('', $this->form->confirmPassword);
        $this->assertEquals('abcd1234', $this->form->passwordResetCode);
        $this->assertTrue($this->form->hasErrors());
        $this->assertEquals($this->expectedErrors, $this->form->getErrors());
    }
    
    private function assertValidationLinkFailure()
    {
        $this->assertTrue($this->form->hasErrors(), json_encode($this->form->getErrors()));
        $this->assertEquals('', $this->form->email);
        $this->assertEquals('', $this->form->password);
        $this->assertEquals('', $this->form->confirmPassword);
        $this->assertEquals('', $this->form->passwordResetCode);
        $this->assertTrue($this->form->hasErrors());
        $this->assertEquals($this->expectedErrors, $this->form->getErrors());
    }
}
