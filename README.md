# Middleware Service

A PSR-15 middleware dispatcher with autowiring and alias support.

## Table of Contents

- [Getting started](#getting-started)
    - [Requirements](#requirements)
    - [Highlights](#highlights)
- [Documentation](#documentation)
    - [Creating Middleware Dispatcher](#creating-middleware-dispatcher)
    - [Adding Middleware](#adding-middleware)
    - [Aliases](#aliases)
    - [Stack Priority](#stack-priority)
    - [Dispatch](#dispatch)
- [Credits](#credits)
___

# Getting started

Add the latest version of the middleware service running this command.

```
composer require tobento/service-middleware
```

## Requirements

- PHP 8.0 or greater

## Highlights

- Framework-agnostic, will work with any project
- Decoupled design
- Autowiring middlewares
- Aliasing middlewares

# Documentation

## Creating Middleware Dispatcher

```php
use Tobento\Service\Middleware\MiddlewareDispatcher;
use Tobento\Service\Middleware\AutowiringMiddlewareFactory;
use Tobento\Service\Middleware\FallbackHandler;
use Tobento\Service\Container\Container;
use Nyholm\Psr7\Factory\Psr17Factory;

// create middleware dispatcher.
$dispatcher = new MiddlewareDispatcher(
    new FallbackHandler((new Psr17Factory())->createResponse(404)),
    new AutowiringMiddlewareFactory(new Container()) // any PSR-11 container
);
```

**new**

You may use the ```new``` method to create a new instance. It will keep the added aliases though.

```php
$newDispatcher = $dispatcher->new();
```

## Adding Middleware

By anonymous function:

```php
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

$dispatcher->add(function(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
    return $handler->handle($request);
});
```

By class instance:

```php
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;

class Middleware implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $response = $handler->handle($request);
        $response->getBody()->write('Hello word');
        return $response;
    }
}

$dispatcher->add(new Middleware());
```

By class name:

```php
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;

class Middleware implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $response = $handler->handle($request);
        $response->getBody()->write('Hello word');
        return $response;
    }
}

$dispatcher->add(Middleware::class);
```

By class name with build-in parameters (not resolvable by autowiring):

```php
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;

class Middleware implements MiddlewareInterface
{
    public function __construct(
        protected string $name,
    ) {}
    
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $response = $handler->handle($request);
        $response->getBody()->write('Hello '.$this->name);
        return $response;
    }
}

$dispatcher->add([Middleware::class, 'name' => 'Sam']);
```

Adding multiple at once:

```php
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;

class Middleware implements MiddlewareInterface
{
    public function __construct(
        protected string $name,
    ) {}
    
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $response = $handler->handle($request);
        $response->getBody()->write('Hello '.$this->name);
        return $response;
    }
}

$dispatcher->add(
    [Middleware::class, 'name' => 'Sam'],
    function(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        return $handler->handle($request);
    },
);
```

## Aliases

You might want to use aliases instead of class names:

```php
// add single alias.
$dispatcher->addAlias('alias', Middleware::class);

// add multiple alias.
$dispatcher->addAliases([
    'alias' => Middleware::class,
]);

// add middleware by alias.
$dispatcher->add('alias');

// if you have not resolvable parameters:
$dispatcher->add(['alias', 'name' => 'Sam']);
```

## Stack Priority

You might want to prioritize the excution order of the middlewares by the following way:

```php
// highest number first.
$dispatcher->add(Middleware::class, priority: 100);

$dispatcher->add(AnotherMiddleware::class, priority: 200);
```

## Dispatch

```php
use Nyholm\Psr7\Factory\Psr17Factory;

$request = (new Psr17Factory())->createServerRequest('GET', 'https://example.com');

$response = $dispatcher->handle($request);
```

# Credits

- [Tobias Strub](https://www.tobento.ch)
- [All Contributors](../../contributors)