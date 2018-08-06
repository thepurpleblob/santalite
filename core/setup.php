<?php
/**
 * SRPS Santa Booking
 *
 * Copyright 2018, Howard Miller (howardsmiller@gmail.com)
 *
 * Various setup tasks
 */

use thepurpleblob\core\coreController;

/**
 * Custom exception handler
 */
function exception_handler($e) {
    $controller = new coreController(true);
    $controller->View('header');
    $controller->View('exception', array(
        'e' => $e,
    ));
    $controller->View('footer');
}

// MAIN SETUP STUFF

// establish database connection
ORM::configure($CFG->dsn);
ORM::configure('username', $CFG->dbuser);
ORM::configure('password', $CFG->dbpass);

// set exception handler
//set_exception_handler('exception_handler');

// start the session
ini_set('session.gc_maxlifetime', 7200);
session_set_cookie_params(7200);
session_name('SRPS_Santas');
session_start();

