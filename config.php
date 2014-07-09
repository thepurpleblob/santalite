<?php

unset($CFG);
$CFG = new stdClass;

// Database stuff
$CFG->dsn = "sqlite:/var/www/santa/database/db.sq3";
$CFG->dbuser = '';
$CFG->dbpass = '';
