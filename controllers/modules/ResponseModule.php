<?php
class ResponseModule extends Singleton
{
    /**
     * http code
     *
     * @access private
     * @var array
     */
    private static $_httpCode = array(
        100 => 'Continue',
        101	=> 'Switching Protocols',
        200	=> 'OK',
        201	=> 'Created',
        202	=> 'Accepted',
        203	=> 'Non-Authoritative Information',
        204	=> 'No Content',
        205	=> 'Reset Content',
        206	=> 'Partial Content',
        300	=> 'Multiple Choices',
        301	=> 'Moved Permanently',
        302	=> 'Found',
        303	=> 'See Other',
        304	=> 'Not Modified',
        305	=> 'Use Proxy',
        307	=> 'Temporary Redirect',
        400	=> 'Bad Request',
        401	=> 'Unauthorized',
        402	=> 'Payment Required',
        403	=> 'Forbidden',
        404	=> 'Not Found',
        405	=> 'Method Not Allowed',
        406	=> 'Not Acceptable',
        407	=> 'Proxy Authentication Required',
        408	=> 'Request Timeout',
        409	=> 'Conflict',
        410	=> 'Gone',
        411	=> 'Length Required',
        412	=> 'Precondition Failed',
        413	=> 'Request Entity Too Large',
        414	=> 'Request-URI Too Long',
        415	=> 'Unsupported Media Type',
        416	=> 'Requested Range Not Satisfiable',
        417	=> 'Expectation Failed',
        500	=> 'Internal Server Error',
        501	=> 'Not Implemented',
        502	=> 'Bad Gateway',
        503	=> 'Service Unavailable',
        504	=> 'Gateway Timeout',
        505	=> 'HTTP Version Not Supported'
    );

    /**
     * 字符编码
     *
     * @var mixed
     * @access private
     */
    private $_charset;

    //默认的字符编码
    const CHARSET = 'UTF-8';

    /**
     * 解析ajax回执的内部函数
     *
     * @access private
     * @param mixed $message 格式化数据
     * @return string
     */
    private function _parseXml($message)
    {
        /** 对于数组型则继续递归 */
        if (is_array($message)) {
            $result = '';

            foreach ($message as $key => $val) {
                $tagName = is_int($key) ? 'item' : $key;
                $result .= '<' . $tagName . '>' . $this->_parseXml($val) . '</' . $tagName . '>';
            }

            return $result;
        } else {
            return preg_match("/^[^<>]+$/is", $message) ? $message : '<![CDATA[' . $message . ']]>';
        }
    }

    /**
     * 设置默认回执编码
     *
     * @access public
     * @param string $charset 字符集
     * @return void
     */
    public function setCharset($charset = null)
    {
        $this->_charset = empty($charset) ? self::CHARSET : $charset;
    }

    /**
     * 获取字符集
     *
     * @access public
     * @return void
     */
    public function getCharset()
    {
        if (empty($this->_charset)) {
            $this->setCharset();
        }

        return $this->_charset;
    }

    /**
     * 在http头部请求中声明类型和字符集
     *
     * @access public
     * @param string $contentType 文档类型
     * @return void
     */
    public function setContentType($contentType = 'text/html')
    {
        header('Content-Type: ' . $contentType . '; charset=' . $this->getCharset(), true);
    }

    /**
     * 设置http头
     *
     * @access public
     * @param string $name 名称
     * @param string $value 对应值
     * @return void
     */
    public function setHeader($name, $value)
    {
        header($name . ': ' . $value, true);
    }

    /**
     * 设置HTTP状态
     *
     * @access public
     * @param integer $code http代码
     * @return void
     */
    public static function setStatus($code)
    {
        if (isset(self::$_httpCode[$code])) {
            header('HTTP/1.1 ' . $code . ' ' . self::$_httpCode[$code], true, $code);
        }
    }

    /**
     * 抛出ajax的回执信息
     *
     * @access public
     * @param string $message 消息体
     * @return void
     */
    public function throwXml($message)
    {
        /** 设置http头信息 */
        $this->setContentType('text/xml');

        /** 构建消息体 */
        echo '<?xml version="1.0" encoding="' . $this->getCharset() . '"?>',
        '<response>',
        $this->_parseXml($message),
        '</response>';

        /** 终止后续输出 */
        exit;
    }

    /**
     * 抛出json回执信息
     *
     * @access public
     * @param string $message 消息体
     * @return void
     */
    public function throwJson($message)
    {
        /** 设置http头信息 */
        $this->setContentType('application/json');
        echo json_encode($message);

        /** 终止后续输出 */
        exit;
    }

    /**
     * 重定向函数
     *
     * @access public
     * @param string $location 重定向路径
     * @param boolean $isPermanently 是否为永久重定向
     * @return void
     */
    public function redirect($location, $isPermanently = false)
    {
        if ($isPermanently) {
            header('Location: ' . $location, false, 301);
            echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
    <html><head>
    <title>301 Moved Permanently</title>
    </head><body>
    <h1>Moved Permanently</h1>
    <p>The document has moved <a href="' . $location . '">here</a>.</p>
    </body></html>';
            exit;
        } else {
            header('Location: ' . $location, false, 302);
            echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
    <html><head>
    <title>302 Moved Temporarily</title>
    </head><body>
    <h1>Moved Temporarily</h1>
    <p>The document has moved <a href="' . $location . '">here</a>.</p>
    </body></html>';
            exit;
        }
    }
    
    public function refresh($additon)
    {
    	$instance = Singleton::getInstance('Router');
    	$this->redirect($instance->getUrl() . $additon);
    }

}
