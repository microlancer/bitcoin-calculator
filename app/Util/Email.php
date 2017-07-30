<?php

namespace App\Util;

use App\Util\Config;
use App\Util\Session;

class Email
{
    private $emailEnabled;
    private $session;
    
    public function __construct(Config $config, Session $session)
    {
        $this->emailEnabled = $config->get('emailEnabled');
        $this->session = $session;
    }
    
    public function send($template, array $vars)
    {
        if (!isset($vars['email']) || !isset($vars['subject'])) {
            throw new \Exception('Target email and subject required');
        }
        
        $template = file_get_contents(__DIR__ . '/../EmailTemplate/' . $template . '.tpl');
        
        foreach ($vars as $key => $value) {
            $template = str_replace("{{" . $key . "}}", $value, $template);
        }
        
        $templateLines = explode("\n", $template);
        $properTemplateLines = [];
        foreach ($templateLines as $line) {
            /*if (strlen($line) > 70) {
                $properTemplateLines[] = implode("\r\n", str_split($line, 70));
            } else*/ {
                $properTemplateLines[] = $line;
            }
        }
        $properTemplate = implode("\r\n", $properTemplateLines) . "\r\n";
        
        // @codeCoverageIgnoreStart
        if ($this->emailEnabled) {
            $result = mail($vars['email'], $vars['subject'], $properTemplate, "From: support@whebsite.com\r\nReply-to: support@whebsite.com");
        } else {
            $msg = '<pre>';
            $msg .= "Displaying this email in your browser because your configuration has emailEnabled=false.\n";
            $msg .= "To: {$vars['email']}\n";
            $msg .= "Subject: {$vars['subject']}\n";
            $msg .= "Body:\n";
            $msg .= $properTemplate;
            $msg .= '</pre>';
            $this->session->set('mailMessage', $msg);
            $result = true;
        }
        
        if (!$result) {
            throw new \Exception('Not accepted for delivery');
        }
        // @codeCoverageIgnoreEnd
    }
}
