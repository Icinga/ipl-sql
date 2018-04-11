<?php

call_user_func(function () {
    $basedir = dirname(__DIR__);

    if (! class_exists('PHPUnit_Framework_TestCase')) {
        require_once __DIR__ . '/phpunit-compat.php';
    }

    $include_path = $basedir . '/vendor' . PATH_SEPARATOR . ini_get('include_path');
    ini_set('include_path', $include_path);

    error_reporting(E_ALL | E_STRICT);
    require_once "$basedir/vendor/autoload.php";
});
