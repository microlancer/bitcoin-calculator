<?php

namespace App\Model\Form;

use App\Model\Auth;
use App\Util\Session;
use App\Util\Validator;

/**
 * @property string $email
 * @property string $password
 */
class Login extends AbstractForm
{
    private $validator;
    private $auth;
    private $session;
    
    public function __construct(Validator $validator, Session $session, Auth $auth)
    {
        $this->defineFields(['email', 'password']);
        $this->validator = $validator;
        $this->session = $session;
        $this->auth = $auth;
    }
    
    /**
     * Validates the parameters. If the validation passes, the form properties
     * will be updated to what was submitted.
     *
     * @param array $params
     * @throws \Exception
     */
    public function validate(array $params)
    {
        parent::validate($params);
        
        $validInput = $this->validator->isEmailChars($params['email'])
                && $this->validator->isPasswordChars($params['password'])
                && $this->auth->checkPassword($params['email'], $params['password']);

        if (!$validInput) {
            $this->setError('email', 'Incorrect email/password.');
        }

        $this->email = $params['email'];
    }
}
