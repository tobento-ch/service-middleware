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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;
use RuntimeException;
use Throwable;

/**
 * MiddlewareDispatcher
 */
class MiddlewareDispatcher implements MiddlewareDispatcherInterface
{    
    /**
     * @var array The registered middleware.
     */    
    protected array $middleware = [];

    /**
     * @var array The registered middleware aliases.
     */    
    protected array $middlewareAliases = [];    

    /**
     * Create a new MiddlewareDispatcher.
     *
     * @param RequestHandlerInterface $fallbackHandler
     * @param MiddlewareFactoryInterface $middlewareFactory
     */    
    public function __construct(
        protected RequestHandlerInterface $fallbackHandler,
        protected MiddlewareFactoryInterface $middlewareFactory
    ) {}
    
    /**
     * Returns a new instance.
     *
     * @return static
     */
    public function new(): static
    {
        $new = clone $this;
        $new->middleware = [];
        return $new;
    }
    
    /**
     * Add a middleware or multiple.
     *
     * @param mixed $middleware Any middleware.
     * @return static $this
     */    
    public function add(mixed ...$middleware): static
    {
        $priority = (int)($middleware['priority'] ?? 0);
        unset($middleware['priority']);
    
        foreach($middleware as $mw)
        {            
            if ($mw instanceof MiddlewareInterface)
            {
                $this->middleware[$priority][$mw::class] = $mw;
            }

            if (is_string($mw))
            {
                $mw = $this->middlewareAliases[$mw] ?? $mw;
                $this->middleware[$priority][$mw] = $mw;
            }

            if (is_callable($mw))
            {
                $this->middleware[$priority][] = $mw;
            }

            if (is_array($mw) && isset($mw[0]) && is_string($mw[0]))
            {
                $mw[0] = $this->middlewareAliases[$mw[0]] ?? $mw[0];
                $this->middleware[$priority][$mw[0]] = $mw;
            }
        }
        
        return $this;
    }

    /**
     * Add a middleware with alias.
     *
     * @param string $alias An alias.
     * @param string $middleware The class Namespace\Middleware::class
     * @return static $this
     */    
    public function addAlias(string $alias, string $middleware): static
    {
        $this->middlewareAliases[$alias] = $middleware;
        
        return $this;
    }

    /**
     * Add multiple middleware with alias.
     *
     * @param array<string, string> $aliases ['alias' => Namespace\Middleware::class]
     * @return static $this
     */    
    public function addAliases(array $aliases): static
    {
        foreach($aliases as $alias => $mw)
        {
            $this->addAlias($alias, $mw);
        }
        
        return $this;
    }

    /**
     * Sets the middleware aliases.
     *
     * @param array<string, string> $aliases ['alias' => Namespace\Middleware::class]
     * @return static $this
     */    
    public function setAliases(array $aliases): static
    {
        $this->middlewareAliases = $aliases;
        
        return $this;
    }
    
    /**
     * Gets the middleware aliases.
     *
     * @return array<string, string> ['alias' => Namespace\Middleware::class]
     */    
    public function getAliases(): array
    {
        return $this->middlewareAliases;
    }    
            
    /**
     * Dispatches the middleware stack.
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */    
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->dispatching($request);
    }

    /**
     * Dispatches the middleware stack.
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @psalm-suppress UnusedVariable
     */    
    protected function dispatching(ServerRequestInterface $request): ResponseInterface
    {            
        // Sort by its priority.
        krsort($this->middleware);
        
        // Merge into one array depth.
        if (!empty($this->middleware))
        {
            $this->middleware = call_user_func_array('array_merge', $this->middleware);
        }
        
        // process
        $requestHandler = new CallableRequestHandler(function(ServerRequestInterface $request) use (&$requestHandler) {

            $middleware = array_shift($this->middleware);

            if ($middleware === null) {
                return $this->fallbackHandler->handle($request);
            }
            
            $middleware = $this->middlewareFactory->createMiddleware($middleware);
            
            // process the middleware.
            return $middleware->process($request, $requestHandler);    
        });
        
        return $requestHandler->handle($request);
    }   
}