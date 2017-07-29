<?php

namespace App\Model\Form;

use App\Model\User;
use App\Model\Users;
use App\Util\Di;
use App\Util\Validator;

/**
 * @property string $email
 * @property string $password
 * @property string $confirmPassword
 * @property string $passwordResetCode
 */
class ResetPassword extends AbstractForm
{
    private $validator;
    private $di;
    private $users;
    
    /**
     * 
     * @param Validator $validator
     * @param Users $users
     * @param Di $di
     */
    public function __construct(Validator $validator, Users $users, Di $di)
    {
        $this->defineFields(['email', 'password', 'confirmPassword', 'passwordResetCode']);
        
        $this->validator = $validator;
        $this->di = $di;
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
        
        $validPasswordString = $this->validator->isValidPasswordString($params['password']);
        
        if (!$validPasswordString) {
            $this->setError('password', 'Password must be between ' . Validator::MIN_PASSWORD_LENGTH .
                        ' and ' . Validator::MAX_PASSWORD_LENGTH . ' characters.');
        }
        
        $matchesConfirm = $params['password'] == $params['confirmPassword'];
        
        if (!$matchesConfirm) {
            $this->setError('confirmPassword', 'Passwords do not match.');
            
            // Clear these form values for user to try again
            $this->password = '';
            $this->confirmPassword = '';
        }
        
        $validEmailString = $this->validator->isValidEmailString($params['email']);
        $emailExists = $validEmailString && $this->users->emailExists($params['email']);
        
        if ($emailExists) {
            $user = $this->di->create(User::class);
            $user->email = $params['email'];
            $this->users->loadUser($user);
            $validCode = isset($user->passwordResetCode) && $this->validator->isValidVerifyCodeString($params['passwordResetCode']);
            $samePasswordResetCode = $validCode && ($user->passwordResetCode == $params['passwordResetCode']);
        } else {
            $samePasswordResetCode = false;
        }
        
        if (!$samePasswordResetCode) {
            // This is a rare case, e.g. user clicks the same reset link again
            // Should redirect them to the login page with an error message
            $this->setError('passwordResetCode', 'Link expired');
            return;
        } elseif ($matchesConfirm && $validPasswordString) {
            // Everything looks good, form values validated successfully.
            $this->password = $params['password'];
            $this->confirmPassword = $params['confirmPassword'];
        }
        
        // Preserve these form values for user to try again
        $this->email = $params['email'];
        $this->passwordResetCode = $params['passwordResetCode'];
    }
}
