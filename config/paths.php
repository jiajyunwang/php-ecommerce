<?php

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

define('ROOT', dirname(__DIR__));
define('FRONT', ROOT . DS . 'views' . DS . 'frontend' . DS);
define('BACK', ROOT . DS . 'views' . DS . 'backend' . DS);
define('AUTH', ROOT . DS . 'views' . DS . 'auth' . DS);