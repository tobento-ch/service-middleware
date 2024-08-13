<?php

/**
 * TOBENTO
 *
 * @copyright   Tobias Strub, TOBENTO
 * @license     MIT License, see LICENSE file distributed with this source code.
 * @author      Tobias Strub
 * @link        https://www.tobento.ch
 */

declare(strict_types=1);

namespace Tobento\Service\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Container\ContainerInterface;
use Tobento\Service\Autowire\Autowire;
use Tobento\Service\Autowire\AutowireException;

/**
 * AutowiringMiddlewareFactory
 */
class AutowiringMiddlewareFactory implements MiddlewareFactoryInterface
{
    /**
     * @var Autowire
     */    
    protected Autowire $autowire;
    
    /**
     * @var array The middlewares to replace.
     */
    protected array $replaces = [];
    
    /**
     * Create a new MiddlewareDispatcher.
     *
     * @param ContainerInterface $container
     * @param array $replaces
     */    
    public function __construct(
        ContainerInterface $container,
        array $replaces = [],
    ) {
        $this->autowire = new Autowire($container);
        $this->replaces = $replaces;
    }
    
    /**
     * Add a middleware to replace.
     *
     * @param string $middleware
     * @param mixed $withMiddleware
     * @return static $this
     */
    public function replaceMiddleware(string $middleware, mixed $withMiddleware): static
    {
        $this->replaces[$middleware] = $withMiddleware;
        return $this;
    }
    
    /**
     * Returns the middlewares to replace.
     *
     * @return array
     */
    public function getReplaceMiddlewares(): array
    {
        return $this->replaces;
    }
    
    /**
     * Create middleware.
     *
     * @param mixed $middleware
     *
     * @throws InvalidMiddlewareException
     *
     * @return MiddlewareInterface
     */    
    public function createMiddleware(mixed $middleware): MiddlewareInterface
    {
        // if it is already an instance, just return.
        if ($middleware instanceof MiddlewareInterface) {
            return $middleware;
        }
        
        if (is_callable($middleware)) {
            return $this->createCallableMiddleware($middleware);
        }
        
        $middlwareData = [];
            
        if (
            is_array($middleware) 
            && isset($middleware[0])
            && is_string($middleware[0])
        ) {
            $middlwareData = $middleware;
            
            // remove middleware
            array_shift($middlwareData);

            $middleware = $middleware[0];
        }
        
        if (!is_string($middleware)) {
            throw new InvalidMiddlewareException($middleware);
        }
        
        // handle replaces:
        if (!empty($this->replaces) && array_key_exists($middleware, $this->replaces)) {
            if (is_null($this->replaces[$middleware])) {
                return $this->createCallableMiddleware(
                    function (ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
                        return $handler->handle($request);
                    }
                );
            } else {
                $mw = $this->replaces[$middleware];
                unset($this->replaces[$middleware]);
                return $this->createMiddleware($mw);
            }
        }
        
        try {
            $middleware = $this->autowire->resolve($middleware, $middlwareData);
        } catch (AutowireException $e) {
            throw new InvalidMiddlewareException($middleware, $e->getMessage());
        }
        
        if (! $middleware instanceof MiddlewareInterface) {
            throw new InvalidMiddlewareException($middleware);
        }
        
        return $middleware;
    }
    
    /**
     * Create a callable middleware.
     *
     * @param callable $middleware
     * @return MiddlewareInterface
     */        
    protected function createCallableMiddleware(callable $middleware): MiddlewareInterface
    {
        return new class ($middleware) implements MiddlewareInterface
        {
            public function __construct(
                private $middleware,
            ) {}

            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler
            ): ResponseInterface {
                return ($this->middleware)($request, $handler);
            }
        };
    }    
}