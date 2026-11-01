<?php

error_reporting(E_ALL);

// RestServer writes its URL map cache (urlMap.cache) into the current working directory.
chdir(__DIR__);

date_default_timezone_set('UTC');

require dirname(__DIR__) . '/vendor/autoload.php';
