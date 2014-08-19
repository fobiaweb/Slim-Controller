# Slim-Controller

[![Total Downloads](https://poser.pugx.org/fobiaweb/Slim-Controller/downloads.png)](https://packagist.org/packages/fobiaweb/Slim-Controller) [![Latest Stable Version](https://poser.pugx.org/fobiaweb/Slim-Controller/v/stable.png)](https://packagist.org/packages/fobiaweb/Slim-Controller)



## Installation

fobiaweb/Slim-Controller can be installed with [Composer](http://getcomposer.org)
by adding it as a dependency to your project's composer.json file.

```json
{
    "require": {
        "fobiaweb/slim-controller": "*"
    }
}
```

Please refer to [Composer's documentation](https://github.com/composer/composer/blob/master/doc/00-intro.md#introduction)
for more detailed installation and usage instructions.


## Usage

Инициализация

    <?php
    $app = new \Slim\App();
    $router = new \Slim\Controller\Router($app);
    // ... router map
    $router->run();

Маршрутизация запросов:
    
    <?php
    // традиционый
    $router->get('/foo', function() {} );
    // Создает объект класса MyController и вызывает метод по умолчанию
    $router->get('/foo', 'MyController' );
    // Создает объект класса MyController и вызывает метод actionOther
    $router->get('/foo', 'MyController:actionOther' );
    // Запускает функцию callable
    $router->get('/foo', array($obj, 'method') );


Методы запросов маршрутизации

    <?php
    // GET /foo
    $router->get('/foo', $callback );
    // POST /foo
    $router->post('/foo', $callback );
    // PUT /foo
    $router->put('/foo', $callback );
    // DELETE /foo
    $router->delete('/foo', $callback );
    // GET, POST, PUT, HEAD, DELETE /foo
    $router->any('/foo', $callback );

    // GET /foo/bar
    // GET /foo/baz
    $router->group('/foo', function() use ($router) {
        $router->get('/bar', $callback);
        $router->get('/baz', $callback);
    });





### --

    <?php
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

