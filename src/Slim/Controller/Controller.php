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
 * Controller class
 *
 * @package   Slim.Controller
 */
class Controller
{
    /**
     * @var \Fobia\Base\Application
     */
    public $app;
    public $params   = array();

    public function __construct(Application $app, $params = array())
    {
        $this->app      = $app;
        $this->params   = $params;
    }

    public function section($section = null)
    {
        if ($section === null || !method_exists($this, $section) ) {
            $this->app->notFound();
        }

        $args = array_slice(func_get_args(), 1);
        dispatchMethod($this, $section, $args);
    }

    public function index()
    {
        $this->app->notFound();
    }

    public function errorAction()
    {
        $this->app->error();
    }
}