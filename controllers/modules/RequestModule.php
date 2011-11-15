<?php
class RequestModule extends Singleton
{
    /**
     * 内部参数
     *
     * @access private
     * @var array
     */
    private $_params = array();

    /**
     * 服务端参数
     *
     * @access private
     * @var array
     */
    private $_server = array();

    /**
     * 客户端ip地址
     *
     * @access private
     * @var string
     */
    private $_ip = NULL;

    /**
     * 客户端字符串
     *
     * @access private
     * @var string
     */
    private $_agent = NULL;

    /**
     * 来源页
     *
     * @access private
     * @var string
     */
    private $_referer = NULL;

    /**
     * 当前过滤器
     *
     * @access private
     * @var array
     */
    private $_filter = array();

    private $_magic_quotes;
    protected function __construct()
    {
        $this->_magic_quotes = (function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc()) ||
            (ini_get('magic_quotes_sybase') && (strtolower(ini_get('magic_quotes_sybase'))!="off"));
    }


    /**
     * 应用过滤器
     *
     * @access private
     * @param mixed $value
     * @return void
     */
    private function _applyFilter($value)
    {
        $value = Singleton::getInstance('FilterModule')->applyFilters($value, $this->_filter);
        $this->_filter = array();
        return $value;
    }


    /**
     * 设置过滤器
     *
     * @access public
     * @param mixed $filter 过滤器名称
     * @return Widget_Request
     */
    public function filter()
    {
        $filters = func_get_args();

        foreach ($filters as $filter) {
            $this->_filter[] = $filter;
        }

        return $this;
    }


    /**
     * 获取实际传递参数(magic)
     *
     * @access public
     * @param string $key 指定参数
     * @return void
     */
    public function __get($key)
    {
        return $this->get($key);
    }


    /**
     * 判断参数是否存在
     *
     * @access public
     * @param string $key 指定参数
     * @return void
     */
    public function __isset($key)
    {
        return isset($_GET[$key])
            || isset($_POST[$key])
            || isset($_COOKIE[$key])
            || $this->isSetParam($key);
    }


    /**
     * 获取实际传递参数
     *
     * @access public
     * @param string $key 指定参数
     * @param mixed $default 默认参数 (default: null)
     * @return void
     */
    public function get($key, $default = null)
    {
        $value = $default;

        switch (true) {
        case isset($this->_params[$key]):
            $value = $this->_params[$key];
            break;
        case isset($_GET[$key]):
            $value = $_GET[$key];
            break;
        case isset($_POST[$key]):
            $value = $_POST[$key];
            break;
        case isset($_COOKIE[$key]):
            $value = $_COOKIE[$key];
            break;
        default:
            $value = $default;
            break;
        }

        $value = is_array($value) || strlen($value) > 0 ? $value : $default;

        if (AUTO_ESCAPE && !$this->_magic_quotes) {
            $value = is_array($value) ? array_map('addslashes', $value) : 
                addslashes($value);
        } elseif (!AUTO_ESCAPE && $this->_magic_quotes) {
            $value = is_array($value) ? array_map('stripslashes', $value) : 
                stripslashes($value);
        }


        return $this->_filter ? $this->_applyFilter($value) : $value;
    }


    /**
     * 从参数列表指定的值中获取http传递参数
     *
     * @access public
     * @param mixed $parameter 指定的参数
     * @return array
     */
    public function from($params)
    {
        $result = array();
        $args = is_array($params) ? $params : func_get_args();

        foreach ($args as $arg) {
            $result[$arg] = $this->get($arg);
        }

        return $result;
    }


    /**
     * 获取指定的http传递参数
     *
     * @access public
     * @param string $key 指定的参数
     * @param mixed $default 默认的参数
     * @return mixed
     */
    public function getParam($key, $default = NULL)
    {
        $value = isset($this->_params[$key]) ? $this->_params[$key] : $default;
        $value = is_array($value) || strlen($value) > 0 ? $value : $default;
        return $this->_filter ? $this->_applyFilter($value) : $value;
    }


    /**
     * 设置http传递参数
     *
     * @access public
     * @param string $name 指定的参数
     * @param mixed $value 参数值
     * @return void
     */
    public function setParam($name, $value)
    {
        $this->_params[$name] = $value;
    }


    /**
     * 删除参数
     *
     * @access public
     * @param string $name 指定的参数
     * @return void
     */
    public function unSetParam($name)
    {
        unset($this->_params[$name]);
    }


    /**
     * 参数是否存在
     *
     * @access public
     * @param string $key 指定的参数
     * @return boolean
     */
    public function isSetParam($key)
    {
        return isset($this->_params[$key]);
    }


    /**
     * 设置多个参数
     *
     * @access public
     * @param mixed $params 参数列表
     * @return void
     */
    public function setParams($params)
    {
        //处理字符串
        if (!is_array($params)) {
            parse_str($params, $out);
            $params = $out;
        }

        $this->_params = array_merge($this->_params, $params);
    }


    /**
     * 设置服务端参数
     *
     * @access public
     * @param string $name 参数名称
     * @param mixed $value 参数值
     * @return void
     */
    public function setServer($name, $value = NULL)
    {
        if (NULL == $value) {
            if (isset($_SERVER[$name])) {
                $value = $_SERVER[$name];
            } else if (isset($_ENV[$name])) {
                    $value = $_ENV[$name];
                }
        }

        $this->_server[$name] = $value;
    }


    /**
     * 获取环境变量
     *
     * @access public
     * @param string $name 获取环境变量名
     * @return string
     */
    public function getServer($name)
    {
        if (!isset($this->_server[$name])) {
            $this->setServer($name);
        }

        return $this->_server[$name];
    }


    /**
     * 设置ip地址
     *
     * @access public
     * @param unknown $ip
     * @return unknown
     */
    public function setIp($ip = NULL)
    {
        switch (true) {
        case NULL !== $this->getServer('HTTP_X_FORWARDED_FOR'):
            $this->_ip = $this->getServer('HTTP_X_FORWARDED_FOR');
            return;
        case NULL !== $this->getServer('HTTP_CLIENT_IP'):
            $this->_ip = $this->getServer('HTTP_CLIENT_IP');
            return;
        case NULL !== $this->getServer('REMOTE_ADDR'):
            $this->_ip = $this->getServer('REMOTE_ADDR');
            return;
        default:
            break;
        }

        $this->_ip = 'unknown';
    }


    /**
     * 获取ip地址
     *
     * @access public
     * @return string
     */
    public function getIp()
    {
        if (NULL === $this->_ip) {
            $this->setIp();
        }

        return $this->_ip;
    }


    /**
     * 设置客户端
     *
     * @access public
     * @param string $agent 客户端字符串
     * @return void
     */
    public function setAgent($agent = NULL)
    {
        $this->_agent = (NULL === $agent) ? $this->getServer('HTTP_USER_AGENT') : $agent;
    }


    /**
     * 获取客户端
     *
     * @access public
     * @return void
     */
    public function getAgent()
    {
        if (NULL === $this->_agent) {
            $this->setAgent();
        }

        return $this->_agent;
    }


    /**
     * 设置来源页
     *
     * @access public
     * @param string $referer 客户端字符串
     * @return void
     */
    public function setReferer($referer = NULL)
    {
        $this->_referer = (NULL === $referer) ? $this->getServer('HTTP_REFERER') : $referer;
    }


    /**
     * 获取客户端
     *
     * @access public
     * @return void
     */
    public function getReferer()
    {
        if (NULL === $this->_referer) {
            $this->setReferer();
        }

        return $this->_referer;
    }


    /**
     * 判断是否为get方法
     *
     * @access public
     * @return boolean
     */
    public function isGet()
    {
        return 'GET' == $this->getServer('REQUEST_METHOD');
    }


    /**
     * 判断是否为post方法
     *
     * @access public
     * @return boolean
     */
    public function isPost()
    {
        return 'POST' == $this->getServer('REQUEST_METHOD');
    }


    /**
     * 判断是否为put方法
     *
     * @access public
     * @return boolean
     */
    public function isPut()
    {
        return 'PUT' == $this->getServer('REQUEST_METHOD');
    }


    /**
     * 判断是否为https
     *
     * @access public
     * @return boolean
     */
    public function isSecure()
    {
        return 'on' == $this->getServer('HTTPS');
    }


    /**
     * 判断是否为ajax
     *
     * @access public
     * @return boolean
     */
    public function isAjax()
    {
        return 'XMLHttpRequest' == $this->getServer('HTTP_X_REQUESTED_WITH');
    }


    /**
     * 判断是否为flash
     *
     * @access public
     * @return boolean
     */
    public function isFlash()
    {
        return 'Shockwave Flash' == $this->getServer('USER_AGENT');
    }


    /**
     * 判断输入是否满足要求
     *
     * @access public
     * @param mixed $query 条件
     * @return boolean
     */
    public function is($query)
    {
        $validated = false;

        /** 解析串 */
        if (is_string($query)) {
            parse_str($query, $params);
        } else if (is_array($query)) {
                $params = $query;
            }

        /** 验证串 */
        if ($params) {
            $validated = true;
            foreach ($params as $key => $val) {
                if (empty($val)) {
                    $validated = $this->__isSet($key);
                } else {
                    $validated = ($this->get($key) == $val);
                }

                if (!$validated) {
                    break;
                }
            }
        }

        return $validated;
    }


}
