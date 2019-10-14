<?php

if (!file_exists(dirname(__FILE__) . '/config.php')) {
    die("Not configured. Copy config-dist.php to config.php and edit to create configuration");
}

require_once(dirname(__FILE__) . '/vendor/autoload.php');
require(dirname(__FILE__) . '/config.php');
require(dirname(__FILE__) . '/core/setup.php');

if (!is_cli()) {
    die('Only for use in CLI mode');
}

// Get details
if (count($argv) < 3) {
    echo("Usage: php lostpassword.php username newpassword\n");
}
$username = $argv[1];
$newpassword = $argv[2];

$user = ORM::for_table('user')->where(['username' => $username])->find_one();
if ($user) {
    $user->password = password_hash($newpassword, PASSWORD_DEFAULT);
    $user->save();
    echo("Password has been updated\n");
} else {
    echo("Account '$username' not found\n");
}
