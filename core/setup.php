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
if ($CFG->dsn) {
    try {
        $DB = new \PDO($CFG->dsn);
    } catch (PDOException $e) {
        die( "Database connection failed: " . $e->getMessage());
    }
    $DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
} else {
    $DB = null;
}

