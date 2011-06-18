<?php
class Basic
{
    private static function _getErrorFilePath($errorCode)
    {
        if (file_exists($file = "./view/system/error$code.phtml")) {
            return $file;
        } elseif (file_exists($file = "./view/system/error.phtml")) {
            return $file;
        }
        return false;
    }


    public static function exceptionHandle(Exception $exception)
    {
        if (DEBUG_MODE) {
            //直接输出调试信息
            echo nl2br($exception->__toString());
            echo '<hr /><p>Router:</p><pre>';
            print_r(Singleton::getInstance('Router'));
            echo '</pre>';

        } else {
            $code = $exception->getCode();
            $message = nl2br($exception->getMessage());

            /*
            如果错误码"可能为"合法的http状态码则尝试设置,
            setStatus()方法会忽略非法的http状态码. */
            if ($code >= 400 && $code <= 505 && !headers_sent()) {
                ResponseModule::setStatus($code);
            }

            $var_list = array(
                'message' => $message,
                'code' => $code,
                'file' => $exception->getFile(),
                'url' => Singleton::getInstance('Router')->getUrl()
            );
            if ($error_file = self::_getErrorFilePath($code)) {
                Lugit::$viewLoader = new viewLoader($var_list, $error_file);
                Lugit::$viewLoader->render();
            } else {
                echo 'No error page is found.<pre>';
                print_r($var_list);
                echo '</pre>';
            }
        }
        exit;
    }


    public static function safeUrl($url)
    {
        return get_magic_quotes_gpc() ? $url : addslashes($url);
    }


    public static function saftParameter($var)
    {

    }


    public static function safeSql($sql)
    {
        return get_magic_quotes_gpc() ? $sql : addslashes($sql);
    }


    public static function requireOr404($file)
    {
        if (!file_exists($file)) throw new NotFoundException();
        require $file;
    }


    public static function includeOr404($file)
    {
        if (!file_exists($file)) throw new NotFoundException();
        include $file;
    }


    public static function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0)
    {

        $ckey_length = 4;
        // 随机密钥长度 取值 0-32;
        // 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
        // 取值越大，密文变动规律越大，密文变化 = 16 的 $ckey_length 次方
        // 当此值为 0 时，则不产生随机密钥

        $key = md5($key ? $key : UC_KEY);
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

        $cryptkey = $keya.md5($keya.$keyc);
        $key_length = strlen($cryptkey);

        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
        $string_length = strlen($string);

        $result = '';
        $box = range(0, 255);

        $rndkey = array();
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }

        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }

        if ($operation == 'DECODE') {
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            return $keyc.str_replace('=', '', base64_encode($result));
        }
    }


    public static function resolveCallback($callback, $className = null)
    {
        if (is_array($callback)) return $callback;

        if (function_exists($callback)) return $callback;
        elseif (strpos($function_name, '::')) return explode('::', $function_name);
        elseif ($className && method_exists($className, $callback)) return array($className, $callback);

        return null;
    }


}