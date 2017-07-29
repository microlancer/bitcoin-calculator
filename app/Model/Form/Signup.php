<?php

namespace App\Model\Form;

use App\Model\Users;
use App\Util\Session;
use App\Util\Validator;

/**
 * @property string $email
 * @property string $password
 */
class Signup extends AbstractForm
{
    private $validator;
    private $users;
    private $session;
    
    public function __construct(Validator $validator, Users $users, Session $session)
    {
        $this->defineFields(['email', 'password']);
        $this->validator = $validator;
        $this->users = $users;
        $this->session = $session;
    }
    
    public function validate(array $params)
    {
        parent::validate($params);
        
        $validEmail = $this->validator->isValidEmailString($params['email']);

        if (!$validEmail) {
            $this->setError('email', 'Email must be between ' . Validator::MIN_EMAIL_LENGTH .
                    ' and ' . Validator::MAX_EMAIL_LENGTH . ' characters' .
                    ' with an @ symbol.');
            $emailExists = false;
        } else {
            $emailExists = $this->users->emailExists($params['email']);
        }

        $validPassword = $this->validator->isValidPasswordString($params['password']);

        if (!$validPassword) {
            $this->setError('password', 'Password must be between ' . Validator::MIN_PASSWORD_LENGTH .
                    ' and ' . Validator::MAX_PASSWORD_LENGTH . ' characters.');
        }

        if ($emailExists) {
            $this->setError('email', 'An account already exists with that email address.');
        }

        if (!$emailExists && $validEmail) {
            // They may have to re-type their password, but we'll redisplay the email.
            $this->email = $params['email'];
        }

        if (!$this->hasErrors()) {
            $this->password = $params['password'];
        } else {
            $this->password = '';
        }
    }
}
