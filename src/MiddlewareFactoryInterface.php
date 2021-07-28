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