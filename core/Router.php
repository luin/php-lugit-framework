<?php
class Router extends Singleton
{
    private $full_url_path = null;
    private $url_path = null;

    protected function __construct()
    {
        if (isset($_SERVER['REQUEST_URI'])) {
            $this->full_url_path = $_SERVER['REQUEST_URI'];
        } elseif (isset($_SERVER['argv'])) {
            $this->full_url_path = $_SERVER['PHP_SELF'] .'?'. $_SERVER['argv'][0];
        } else {
            $this->full_url_path = $_SERVER['PHP_SELF'] .'?'. $_SERVER['QUERY_STRING'];
        }

        $pos =  strpos($this->full_url_path, '?');
        if ($pos === false) {
            $this->url_get = '';
            $this->url_path = $this->full_url_path;
        }else {
            $this->url_get = substr($this->full_url_path, $pos + 1);
            $this->url_path = substr($this->full_url_path, 0, $pos);
        }

        //把连续的“\"或“/”替换成一个“/”
        $this->url_path = preg_replace('/[\/\\\\]+/', '/', $this->url_path);
        //去掉末尾的斜杠
        $this->url_path = (substr($this->url_path, -1) == '/')?
            substr($this->url_path, 1, -1) : substr($this->url_path, 1);

        $array_urlPath = array();
        if ($this->url_path) {
            $pos =  strpos($this->url_path, '/');
            if ($pos === false) {
                $array_urlPath = array(0 => $this->url_path);
            }else {
                $array_urlPath = explode('/', $this->url_path);
            }
        }
        $this->controllerName = isset($array_urlPath[0]) ? strtolower($array_urlPath[0]) : 'index';
        $this->actionName = isset($array_urlPath[1]) ? strtolower($array_urlPath[1]) : 'index';

        //获得并设置参数
        $this->array_parameter = array();
        if (($arrayCount = count($array_urlPath)) > 2) {
            for ($i = 2; $i < $arrayCount; ++$i) {
                $this->array_parameter[] = Basic::safeUrl($array_urlPath[$i]);
            }
        }

        if (!Singleton::getInstance('RequestModule')->isPost())
            $this->checkUrl();
    }


    private function checkUrl()
    {
        if ($this->controllerName == 'index' && $this->actionName == 'index' && !$this->array_parameter) {
            $url = '';
        } elseif ($this->actionName == 'index' && !$this->array_parameter) {
            $url = '/' . $this->controllerName;
        } else {
            $url = '/' . $this->controllerName . '/' . $this->actionName;
        }
        if ($this->array_parameter) {
            foreach ($this->array_parameter as $v) {
                $url .= '/' . $v;
            }
            if (strpos($v, '.') === false) $url .= '/';
        } else {
            $url .= '/';
        }
        if ($this->url_get) {
            $url .= '?' . $this->url_get;
        }
        if ($url != $this->full_url_path) {
            Singleton::getInstance('ResponseModule')->redirect($url, true);
        }
    }


    public function dispatch()
    {
        $view_controllerName = $this->controllerName;
        $view_actionName = $this->actionName;

        $controllerClassName = ucfirst($this->controllerName) . 'Controller';
        if (file_exists('./controllers/' . $controllerClassName . '.php')) {
            require './controllers/' . $controllerClassName . '.php';
        } else {
            $view_controllerName = DEFAULT_CONTROLLER_NAME;
            $controllerClassName = DEFAULT_CONTROLLER_NAME . 'Controller';
            Basic::requireOr404('./controllers/' . $controllerClassName . '.php');
        }

        //把controller中输出的内容保留并在view后输出
        ob_start();
        Lugit::$controller = new $controllerClassName($this->array_parameter);

        if (method_exists(Lugit::$controller, $this->actionName . 'Action')) {
            //方法存在，调用方法
            Lugit::$controller->{$this->actionName . 'Action'}();
        } else if (method_exists(Lugit::$controller, DEFAULT_ACTION_NAME . 'Action')) {
                //方法不存在，调用默认方法
                $view_actionName = DEFAULT_ACTION_NAME;
                Lugit::$controller->{DEFAULT_ACTION_NAME . 'Action'}($this->actionName);
        } else {
            //方法不存在 且 默认方法不存在，返回404错误
            throw new NotFoundException();
        }
        $controller_output = ob_get_clean();

        if (empty(Lugit::$viewLoader)) {
            Lugit::$viewLoader = new viewLoader(Lugit::$controller->getVars(),
                './views/scripts/' . $view_controllerName . '/' . $view_actionName . '.phtml');
        }
        Lugit::$viewLoader->render();
        echo $controller_output;
    }


    /**
     * 获得当前页面完整的URL.
     * 
     * @access public
     * @return string 当前页面完整的URL
     */
    public function getUrl()
    {
        return $this->full_url_path;
    }


}