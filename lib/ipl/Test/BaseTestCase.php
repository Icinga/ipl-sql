<?php

namespace ipl\Test;

use ipl\Loader\CompatLoader;
use Icinga\Application\Icinga;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

/**
 * Class BaseTestCase
 * @package ipl\Test
 */
abstract class BaseTestCase extends PHPUnit_Framework_TestCase
{
    private static $app;

    public function setUp()
    {
        // $this->setupCompatLoader();
    }

    /**
     * @param $obj
     * @param $name
     * @return \ReflectionMethod
     */
    public function getProtectedMethod($obj, $name)
    {
        $class = new ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * @param $obj
     * @param $name
     * @return \ReflectionMethod
     */
    public function getPrivateMethod($obj, $name)
    {
        return $this->getProtectedMethod($obj, $name);
    }

    /**
     * Initialize our CompatLoader
     */
    protected function setupCompatLoader()
    {
        require_once dirname(__DIR__) . '/Loader/CompatLoader.php';
        CompatLoader::delegateLoadingToIcingaWeb($this->app());
    }

    /**
     * Lazy loading for tests requiring an Icinga\Application
     *
     * @return \Icinga\Application\ApplicationBootstrap
     */
    protected function app()
    {
        if (self::$app === null) {
            self::$app = Icinga::app();
        }

        return self::$app;
    }
}
