<?php

namespace App\Model;

use App\Util\Mysql;

class Auth
{
    private $mysql;
    
    public function __construct(Mysql $mysql)
    {
        $this->mysql = $mysql;
    }
    
    public function checkPassword($email, $password)
    {
        sleep(1);
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $query = 'select password from users where email = ?';
        $params = [
            'email' => $email,
        ];
        $result = $this->mysql->query($query, 's', $params);
        
        if (empty($result)) {
            return false;
        }
        
        return password_verify($password, $result[0]['password']);
    }
}
