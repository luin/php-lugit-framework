<?php
class AuthModule extends Singleton
{
    private $username = null;
    private $password = null;
    private $_cookieModule;
    private $_sessionModule;

    protected function __construct()
    {
        $this->_cookieModule = Singleton::getInstance('CookieModule');
        $this->_sessionModule = Singleton::getInstance('SessionModule');

        if (defined('SALT')) $this->salt = SALT;
        else throw new Exception('"SALT" is not defined.');
        if (!isset($this->_sessionModule->username) || !isset($this->_sessionModule->password)) {
            $username = $this->_cookieModule->get(md5('username' . $this->salt));
            $password = $this->_cookieModule->get(md5('password' . $this->salt));
            if (isset($username) && isset($password)) {
                $password = Basic::authcode($password, 'DECODE', $this->salt);
                $this->_sessionModule->username = $username;
                $this->_sessionModule->password = $password;
            }
        }
        $this->username = $this->_sessionModule->username;
        $this->password = $this->_sessionModule->password;
    }


    private function generatePassword($length = 8)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_ []{}<>~`+=,.;:/?|';
        $password ='';
        for ($i = 0; $i < $length; ++$i)
            $password .= $chars[mt_rand(0, strlen($chars) - 1)];
        return $password;
    }


    public function saveAuth($username, $password, $remember = false)
    {
        $this->clearAuth();
        $this->username = $this->_sessionModule->username = $username;
        $this->password = $this->_sessionModule->password = $password;
        if ($remember) {
            $this->_cookieModule->set(md5('username' . $this->salt), $username, time() + 2592000);
            $password = Basic::authcode($password, 'ENCODE', $this->salt);
            $this->_cookieModule->set(md5('password' . $this->salt), $password, time() + 2592000);
        }
    }


    public function issetAuth()
    {
        return isset($this->username) && isset($this->password);
    }


    public function clearAuth()
    {
        unset($this->_sessionModule->username);
        unset($this->_sessionModule->password);
        $this->_sessionModule->sessionDestroy();
        $this->_cookieModule->delete(md5('username' . $this->salt));
        $this->_cookieModule->delete(md5('password' . $this->salt));
    }


    public function __get($name)
    {
        return $this->$name;
    }


}