<?php
class SessionModule extends Singleton
{
    private $enable_session_verify;

    protected function __construct($enable_session_verify = true)
    {
        session_start();
        $this->enable_session_verify = $enable_session_verify;
    }


    public function __get($key)
    {
        return $this->get($key);
    }


    public function get($key, $default = null)
    {
        $this->sessionVerify();
        return (isset($_SESSION[$key])) ? $_SESSION[$key] : $default;
    }


    public function __set($key, $value)
    {
        $this->set($key, $value);
    }


    public function set($key, $value)
    {
        $this->sessionVerify();
        $_SESSION[$key] = $value;
    }


    private function sessionVerify()
    {
        if (!$this->enable_session_verify) return;

        if (!isset($_SESSION['user_agent'])) {
            $_SESSION['user_agent'] = md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);
        } elseif ($_SESSION['user_agent'] !=
            md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'])) {
            session_regenerate_id();
        }
    }


    public function sessionDestroy()
    {
        @session_destroy();
        setcookie(session_name(), '', time()-3600);
        $_SESSION = array();
    }


}