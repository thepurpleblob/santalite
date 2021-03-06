<?php
/**
 * SRPS Santa Booking
 * 
 * Copyright 2018, Howard Miller (howardsmiller@gmail.com)
 *
 * Main index/entry point
 */

if (!file_exists(dirname(__FILE__) . '/config.php')) {
    die("Not configured. Copy config-dist.php to config.php and edit to create configuration");
}

require_once(dirname(__FILE__) . '/vendor/autoload.php');
require(dirname(__FILE__) . '/config.php');
require(dirname(__FILE__) . '/core/setup.php');

$CFG->basepath = dirname(__FILE__);

error_reporting(E_ALL);
ini_set('display_errors', 'stdout');

// If no path is given, just start a booking
if (isset($_SERVER['PATH_INFO'])) {
    $info = $_SERVER['PATH_INFO'];
} else {
    $info = '/booking/start';
}

// If path is just /admin...
if ($info=='/admin') {
    $info = '/admin/index';
}

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
$controller_name = '\\thepurpleblob\\santa\\controller\\' . $controller_name . 'Controller';
$controller = new $controller_name;

// execute specified action
$action_name .= 'Action';
array_shift($paths);
array_shift($paths);
array_shift($paths);
call_user_func_array(array($controller, $action_name), $paths);
