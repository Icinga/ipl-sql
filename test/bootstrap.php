<?php

use ipl\Test\Bootstrap;

call_user_func(function () {
    $basedir = dirname(__DIR__);

    if (! class_exists('PHPUnit_Framework_TestCase')) {
        require_once __DIR__ . '/phpunit-compat.php';
    }

    require_once $basedir . '/lib/ipl/Test/Bootstrap.php';
    Bootstrap::cli($basedir);
});
