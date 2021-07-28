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
    private Autowire $autowire;
    
    /**
     * Create a new MiddlewareDispatcher.
     *
     * @param ContainerInterface $container
     */    
    public function __construct(
        protected ContainerInterface $container
    ) {
        $this->autowire = new Autowire($this->container);
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
        
        if (!is_string($middleware))
        {
            throw new InvalidMiddlewareException($middleware);
        }    
        
        try {
            $middleware = $this->autowire->resolve($middleware, $middlwareData);
        } catch (AutowireException $e) {
            throw new InvalidMiddlewareException($middleware, $e->getMessage());
        }
        
        if (! $middleware instanceof MiddlewareInterface)
        {
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