<?php
class ViewLoader
{
    private $vars;
    private $viewFilePath;
    public function __construct($parameters, $viewFilePath)
    {
        $this->vars = $parameters;
        $this->viewFilePath = $viewFilePath;
    }

    public function render()
    {
        if(!file_exists($this->viewFilePath)) {
            throw new exception('Cannot find the view file: ' . $this->viewFilePath);
        }
        include $this->viewFilePath;
    }

    public function load($name)
    {
        if(file_exists("./views/layouts/$name.phtml"))
            include "./views/layouts/$name.phtml";
        else
            throw new NotFoundException();
    }

    public function __get($key)
    {
        $key = ucfirst(strtolower($key));
        if(class_exists($key . 'Helper')) {
            return Singleton::getInstance($key . 'Helper');
        } else {
            return null;
        }
    }

}