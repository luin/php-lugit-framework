<?php
class ValidatorModule extends Singleton
{
    /**
     * 应用验证器并返回过滤后的结果.
     * 用法:
     * Singleton->getInstance('ValidatorModule')->applyValidator('not_empty', '');
     * //返回false
     *
     * Singleton->getInstance('ValidatorModule')->applyValidator('not_empty', array('', 'abc'));
     * //返回false
     *
     * 支持静态方法
     * Singleton->getInstance('ValidatorModule')->applyValidator('YourClass::yourMethod', 'abc');
     *
     * @access public
     * @param string $validator 验证器名称
     * @param mixed $data 要处理的数据
     * @return bool 验证结果
     */
    public function applyValidator($validator, $data)
    {
        $result = Singleton::getInstance('FilterModule')->applyFilter($validator, $data);
        if (is_array($result)) {
            foreach ($result as $v) {
                if (!$v) return false;
            }
            return true;
        }
        return (bool)$result;
    }


    /**
     * 应用验证器并返回验证结果.
     *
     * @access public
     * @param array $validators 验证器列表
     * @param mixed $data 要验证的数据
     * @param array & $faults (default: null) 返回验证结果
     * @return bool 验证结果
     */
    public function applyValidators($validators, $data, & $faults = null)
    {
        $skip_on_failed = (func_num_args() === 2);
        $faults = array();
        $result = true;

        if (is_array($validators)) {
            foreach ($validators as $validator) {
                if ($this->applyValidator($validator, $data)) {
                    $faults[] = true;
                } else {
                    if ($skip_on_failed) return false;

                    $faults[] = $result = false;
                }
            }
        } else {
            throw new exception('applyFilters() need first param to be a array.');
        }
        return $result;
    }



    public static function not_empty($value)
    {
        if (is_object($value) && $value instanceof ArrayObject) {
            $value = $value->getArrayCopy();
        }

        return $value === '0' or ! empty($value);
    }


    public static function regex($value, $expression)
    {
        return (bool) preg_match($expression, (string) $value);
    }


    public static function min_length($value, $length)
    {
        return mb_strlen($value) >= $length;
    }


    public static function max_length($value, $length)
    {
        return mb_strlen($value) <= $length;
    }


    public static function exact_length($value, $length)
    {
        return mb_strlen($value) === $length;
    }


    public static function email($email)
    {
        $expression = '/^[-_a-z0-9\'+*$^&%=~!?{}]++(?:\.[-_a-z0-9\'+*$^&%=~!?{}]+)*+@(?:(?![-.])[-a-z0-9.]+(?<![-.])\.[a-z]{2,6}|\d{1,3}(?:\.\d{1,3}){3})(?::\d++)?$/iD';

        return (bool) preg_match($expression, (string) $email);
    }


    public static function url($url)
    {
        return (bool) filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED);
    }


    public static function ip($ip, $allow_private = TRUE)
    {
        $flags = FILTER_FLAG_NO_RES_RANGE;

        if ($allow_private === FALSE) {
            $flags = $flags | FILTER_FLAG_NO_PRIV_RANGE;
        }

        return (bool) filter_var($ip, FILTER_VALIDATE_IP, $flags);
    }


    public static function date($str)
    {
        return strtotime($str) !== FALSE;
    }


    public static function alpha($str)
    {
        $str = (string) $str;
        return ctype_alpha($str);
    }


    public static function alpha_numeric($str)
    {
        return ctype_alnum($str);
    }


    public static function alpha_dash($str)
    {
        $regex = '/^[-a-z0-9_]++$/iD';
        return (bool) preg_match($regex, $str);
    }


    public static function digit($str)
    {
        return is_int($str) || ctype_digit($str);
    }


    public static function numeric($str)
    {
        list($decimal) = array_values(localeconv());
        return (bool) preg_match('/^-?[0-9'.$decimal.']++$/D', (string) $str);
    }


    public static function range($number, $min, $max)
    {
        return $number >= $min && $number <= $max;
    }


    public static function color($str)
    {
        return (bool) preg_match('/^#?+[0-9a-f]{3}(?:[0-9a-f]{3})?$/iD', $str);
    }



}
