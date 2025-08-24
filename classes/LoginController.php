<?php

require_once 'DbController.php';

class LoginController {
    public function login($error='') {
        require_once 'views/auth/login.php';
    }

    public function loginSubmit($email, $password) {
        $emailErr = $passwordErr = $loginErr = '';
        if (empty(trim($email))) {
            $error['email'] = 'email不可為空';
            $this->login($error);
            exit;
        } else {
            if (!preg_match('/([\w\-]+\@[\w\-]+\.[\w\-]+)/', $email)) {
                $error['email'] = 'Email格式錯誤';
                $this->login($error);
                exit;
            }
        }

        if (empty(trim($password))) {
            $error['password'] = '密碼不可為空';
            $this->login($error);
            exit;
        } else {
            if (strlen($password) < 6) {
                $error['password'] = '密碼至少需6位數';
                $this->login($error);
                exit;
            }
        }

        $link = new DbController();
        $user = mysqli_fetch_assoc($link->connect()->query("
            SELECT * 
            FROM `users` 
            WHERE `email`='$email'
        "));
        if (isset($user)){
            if ($email===$user['email'] && password_verify($password, $user['password']) && $user['role']==='admin') {
                session_start();
                session_destroy();
                session_start();
                $_SESSION['id'] = $user['id'];
                $_SESSION['email'] = $email;
                $_SESSION['role'] = $user['role'];
                
                header('Location: /admin');
                exit;
            }
        }
        $error['login'] = '電子郵件或密碼無效，請重試！';
        $this->login($error);
        exit;
    }
}