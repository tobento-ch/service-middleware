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

/**
 * FallbackHandler
 */
class FallbackHandler implements RequestHandlerInterface
{    
    /**
     * Create a new FallbackHandler.
     *
     * @param ResponseInterface $response
     */    
    public function __construct(
        protected ResponseInterface $response
    ) {}
    
    /**
     * Handle the request.
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */    
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->response;
    }
}