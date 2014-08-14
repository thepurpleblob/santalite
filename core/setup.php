<?php

use core\coreController;

/**
 * class autoloader
 */
function __autoload($name) {

    $parts = explode('\\', $name);

    // get class's filename
    $classname = array_pop($parts) . '.php';

    // rest of parts are the path
    $path = implode('/', $parts);
    $path .= '/' . $classname;

    require($path);
}

/**
 * Custom exception handler
 */
function exception_handler(Exception $e) {
    $controller = new coreController(true);
    $controller->View('header');
    $controller->View('exception', array(
        'e' => $e,
    ));
    $controller->View('footer');
}

// MAIN SETUP STUFF

// establish database connection
require_once('idiorm/idiorm.php');
\ORM::configure($CFG->dsn);
\ORM::configure('username', $CFG->dbuser);
\ORM::configure('password', $CFG->dbpass);

// set exception handler
set_exception_handler('exception_handler');

// start the session
session_name('SRPS_Santas');
session_start();

