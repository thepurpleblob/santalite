<?php
/**
 * SRPS Santa Booking
 *
 * Copyright 2018, Howard Miller (howardsmiller@gmail.com)
 *
 * Various setup tasks
 */

use thepurpleblob\core\coreController;
use thepurpleblob\core\coreSession;

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

// Check if database has been created
ORM::raw_execute('SHOW TABLES');
$statement = ORM::get_last_statement();
$tables = $statement->fetchAll();
if (!count($tables)) {
    require_once(dirname(__FILE__) . '/../src/asset/sql/santa_schema.php');
    foreach ($schema as $sql) {
        ORM::raw_execute($sql);
    }
}

// Check if there is an admin account
if (!ORM::forTable('user')->where(['username' => 'admin'])->findOne()) {
    $admin = ORM::forTable('user')->create();
    $admin->username = 'admin';
    $admin->fullname = 'Santa Administrator';
    $admin->password = password_hash('password', PASSWORD_DEFAULT); // Obviously, change this asap
    $admin->role = 'admin';
    $admin->save();
}

// Check if there is a fares record
if (!ORM::forTable('fares')->findOne(1)) {
    $fares = ORM::forTable('fares')->create();
    $fares->id = 1;
    $fares->adult = 0;
    $fares->child = 0;
    $fares->save();
}

// Check if there are any times
if (!ORM::forTable('traintime')->count()) {
    $defaulttimes = ['10:30', '12:00', '13:30', '15:00'];
    foreach ($defaulttimes as $defaulttime) {
        $traintime = ORM::forTable('traintime')->create();
        $traintime->time = $defaulttime;
        $traintime->save();
    }
}

// set exception handler
//set_exception_handler('exception_handler');

// start the session
$coresession = new coreSession;

