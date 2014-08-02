<?php

require(dirname(__FILE__) . '/config.php');
require(dirname(__FILE__) . '/core/setup.php');
require_once(dirname(__FILE__) . '/lib/sagelib.php');

$CFG->basepath = dirname(__FILE__);

error_reporting(E_ALL);
ini_set('display_errors', 'stdout');

$info = $_SERVER['PATH_INFO'];
if ($info) {
    $paths = explode('/', $info);
} else {
    throw new Exception("No path specified");
}

// get controller and action
$controller_name = $paths[1];
if (!$action_name = $paths[2]) {
    throw new Exception("No action specified for controller '$controller'");
}

// try to load controller
$controller_name = '\\controller\\' . $controller_name . 'Controller';
$controller = new $controller_name;

// execute specified action
$action_name .= 'Action';
array_shift($paths);
array_shift($paths);
array_shift($paths);
call_user_func_array(array($controller, $action_name), $paths);
