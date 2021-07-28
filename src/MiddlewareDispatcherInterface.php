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

use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

/**
 * MiddlewareDispatcherInterface
 */
interface MiddlewareDispatcherInterface extends RequestHandlerInterface
{
    /**
     * Add a middleware or multiple.
     *
     * @param mixed $middleware Any middleware.
     * @return static $this
     */    
    public function add(mixed ...$middleware): static;

    /**
     * Add a middleware with alias.
     *
     * @param string $alias An alias.
     * @param string $middleware The class Namespace\Middleware::class
     * @return static $this
     */    
    public function addAlias(string $alias, string $middleware): static;

    /**
     * Add multiple middleware with alias.
     *
     * @param array<string, string> $aliases ['alias' => Namespace\Middleware::class]
     * @return static $this
     */    
    public function addAliases(array $aliases): static;

    /**
     * Sets the middleware aliases.
     *
     * @param array<string, string> $aliases ['alias' => Namespace\Middleware::class]
     * @return static $this
     */    
    public function setAliases(array $aliases): static;
    
    /**
     * Gets the middleware aliases.
     *
     * @return array<string, string> ['alias' => Namespace\Middleware::class]
     */    
    public function getAliases(): array;
}