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
        $router = Singleton::getInstance('Router');
        $this->controllerName = $router->controllerName;
        $this->actionName = $router->actionName;
        $this->init();
    }


    public function __get($key)
    {
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
        $controller_output = ob_get_clean();
        Lugit::$viewLoader = new viewLoader(Lugit::$controller->getVars(),
            "./views/scripts/$viewPath.phtml");

        if ($exit) {
            Lugit::$viewLoader->render();
            echo $controller_output;
            exit;
        }
    }


}