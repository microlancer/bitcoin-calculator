<?php

namespace App\Model;

use App\Util\Config;
use App\Util\Email;
use App\Util\Mysql;

class Users
{
    private $mysql;
    private $config;
    private $email;
    
    public function __construct(Mysql $mysql, Config $config, Email $email)
    {
        $this->mysql = $mysql;
        $this->config = $config;
        $this->email = $email;
    }
    
    public function emailExists($email)
    {
        sleep(1);
        $query = 'select count(*) as cnt from users where email = ?';
        $params = [$email];
        $results = $this->mysql->query($query, 's', $params);
        return $results[0]['cnt'] ? true : false;
    }
    
    public function add(User $user)
    {
        $verifyCode = bin2hex(openssl_random_pseudo_bytes(8));
        $query = 'insert into users '
                . '(email, password, verifyCode, createdTs, updatedTs) '
                . 'values (?, ?, ?, NOW(), NOW())';
        
        $hash = password_hash($user->password, PASSWORD_BCRYPT);
        
        $params = [$user->email, $hash, $verifyCode];
        
        $added = $this->mysql->query($query, 'sss', $params);
        
        if ($added != 1) {
            throw new \Exception('Failed to add user record');
        }
        
        $user->verifyCode = $verifyCode;
    }
    
    public function delete($email)
    {
        $query = 'delete from users where email = ? limit 1';
        $removed = $this->mysql->query($query, 's', [$email]);
        
        if ($removed != 1) {
            throw new \Exception('Failed to delete user record');
        }
    }
    
    public function loadUser(User $user)
    {
        if (!isset($user->email) && !isset($user->id)) {
            throw new \Exception('Can only load user by email or id, none specified.');
        }
        
        if (isset($user->email)) {
            $where = 'email = ?';
            $types = 's';
            $params = [$user->email];
        } else {
            $where = 'id = ?';
            $types = 'i';
            $params = [$user->id];
        }
        
        $query = "select id, email, verifyCode, passwordResetCode from users where $where limit 1";
        
        $rows = $this->mysql->query($query, $types, $params);
        
        if (empty($rows) || !isset($rows[0])) {
            throw new \Exception('Unable to find user.');
        }
        
        $user->init($rows[0]);
    }
    
    public function saveUser(User $user)
    {
        if (!isset($user->id)) {
            throw new \Exception('User ID required to save');
        }
        
        $set = [];
        $params = [];
        $typesMap = ['email' => 's', 'verifyCode' => 's', 'password' => 's', 'passwordResetCode' => 's'];
        $types = [];
        
        foreach ($user->getModifiedProperties() as $property) {
            $set[] = "$property = ?";
            $types[] = $typesMap[$property];
            
            if ($property == 'password') {
                $params[] = password_hash($user->password, PASSWORD_BCRYPT);
            } else {
                $params[] = $user->$property;
            }
        }
        
        if (empty($set)) {
            // Nothing to update
            return;
        }
        
        $params[] = $user->id;
        $types[] = 'i';
        
        $query = "update users set " . implode(", ", $set) . " where id = ?";
        
        $updated = $this->mysql->query($query, implode('', $types), $params);
        
        if (!$updated == 1) {
            throw new \Exception('Failed to update user record');
        }
    }
}
