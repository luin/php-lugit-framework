<?php
class ApiModule extends Singleton
{
    private $authModule;
    private $callback;

    protected function __construct()
    {
        $this->authModule = Singleton::getInstance('AuthModule');
    }


    public function setCallback($callback)
    {
        $this->$callback = $callback;
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


    public function post($uri, $parameters = array(), $basicAuth = true)
    {
        return $this->_callApi($uri, $basicAuth, $parameters, 'post');
    }


    private function _callApi($uri, $basicAuth = true, $parameters = array(), $method = 'get')
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, API_URL . $uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //whether need HTTP BASIC AUTH
        if ($basicAuth &&
            isset($this->authModule->username) && isset($this->authModule->password)) {
            curl_setopt($ch, CURLOPT_USERPWD,
                "{$this->authModule->username}:{$this->authModule->password}");
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        }

        if ($method == 'post') {
            curl_setopt($ch, CURLOPT_POST, 1 );
            curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
        }

        $output = curl_exec($ch);

        if ($output === false) {
            //如果curl执行失败
            throw new ApiException(curl_error($ch));
        }

        $info   = curl_getinfo($ch);
        curl_close($ch);


        if (isset($this->callback)) {
            return call_user_func_array($this->callback,
                array($output, $info['http_code']));
        } else {
            return array('response_text'=>$output,
                'http_code'=>$info['http_code']);
        }
    }


}