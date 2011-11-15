<?php
class ApiModule extends Singleton
{
    private $authModule;
    private $callback;
    private $defaultContentType = 'array';

    protected function __construct()
    {
        $this->authModule = Singleton::getInstance('AuthModule');
    }


    public function setCallback($callback)
    {
        $this->callback = $callback;
        return $this;
    }

    public function setDefaultContentType($type)
    {
        $this->defaultContentType = $type;
        return $this;
    }


    public function get($uri, $parameters = array(), $basicAuth = true)
    {
        $uri_query = '';

        //将参数传成url
        if ($parameters) {
            foreach ($parameters as $key => $value) {
                if ($uri_query) $uri_query .= '&';
                $uri_query .= "$key=$value";
            }
        }
        $uri .= '?' . $uri_query;

        return $this->_callApi($uri, $basicAuth);
    }


    public function post($uri, $parameters = array(), $basicAuth = true, $contentType = '')
    {
        if(!$contentType) $contentType = $this->defaultContentType;

        return $this->_callApi($uri, $basicAuth, $parameters, 'post', $contentType);
    }


    private function _callApi($uri, $basicAuth = true, $parameters = array(), $method = 'get', $contentType = '')
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, API_URL . $uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //whether need HTTP BASIC AUTH
        if ($basicAuth && $this->authModule->username && $this->authModule->password) {
            curl_setopt($ch, CURLOPT_USERPWD,
                "{$this->authModule->username}:{$this->authModule->password}");
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        }

        if ($method == 'post') {
            curl_setopt($ch, CURLOPT_POST, 1 );

            if($contentType == 'json' && is_array($parameters)) {
                $parameters = json_encode($parameters);
            }

            if($contentType) {
                if($contentType == 'xml')
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
                elseif($contentType == 'json')
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
        }

        $output = curl_exec($ch);

        if ($output === false) {
            //如果curl执行失败
            throw new ApiException(curl_error($ch));
        }

        $info   = curl_getinfo($ch);
        curl_close($ch);

        if ($this->callback) {
            return call_user_func_array($this->callback,
                array($output, $info['http_code']));
        } else {
            return array('response_text'=>$output,
                'http_code'=>$info['http_code']);
        }
    }


}