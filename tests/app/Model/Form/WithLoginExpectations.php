<?php

namespace Test\App\Model\Form;

use App\Model\Auth;
use App\Util\Session;
use App\Util\Validator;

trait WithLoginExpectations
{   
    public function setUpMocksForExpectations()
    {
        $this->setupMocks([
            Validator::class,
            Session::class,
            Auth::class,
        ]);
    }
    
    private function expectUtilValidatorPass()
    {
        $this->setMockExpectations(Validator::class, [
            'isEmailChars' => [$this->once(), ['foo@bar.com'], true],
            'isPasswordChars' => [$this->once(), ['somepass123'], true],
        ]);
    }
    
    private function expectUtilValidatorFailInvalidEmailChars()
    {
        $this->setMockExpectations(Validator::class, [
            'isEmailChars' => [$this->once(), ['foo@bar.com'], false],
            'isPasswordChars' => [$this->never(), null, null],
        ]);
    }
    
    private function expectAuthCheckPasswordPass()
    {
        $this->setMockExpectations(Auth::class, [
            'checkPassword' => [$this->once(), ['foo@bar.com', 'somepass123'], true],
        ]);
    }
    
    private function expectAuthCheckPasswordFail()
    {
        $this->setMockExpectations(Auth::class, [
            'checkPassword' => [$this->once(), ['foo@bar.com', 'somepass123'], false],
        ]);
    }
    
    private function expectUtilValidatorFailInvalidPasswordChars()
    {
        $this->setMockExpectations(Validator::class, [
            'isEmailChars' => [$this->once(), ['foo@bar.com'], true],
            'isPasswordChars' => [$this->once(), ['somepass123'], false],
        ]);
    }
    
}