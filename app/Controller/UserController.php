<?php

namespace App\Controller;

use App\Model\Auth;
use App\Model\Form\ForgotPassword;
use App\Model\Form\Login;
use App\Model\Form\ResetPassword;
use App\Model\Form\Signup;
use App\Model\Menu;
use App\Model\User;
use App\Model\Users;
use App\Util\Config;
use App\Util\Di;
use App\Util\HeaderParams;
use App\Util\Session;
use App\Util\Validator;
use App\Util\View;

class UserController extends ViewController
{
    private $session;
    private $headers;
    private $auth;
    private $loginForm;
    private $signupForm;
    private $forgotPasswordForm;
    private $validator;
    private $users;
    private $di;
    private $resetPasswordForm;
    
    public function __construct(Config $config,
            View $view, Menu $menu, Session $session,
            HeaderParams $headers, Auth $auth, Login $loginForm, Signup $signupForm,
            Users $users, Validator $validator, Di $di, ForgotPassword $forgotPasswordForm,
            ResetPassword $resetPasswordForm)
    {
        parent::__construct($config, $view, $menu);
        $this->session = $session;
        $this->headers = $headers;
        $this->auth = $auth;
        $this->loginForm = $loginForm;
        $this->signupForm = $signupForm;
        $this->users = $users;
        $this->validator = $validator;
        $this->di = $di;
        $this->forgotPasswordForm = $forgotPasswordForm;
        $this->resetPasswordForm = $resetPasswordForm;
    }

    public function loginAction(array $unfilteredRequestParams)
    {
        $this->view->addVars($unfilteredRequestParams);
        
        $this->view->addVars(['message' => $this->session->getOnce('message')]);
        
        $loginFormState = $this->session->getOnce('loginFormState');
        
        if (isset($loginFormState)) {
            $this->view->addVars($loginFormState);
        } else {
            $this->view->addVars($this->loginForm->getState());
        }
        
        $this->view->render('user/login');
    }
    
    public function loginSubmitAction(array $p)
    {
        $this->session->regenerate();
            
        $this->loginForm->validate($p);
        
        if ($this->loginForm->hasErrors()) {
            $this->session->set('loggedIn', false);
            $this->session->set('loginFormState', $this->loginForm->getState());
            $this->headers->redirect('user/login');
            return;
        }
       
        $this->successfulLogin($this->loginForm->email);
    }
    
    public function signupAction(array $unfilteredRequestParams)
    {
        $this->view->addVars($unfilteredRequestParams);
        
        $signupFormState = $this->session->getOnce('signupFormState');
        
        if (isset($signupFormState)) {
            $this->view->addVars($signupFormState);
        } else {
            $this->view->addVars($this->signupForm->getState());
        }
        
        $this->view->render('user/signup');
    }
    
    public function signupSubmitAction(array $unfilteredRequestParams)
    {
        $this->signupForm->validate($unfilteredRequestParams);
        
        if ($this->signupForm->hasErrors()) {
            $this->session->set('signupFormState', $this->signupForm->getState());
            $this->headers->redirect('user/signup');
            return;
        }
        
        $user = $this->createUserObject();
        $user->email = $this->signupForm->email;
        $user->password = $this->signupForm->password;
        
        // Add user to database
        $this->users->add($user);
        
        // Send confirmation email
        $user->sendConfirmationEmail();
        
        $this->session->set('message',
                'Confirmation email has been sent. Please check your email to login.');
        
        $this->headers->redirect('');
    }
    
    public function accountAction(array $params)
    {
        if (!$this->session->get('loggedIn')) {
            $this->session->set('message', 'You must be logged-in to do that. Please login first.');
            $this->session->set('returnUrl', 'user/account');
            $this->headers->redirect('user/login');
            return;
        }
        
        $vars = [
            'message' => $this->session->getOnce('message'),
        ];
        
        $vars += $params;
        
        $user = $this->createUserObject();
        $user->id = $this->session->get('userId');
        $this->users->loadUser($user);
        
        $this->view->addVars($vars);
        $this->view->render('user/account');
    }
    
    public function logoutAction()
    {
        $this->session->end();
        $this->session->set('message', 'Logout successful');
        $this->headers->redirect('');
    }
    
    public function verifyAction(array $p)
    {
        if (!$this->validator->isValidEmailString($p['email']) ||
            !$this->validator->isValidVerifyCodeString($p['verifyCode'])) {
            throw new \Exception('Invalid input');
        }
        
        if (!$this->users->emailExists($p['email'])) {
            throw new \Exception('Invalid email specified');
        }
        
        $user = $this->createUserObject();
        $user->email = $p['email'];
        $this->users->loadUser($user);
        
        if (!isset($user->verifyCode)) {
            sleep(1);
            $this->session->set('message', 'Email has already been verified. Please login.');
            $this->headers->redirect('user/login');
        }
        
        if ($user->verifyCode == $p['verifyCode']) {
            // Clear the verifyCode for the user and save it
            $user->verifyCode = null;
            $this->users->saveUser($user);
        } else {
            throw new \Exception('Invalid verifyCode specified');
        }
        
        $this->successfulLogin($p['email']);
    }
    
    public function needsVerificationAction(array $p)
    {
        $this->view->addVars($p);
        $this->view->render('user/needs-verification');
    }
    
    public function resendVerificationAction(array $p)
    {
        $user = $this->createUserObject();
        $user->id = $this->session->get('userId');
        
        if (is_null($user->id)) {
            $this->headers->redirect('user/login');
            return;
        }
        
        $this->users->loadUser($user);
        
        // Send confirmation email
        $user->sendConfirmationEmail();
        
        $this->session->set('message',
                'Confirmation email has been sent. Please check your email to login.');
        
        $this->headers->redirect('');
    }
    
    public function forgotPasswordAction(array $p)
    {
        $this->view->addVars($p);
        
        $forgotPasswordFormState = $this->session->getOnce('forgotPasswordFormState');
        
        if (isset($forgotPasswordFormState)) {
            $this->view->addVars($forgotPasswordFormState);
        } else {
            $this->view->addVars($this->forgotPasswordForm->getState());
        }
        
        $this->view->render('user/forgot-password');
    }
    
    public function forgotPasswordSubmitAction(array $p)
    {
        $this->forgotPasswordForm->validate($p);
        
        if ($this->forgotPasswordForm->hasErrors()) {
            $this->session->set('forgotPasswordFormState', $this->forgotPasswordForm->getState());
            $this->headers->redirect('user/forgot-password');
            return;
        }
        
        $user = $this->createUserObject();
        $user->email = $this->forgotPasswordForm->email;
        $this->users->loadUser($user);
        
        if (isset($user->verifyCode)) {
            $this->session->set('userId', $user->id);
            $this->headers->redirect('user/needs-verification');
            return;
        }
        
        // Generate a password reset link for the user and save it
        $user->generatePasswordResetCode();
        $this->users->saveUser($user);
        
        // Send a password reset link
        $user->sendPasswordResetEmail();
        
        $this->session->set('message',
                'Password reset link been sent. Please check your email.');
        
        $this->headers->redirect('');
    }
    
    public function passwordResetVerifyAction(array $p)
    {
        $resetPasswordFormState = $this->session->getOnce('resetPasswordFormState');
        
        if (isset($resetPasswordFormState)) {
            $p['email'] = $resetPasswordFormState['formValues']['email'];
            $p['passwordResetCode'] = $resetPasswordFormState['formValues']['passwordResetCode'];
        } else {
            // From an email link, get from query parameters
            if (!isset($p['email']) || !$this->validator->isValidEmailString($p['email']) ||
                !isset($p['passwordResetCode']) || !$this->validator->isValidVerifyCodeString($p['passwordResetCode'])) {
                throw new \Exception('Invalid input');
            }
        }
        
        if (!$this->users->emailExists($p['email'])) {
            throw new \Exception('Invalid email specified');
        }
        
        $user = $this->createUserObject();
        $user->email = $p['email'];
        $this->users->loadUser($user);
        
        if (!isset($user->passwordResetCode)) {
            $this->session->set('message', 'Password reset link has expired. Please try again.');
            $this->headers->redirect('user/login');
        }
        
        // Code is correct, so render a password reset form, with hidden code
        
        if (isset($resetPasswordFormState)) {
            $this->view->addVars($resetPasswordFormState);
        } else {
            $defaultState = $this->resetPasswordForm->getState();
            $defaultState['formValues']['email'] = $p['email'];
            $defaultState['formValues']['passwordResetCode'] = $p['passwordResetCode'];
            $this->view->addVars($defaultState);
        }
        
        $this->view->addVars($p);
        $this->view->render('user/reset-password-form');
    }
    
    
    public function passwordResetSubmitAction(array $p)
    {
        $this->resetPasswordForm->validate($p);
        
        if ($this->resetPasswordForm->hasErrors()) {
            if ($this->resetPasswordForm->getError('passwordResetCode') == 'Link expired') {
                $this->session->set('message', 'Password reset link has expired. Please try again.');
                $this->headers->redirect('user/login');
                return;
            }
            
            $this->session->set('resetPasswordFormState', $this->resetPasswordForm->getState());
            $this->headers->redirect('user/password-reset-verify');
            return;
        }
        
        $user = $this->createUserObject();
        $user->email = $this->resetPasswordForm->email;
        $this->users->loadUser($user);
        
        // Clear the passwordResetCode, assign the new password for the user and save it
        $user->passwordResetCode = null;
        $user->password = $this->resetPasswordForm->password;
        $this->users->saveUser($user);

        $this->successfulLogin($p['email']);
    }
    
    private function successfulLogin($email)
    {
        $user = $this->createUserObject();
        $user->email = $email;
        $this->users->loadUser($user);
        
        if (isset($user->verifyCode)) {
            $this->headers->redirect('user/needs-verification');
            $this->session->set('userId', $user->id);
            return;
        }
        
        $this->session->set('message', 'Successfully logged in as ' . $email);
        $this->session->set('loggedIn', true);
        $this->session->set('userId', $user->id);
        
        $returnUrl = $this->session->getOnce('returnUrl');
        
        if (isset($returnUrl)) {
            $this->headers->redirect($returnUrl);
            return;
        }
        
        $this->headers->redirect('user/account');
    }
    
    /**
     * Create a new User object.
     *
     * @return User
     */
    private function createUserObject()
    {
        return $this->di->create(User::class);
    }
}
