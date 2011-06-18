<?php
/**
 * 实现可继承的Singleton基类.
 */
class Singleton
{
    protected static $instances = array();

    public static function getInstance($className)
    {
        if(!isset(self::$instances[$className])) {
            self::$instances[$className] = new $className;
        }
        return self::$instances[$className];
    }

    protected function __construct() { }

    protected function __clone() { }

}
