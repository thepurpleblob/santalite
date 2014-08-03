<?php

unset($CFG);
$CFG = new stdClass;

// Database stuff
$CFG->dsn = "";
$CFG->dbuser = '';
$CFG->dbpass = '';

// paths
$CFG->www = '';

$CFG->dirroot = '';

// defaults for limits
$CFG->default_limit = 100;
$CFG->default_party = 8;

// number of people limit in each select
$CFG->select_limit = 8;

// Sagepay stuff
$CFG->sage_url = 'https://test.sagepay.com/gateway/service/vspform-register.vsp';
$CFG->sage_vendor = 'srpsrailtours';
$CFG->sage_encrypt = 'Qpu7H4zy5L5Wbwpb';
$CFG->sage_email = 'howardsmiller@gmail.com'; // multiple separate with colons
$CFG->sage_message = ''; // message inserted into sage email (up to 700 chars)
$CFG->sage_prefix = 'TS'; // prefix for booking codes (MUST be unique for installation)