<?php

namespace Slim\Controller\Test;

use Slim\Controller\SlimController;

class DummyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * A dummy test that calls a beacon method ensuring the class is autolaoded.
     *
     * @see https://github.com/cpliakas/php-project-starter/issues/19
     * @see https://github.com/cpliakas/php-project-starter/issues/21
     */
    public function testAutoload()
    {
        $class = new SlimController();
        $this->assertTrue($class->autoloaded());
    }
}