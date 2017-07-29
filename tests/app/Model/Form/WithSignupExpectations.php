<?php

namespace Test\App\Model\Form;

use App\Model\Users;
use App\Util\Session;
use App\Util\Validator;

trait WithSignupExpectations
{   
    public function setUpMocksForExpectations()
    {
        $this->setupMocks([
            Validator::class,
            Session::class,
            Users::class,
        ]);
    }
    
    private function expectUtilValidatorPass()
    {
        $this->setMockExpectations(Validator::class, [
            'isValidEmailString' => [$this->once(), ['foo@bar.com'], true],
            'isValidPasswordString' => [$this->once(), ['pass123'], true],
        ]);
    }
    
    private function expectUtilValidatorFailBadEmailChars()
    {
        $this->setMockExpectations(Validator::class, [
            'isValidEmailString' => [$this->once(), ['foo@bar.com'], false],
            'isValidPasswordString' => [$this->once(), ['pass123'], true],
        ]);
    }
    
    private function expectUtilValidatorFailBadPasswordChars()
    {
        $this->setMockExpectations(Validator::class, [
            'isValidEmailString' => [$this->once(), ['foo@bar.com'], true],
            'isValidPasswordString' => [$this->once(), ['pass123'], false],
        ]);
    }
    
    private function expectEmailExists()
    {
        $this->setMockExpectations(Users::class, [
            'emailExists' => [$this->once(), ['foo@bar.com'], true],
        ]);
    }
    
    private function expectEmailDoesNotExist()
    {
        $this->setMockExpectations(Users::class, [
            'emailExists' => [$this->once(), ['foo@bar.com'], false],
        ]);
    }
}