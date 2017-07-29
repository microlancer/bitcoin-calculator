<?php
namespace App\Model;

use App\Util\Config;
use App\Util\Email;

/**
 * Class User
 * @property int $id
 * @property string $email
 * @property string $password
 * @property string $verifyCode
 * @property string $passwordResetCode
 */
class User
{
    use WithProperties;
    
    private $config;
    private $mailer;
   
    public function __construct(Config $config, Email $mailer)
    {
        $this->defineProperties([
           'id',
           'email',
           'password',
           'verifyCode',
           'passwordResetCode',
        ]);
        
        $this->config = $config;
        $this->mailer = $mailer;
    }
    
    public function sendConfirmationEmail()
    {
        if (!isset($this->verifyCode)) {
            throw new \Exception('Cannot send confirmation without code');
        }
        
        $verifyLink = $this->config->get('baseUrl')
                . '/user/verify?verifyCode=' . $this->verifyCode
                . '&email=' . urlencode($this->email);
        
        $emailParams = [
            'email' => $this->email,
            'subject' => 'Bitvest - Please verify your email address',
            'verifyLink' => $verifyLink,
        ];
        
        $this->mailer->send('verify-code', $emailParams);
    }
    
    public function generatePasswordResetCode()
    {
        $this->passwordResetCode = bin2hex(openssl_random_pseudo_bytes(8));
    }
    
    public function sendPasswordResetEmail()
    {
        if (!isset($this->passwordResetCode)) {
            throw new \Exception('Cannot send confirmation without code');
        }
        
        $passwordResetLink = $this->config->get('baseUrl')
                . '/user/password-reset-verify?passwordResetCode=' . $this->passwordResetCode
                . '&email=' . urlencode($this->email);
        
        $emailParams = [
            'email' => $this->email,
            'subject' => 'Bitvest - Password reset has been requested',
            'passwordResetLink' => $passwordResetLink,
        ];
        
        $this->mailer->send('password-reset', $emailParams);
    }
}
