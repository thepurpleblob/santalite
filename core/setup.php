<?php

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

// MAIN SETUP STUFF

// establish database connection
require_once('idiorm/idiorm.php');
\ORM::configure($CFG->dsn);
\ORM::configure('username', $CFG->dbuser);
\ORM::configure('password', $CFG->dbpass);

// start the session
session_name('SRPS_Santas');
session_start();

