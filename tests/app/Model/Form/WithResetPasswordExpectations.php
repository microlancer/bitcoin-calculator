<?php

namespace Test\App\Model\Form;

use App\Model\User;
use App\Model\Users;
use App\Util\Di;
use App\Util\Validator;

trait WithResetPasswordExpectations
{   
    public function setUpMocksForExpectations()
    {
        $this->setupMocks([
            Validator::class,
            Users::class,
            Di::class,
            User::class,
        ]);
    }
    
    private function expectUtilValidatorPass()
    {
        $this->setMockExpectations(Validator::class, [
            'isValidEmailString' => [$this->once(), ['foo@bar.com'], true],
            'isValidPasswordString' => [$this->once(), ['pass123'], true],
            'isValidVerifyCodeString' => [$this->any(), ['abcd1234'], true],
        ]);
    }
    
    private function expectUtilValidatorFailPasswordInvalid()
    {
        $this->setMockExpectations(Validator::class, [
            'isValidEmailString' => [$this->any(), null, true],
            'isValidPasswordString' => [$this->once(), ['pass123'], false],
            'isValidVerifyCodeString' => [$this->any(), null, true],
        ]);
    }
    
    private function expectUserExists()
    {
        $this->setMockExpectations(Users::class, [
            'loadUser' => [$this->once(), null, null],
            'emailExists' => [$this->once(), ['foo@bar.com'], true],
        ]);
        
        $this->setMockExpectations(User::class, [
            '__isset' => [$this->atLeastOnce(), ['passwordResetCode'], true],
            '__set' => [$this->once(), ['email'], null],
            '__get' => [$this->once(), ['passwordResetCode'], 'abcd1234'],
        ]);
        
        $this->setMockExpectations(Di::class, [
            'create' => [$this->once(), [User::class], $this->{User::class}],
        ]);
    }
    
    private function expectUserExistsButDifferentResetCode()
    {
        $this->setMockExpectations(Users::class, [
            'loadUser' => [$this->once(), null, null],
            'emailExists' => [$this->once(), ['foo@bar.com'], true],
        ]);
        
        $this->setMockExpectations(User::class, [
            '__isset' => [$this->atLeastOnce(), ['passwordResetCode'], true],
            '__set' => [$this->once(), ['email'], null],
            '__get' => [$this->once(), ['passwordResetCode'], '9999abcd'],
        ]);
        
        $this->setMockExpectations(Di::class, [
            'create' => [$this->once(), [User::class], $this->{User::class}],
        ]);
    }
    
    private function expectUserExistsButNoResetCode()
    {
        $this->setMockExpectations(Users::class, [
            'loadUser' => [$this->once(), null, null],
            'emailExists' => [$this->once(), ['foo@bar.com'], true],
        ]);
        
        $this->setMockExpectations(User::class, [
            '__isset' => [$this->atLeastOnce(), ['passwordResetCode'], true],
            '__set' => [$this->once(), ['email'], null],
            '__get' => [$this->once(), ['passwordResetCode'], null],
        ]);
        
        $this->setMockExpectations(Di::class, [
            'create' => [$this->once(), [User::class], $this->{User::class}],
        ]);
    }
    
    
    private function expectUserDoesNotExist()
    {
        $this->setMockExpectations(Users::class, [
            'emailExists' => [$this->once(), ['foo@bar.com'], false],
        ]);
    }
    
    private function expectInputPasswordsDontMatch()
    {
        $this->expectGoodInput();
        $this->params['confirmPassword'] = 'somethingElse123';
    }
}