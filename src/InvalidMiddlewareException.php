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

use InvalidArgumentException;
use Throwable;

/**
 * InvalidMiddlewareException
 */
class InvalidMiddlewareException extends InvalidArgumentException
{
    /**
     * Create a new InvalidMiddlewareException
     *
     * @param mixed $middleware
     * @param string $message The message
     * @param int $code
     * @param null|Throwable $previous
     */
    public function __construct(
        protected mixed $middleware,
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        if ($message === '') {
            
            $middleware = $this->convertMiddlewareToString($middleware);
            
            $message = 'Middleware ['.$middleware.'] is invalid';    
        }
        
        parent::__construct($message, $code, $previous);
    }
    
    /**
     * Get the middleware.
     *
     * @return mixed
     */
    public function middleware(): mixed
    {
        return $this->middleware;
    }

    /**
     * Convert middleware to string.
     *
     * @param mixed $middleware
     * @return string
     */
    protected function convertMiddlewareToString(mixed $middleware): string
    {
        if (is_string($middleware)) {
            return $middleware;
        }
        
        if (is_object($middleware)) {
            return $middleware::class;
        }
        
        return '';
    }
}