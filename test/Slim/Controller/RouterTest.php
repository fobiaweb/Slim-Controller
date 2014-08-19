<?php

use Slim\Controller\Router;


class MyController extends \Slim\Controller\Controller
{
    public function one()
    {
        return 'one';
    }

    public function two($param)
    {
        return $param;
    }
}

class RouterTest extends \PHPUnit_Framework_TestCase
{

    /**
     *
     * @param type $request
     * @return \Slim\Controller\Router
     */
    protected function createRouter($request = null)
    {
        $app = new \Slim\App();
        $router = new \Slim\Controller\Router($app, ($request) ? $request : $app['request']);

        return $router;
    }

    /**
     * Constructor should initialize routes as empty array
     */
    public function testConstruct()
    {
        $app = new \Slim\App();
        $router = new \Slim\Controller\Router($app, $app['request']);
        $this->assertInstanceOf('\Slim\Router', $router->getRouter());
    }

    /**
     * Map should set and return instance of \Slim\Route
     */
    public function testMap()
    {
        $router =  $this->createRouter();
        $route = $router->map('/foo', function() {})->via('GET');
        $this->assertAttributeContains($route, 'routes', $router->getRouter());
    }

    /*
     * По строковому названию
     */
    public function testCreateController1()
    {
        $router = $this->createRouter();

        $callback = $router->createController('MyController:one');
        $this->assertInstanceOf('Closure', $callback);
        $this->assertEquals('one', $callback());

        $callback = $router->createController('MyController:two');
        $this->assertEquals('bar', $callback('bar'));
    }

    /*
     * Создания контролера по лямбда функции
     */
    public function testCreateController2()
    {
        $router = $this->createRouter();

        $callback = $router->createController(function() {
            return "closure";
        });
        $this->assertInstanceOf('Closure', $callback);
        $this->assertEquals('closure', $callback());
    }

    /*
     * Создания контролера по масиву
     */
    public function testCreateController3()
    {
        $router = $this->createRouter();
        $obj = new MyController(new \Slim\App());
        
        $callback = $router->createController(array($obj, 'one'));

        $this->assertInstanceOf('Closure', $callback);
        $this->assertEquals( 'one', $callback() );
    }
}
