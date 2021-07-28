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

namespace Tobento\Service\Middleware\Test\Mock;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * MiddlewareWithBuildInParameter
 */
class MiddlewareWithBuildInParameter implements MiddlewareInterface
{
    /**
     * Create a new MiddlewareWithBuildInParameter
     *
     * @param Foo $foo
     * @param int $number
     */
    public function __construct(
        protected Foo $foo,
        protected int $number,
    ) {}
    
    /**
     * Process an incoming server request.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $response = $handler->handle($request);
        $response->getBody()->write('MiddlewareWithBuildInParameter');
        return $response;
    }
}