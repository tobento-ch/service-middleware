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

use Psr\Http\Server\MiddlewareInterface;

/**
 * MiddlewareFactoryInterface
 */
interface MiddlewareFactoryInterface
{
    /**
     * Add a middleware to replace.
     *
     * @param string $middleware
     * @param mixed $withMiddleware
     * @return static $this
     */
    public function replaceMiddleware(string $middleware, mixed $withMiddleware): static;
    
    /**
     * Returns the middlewares to replace.
     *
     * @return array
     */
    public function getReplaceMiddlewares(): array;
    
    /**
     * Create middleware.
     *
     * @param mixed $middleware
     *
     * @throws InvalidMiddlewareException
     *
     * @return MiddlewareInterface
     */    
    public function createMiddleware(mixed $middleware): MiddlewareInterface;
}