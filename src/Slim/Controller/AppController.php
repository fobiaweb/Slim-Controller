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

/**
 * AppController class
 *
 * @package   Slim.Controller
 */
class AppController extends \Slim\App
{
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
     * @param string $controller
     * @return callable
     */
    public function createController($controller)
    {
        list( $class, $method ) = explode(':', $controller);

        // Method name
        if (!$method) {
            $method = 'index';
        }
        $method =  $this['settings']['controller.action_prefix']
            . $method
            . $this['settings']['controller.action_suffix'];

        // Class name
        if (substr($class, 0, 1) != '\\') {
            $class =  $this['settings']['controller.prefix']. $class;
        }
        $class .= $this['settings']['controller.suffix'];
        $class = str_replace('.', '_', $class);

        // Class arguments
        $classArgs = array_slice(func_get_args(), 1);
        $app = & $this;

        return function() use ( $app, $classArgs, $class, $method ) {
            $methodArgs = func_get_args();
            $classController = new $class( $app, $classArgs );
            // Log::debug("Call method controller: $class -> $method", $methodArgs);
            return call_user_func_array(array($classController, $method), $methodArgs);
        };
    }

    /**
     * Базовый url путь
     *
     * @param string  $url
     * @return string
     */
    public function urlForBase($url = '')
    {
        static $base_url = null;
        if ($base_url === null) {
            if ($this['router']->hasNamedRoute('base')) {
                $base_url = $this->urlFor('base');
            } else {
                $base_url = $this['request']->getScriptName();
            }
        }
        $url = $base_url . $url;
        return preg_replace('|/+|', '/', $url);
    }

    /* ***********************************************
     * OVERRIDE
     * ********************************************** */

    /**
     *
     */
    protected function mapRoute($args)
    {
        $callable = array_pop($args);
        if (!is_callable($callable)) {
            $callable = $this->createController($callable);
        }
        array_push($args, $callable);
        return parent::mapRoute($args);
    }

    protected function dispatchRequest(\Slim\Http\Request $request, \Slim\Http\Response $response)
    {
        try {
            $this->applyHook('slim.before');
            ob_start();
            $this->applyHook('slim.before.router');
            $dispatched = false;
            $matchedRoutes = $this['router']->getMatchedRoutes($request->getMethod(), $request->getPathInfo(), true);
            foreach ($matchedRoutes as $route) {
                // dump($matchedRoutes);
                /* @var $route \Slim\Route */
                try {
                    $this->applyHook('slim.before.dispatch');
                    $dispatched = $route->dispatch();
                    $this->applyHook('slim.after.dispatch');
                    if ($dispatched) {
                        break;
                    }
                } catch (\Slim\Exception\Pass $e) {
                    continue;
                }
            }
            if (!$dispatched) {
                $this->notFound();
            }
            $this->applyHook('slim.after.router');
        } catch (\Slim\Exception\Stop $e) {}
        $response->write(ob_get_clean());
        $this->applyHook('slim.after');
    }
}