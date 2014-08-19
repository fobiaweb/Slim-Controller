<?php
/**
 * Slim - a micro PHP 5 framework
 *
 * @author      Dmitriy Tyurin <fobia3d@gmail.com>
 * @copyright   Copyright (c) 2014 Dmitriy Tyurin
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
namespace Slim\Controller;

use Slim\Interfaces\Http\RequestInterface;
use Slim\Http\Request;
/**
 * Router class
 *
 * @package   Slim.Controller
 */
class Router
{

    /**
     * @var \Slim\Router
     */
    private $router;

    /**
     * @var \Slim\App
     */
    protected $app;
    private $request;

    /**
     *
     * @param \Slim\App $app
     * @param \Slim\Interfaces\Http\RequestInterface $request
     */
    public function __construct(\Slim\App $app, RequestInterface $request = null)
    {
        $this->app     = $app;
        $this->request = $request;
        
        $this->router  = new \Slim\Router();
    }

    /**
     * @return  \Slim\Router
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * Возвращает функцию автозоздания контролера
     *
     * Конфиги:
     *   controller.prefix          - префикс к классу контролера
     *   controller.suffix          - суффикс к классу контролера
     *   controller.action_prefix   - префикс к методу действия
     *   controller.action_suffix   - суффикс к методу действия
     *
     * Если имя контролера начинаеться с '\' - расматриваеться как абсолютный путь
     * и конфиг 'controller.prefix' не применеються
     *
     * Example:
     * При controller.prefix = '\Controller' значение 'AuthController:login'
     * приобразуеться в \Controller\AuthController->login().
     * А запись '\AuthController:login' будет \AuthController->login()
     *
     * ### Вызов метода из объекта контролера (с ночальным префиксом)
     * e.g `createController('MyController:action');`
     *
     * ### Вызов метода из объекта контролера (глобального пространства имен)
     * e.g `createController('\MyController:action');`
     *
     * ### Вызов метода по умолчанию из объекта контролера (с ночальным префиксом)
     * e.g `createController('MyController');`
     *
     * ### Вызов callback функции
     * e.g `createController(array($obj, $method));`
     *
     * @param string $name
     * @return callable
     */
    public function createController($name)
    {
        if ( is_array($name) ) {
            return function() use($name) {
                $methodArgs = func_get_args();
                return call_user_func_array( $name, $methodArgs );
            };
        }
        
        if ( !is_string($name) ) {
            return $name;
        }

        $app = & $this->app;
        list( $class, $method ) = explode(':', $name);

        // Method name
        if ( !$method ) {
            $method = 'index';
        }
        $method =  $app['settings']['controller.action_prefix']
            . $method
            . $this->app['settings']['controller.action_suffix'];

        // Class name
        if (substr($class, 0, 1) != '\\') {
            $class =  $app['settings']['controller.prefix']. $class;
        }
        $class .= $app['settings']['controller.suffix'];
        $class = str_replace('.', '_', $class);

        // Class arguments
        $classArgs = array_slice(func_get_args(), 1);
        
        return function() use ( $app, $classArgs, $class, $method ) {
            $methodArgs = func_get_args();
            $controller = new $class( $app, $classArgs );
            return call_user_func_array( array($controller, $method), $methodArgs );
        };
    }


    /************************************************************************
     * Routing
     * ********************************************************************** */

    /**
     * Add GET|POST|PUT|PATCH|DELETE route
     *
     * Adds a new route to the router with associated callable. This
     * route will only be invoked when the HTTP request's method matches
     * this route's method.
     *
     * ARGUMENTS:
     *
     * First:       string  The URL pattern (REQUIRED)
     * In-Between:  mixed   Anything that returns TRUE for `is_callable` (OPTIONAL)
     * Last:        mixed   Anything that returns TRUE for `is_callable` (REQUIRED)
     *
     * The first argument is required and must always be the
     * route pattern (ie. '/books/:id').
     *
     * The last argument is required and must always be the callable object
     * to be invoked when the route matches an HTTP request.
     *
     * You may also provide an unlimited number of in-between arguments;
     * each interior argument must be callable and will be invoked in the
     * order specified before the route's callable is invoked.
     *
     * USAGE:
     *
     * Slim::get('/foo'[, middleware, middleware, ...], callable);
     *
     * @param  array
     * @return \Slim\Route
     */
    protected function mapRoute($args)
    {
        $pattern  = array_shift($args);
        $callable = array_pop($args);

        //if ( ! is_callable($callable)) {
        if ( is_string($callable) || is_array($callable) ) {
            $callable = $this->createController($callable);
        }
        
        $route = new \Slim\Route($pattern, $callable,
                                 $this->app['settings']['case_sensitive']);
        $this->router->map($route);
        if (count($args) > 0) {
            $route->setMiddleware($args);
        }

        return $route;
    }

    /**
     * Add route without HTTP method
     * 
     * @return \Slim\Route
     */
    public function map()
    {
        $args = func_get_args();

        return $this->mapRoute($args);
    }

    /**
     * Add GET route
     *
     * @return \Slim\Route
     * @api
     */
    public function get()
    {
        $args = func_get_args();

        return $this->mapRoute($args)->via(Request::METHOD_GET, Request::METHOD_HEAD);
    }

    /**
     * Add POST route
     *
     * @return \Slim\Route
     * @api
     */
    public function post()
    {
        $args = func_get_args();

        return $this->mapRoute($args)->via(Request::METHOD_POST);
    }

    /**
     * Add PUT route
     *
     * @return \Slim\Route
     * @api
     */
    public function put()
    {
        $args = func_get_args();

        return $this->mapRoute($args)->via(Request::METHOD_PUT);
    }

    /**
     * Add PATCH route
     *
     * @return \Slim\Route
     * @api
     */
    public function patch()
    {
        $args = func_get_args();

        return $this->mapRoute($args)->via(Request::METHOD_PATCH);
    }

    /**
     * Add DELETE route
     *
     * @return \Slim\Route
     * @api
     */
    public function delete()
    {
        $args = func_get_args();

        return $this->mapRoute($args)->via(Request::METHOD_DELETE);
    }

    /**
     * Add OPTIONS route
     *
     * @return \Slim\Route
     * @api
     */
    public function options()
    {
        $args = func_get_args();

        return $this->mapRoute($args)->via(Request::METHOD_OPTIONS);
    }

    /**
     * Route Groups
     *
     * This method accepts a route pattern and a callback. All route
     * declarations in the callback will be prepended by the group(s)
     * that it is in.
     *
     * Accepts the same parameters as a standard route so:
     * (pattern, middleware1, middleware2, ..., $callback)
     *
     * @api
     */
    public function group()
    {
        $args     = func_get_args();
        $pattern  = array_shift($args);
        $callable = array_pop($args);
        $this->router->pushGroup($pattern, $args);
        if (is_callable($callable)) {
            call_user_func($callable);
        }
        $this->router->popGroup();
    }

    /**
     * Add route for any HTTP method
     * @return \Slim\Route
     * @api
     */
    public function any()
    {
        $args = func_get_args();

        return $this->mapRoute($args)->via("ANY");
    }
    
    /* ***********************************************
     * OVERRIDE
     * ********************************************** */

    public function run()
    {
        $request = $this->request;
        if ( ! $request) {
            $request = $this->app['request'];
        }

        $this->dispatchRequest($request);
    }

    protected function dispatchRequest(Request $request)
    {
        try {
            $dispatched    = false;
            $matchedRoutes = $this->getRouter()
                    ->getMatchedRoutes($request->getMethod(),
                                       $request->getPathInfo());
            foreach ($matchedRoutes as $route) {
                try {
                    $this->app->applyHook('slim.before.dispatch');
                    $dispatched = $route->dispatch();
                    $this->app->applyHook('slim.after.dispatch');
                    if ($dispatched) {
                        break;
                    }
                } catch (\Slim\Exception\Pass $e) {
                    continue;
                }
            }
            if ( ! $dispatched) {
                $this->app->notFound();
            }
        } catch (\Slim\Exception\Stop $e) {
            throw $e;
        }
    }
}