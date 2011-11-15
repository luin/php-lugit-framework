<?php

require __DIR__ . '/controllers/Controller.php';
require __DIR__ . '/views/ViewLoader.php';

function __autoLoad($className)
{
    if ('Helper' === substr($className, -6)) {
        //auto load view helper
        if (file_exists(__DIR__ . "/views/helpers/$className.php")) {
            include __DIR__ . "/views/helpers/$className.php";
            return true;
        } elseif (file_exists("./helpers/$className.php")) {
            include "./helpers/$className.php";
            return true;
        }
    } elseif ('Module' === substr($className, -6)) {
        //auto load controller module
        if (file_exists(__DIR__ . "/controllers/modules/$className.php")) {
            include __DIR__ . "/controllers/modules/$className.php";
            return true;
        } elseif (file_exists("./controllers/modules/$className.php")) {
            include "./controllers/modules/$className.php";
            return true;
        }
    } elseif ('Exception' === substr($className, -9)) {
        include __DIR__ . "/core/Exceptions.php";
        return true;
    } else {
        //auto load core
        if (file_exists(__DIR__ . "/core/$className.php")) {
            include __DIR__ . "/core/$className.php";
            return true;
        }
    }
    return false;
}


class Lugit
{
    public static $controller;
    public static $viewLoader;

    private static function initConst()
    {
        define('LUGIT_ROOT', __DIR__);

        
        if (!defined('DEBUG_MODE')) define('DEBUG_MODE', false);

        //是否自动对request获得的内容转义
        if (!defined('AUTO_ESCAPE')) define('AUTO_ESCAPE', true);


        //默认Controller
        if (!defined('DEFAULT_CONTROLLER_NAME')) define('DEFAULT_CONTROLLER_NAME', 'Adapt');

        //默认Action
        if (!defined('DEFAULT_ACTION_NAME')) define('DEFAULT_ACTION_NAME', 'adapt');

    }


    public static function run()
    {
        self::initConst();

        date_default_timezone_set('Etc/GMT-8');

        error_reporting(DEBUG_MODE ? E_ALL : E_ALL ^ E_NOTICE ^ E_WARNING);

        set_exception_handler(array('Basic', 'exceptionHandle'));

        Singleton::getInstance('Router')->dispatch();
    }


}
