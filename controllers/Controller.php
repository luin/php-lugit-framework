<?php
abstract class Controller
{
    protected $parameters;
    protected $vars;
    protected $controllerName;
    protected $actionName;

    public function __construct($parameters = array())
    {
        $this->parameters = $parameters;
        $this->router = Singleton::getInstance('Router');
        $this->controllerName = $this->router->controllerName;
        $this->actionName = $this->router->actionName;
        $this->init();
    }


    public function __get($key)
    {
        $key = ucfirst(strtolower($key));
        if (class_exists($key . 'Module')) {
            return Singleton::getInstance($key . 'Module');
        } else {
            return null;
        }
    }


    protected function init()
        { }


    protected function setVar($key, $value)
    {
        $this->vars->$key = $value;
    }


    protected function getVar($key)
    {
        return isset($this->vars->$key) ? $this->vars->$key : null;
    }


    public function getVars()
    {
        return $this->vars;
    }


    public function render($viewPath, $exit = true)
    {
        $router->template = "./views/scripts/$viewPath.phtml";
    }


}