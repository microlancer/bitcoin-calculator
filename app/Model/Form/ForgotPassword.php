<?php

namespace App\Model\Form;

use App\Model\Users;
use App\Util\Validator;

/**
 * @property $email
 */
class ForgotPassword extends AbstractForm
{
    private $validator;
    private $users;
    
    public function __construct(Validator $validator, Users $users)
    {
        $this->defineFields(['email']);
        $this->validator = $validator;
        $this->users = $users;
    }
    
    /**
     * Validates the parameters. If the validation passes, the class values
     * will be updated to what was submitted.
     *
     * @param array $params
     * @throws \Exception
     */
    public function validate(array $params)
    {
        parent::validate($params);

        $validEmailChars = $this->validator->isEmailChars($params['email']);

        if ($validEmailChars) {
            $emailExists = $this->users->emailExists($params['email']);
        } else {
            $emailExists = false;
        }

        if (!$validEmailChars || !$emailExists) {
            $this->setError('email', 'Invalid email address.');
        } else {
            $this->email = $params['email'];
        }
    }
}
