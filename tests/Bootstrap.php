<?php

error_reporting(E_ALL | E_STRICT);
chdir(__DIR__);

// Set the default timezone. While this doesn't cause any tests to fail, PHP
// complains if it is not set in 'date.timezone' of php.ini.
date_default_timezone_set('UTC');

echo PHP_EOL;
echo "Value of PHP_SAPI: " . PHP_SAPI . PHP_EOL;
echo "Value of php_sapi_name(): " . php_sapi_name() . PHP_EOL;
echo '$_SERVER[\'HTTP_USER_AGENT\'] is set: ' . (isset($_SERVER['HTTP_USER_AGENT']) ? 'true' : 'false') . PHP_EOL;
echo PHP_EOL;


// Ensure that composer has installed all dependencies
if (!file_exists(dirname(__DIR__) . '/composer.lock')) {
    die("Dependencies must be installed using composer:\n\nphp composer.phar install --dev\n\n"
        . "See http://getcomposer.org for help with installing composer\n");
}

// Include the composer autoloader
$autoloader = require_once(dirname(__DIR__) . '/vendor/autoload.php');
