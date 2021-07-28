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

namespace Tobento\Service\Middleware\Test;

use PHPUnit\Framework\TestCase;
use Tobento\Service\Middleware\AutowiringMiddlewareFactory;
use Tobento\Service\Middleware\InvalidMiddlewareException;
use Tobento\Service\Container\Container;
use Tobento\Service\Middleware\Test\Mock\{
    MiddlewareWithParameters,
    MiddlewareWithBuildInParameter,
    MiddlewareWithoutParameters,
};
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Server\MiddlewareInterface;

/**
 * AutowiringMiddlewareFactoryTest tests
 */
class AutowiringMiddlewareFactoryTest extends TestCase
{
    private function createFactory(): AutowiringMiddlewareFactory
    {
        return new AutowiringMiddlewareFactory(new Container());
    }
    
    public function testCreateFromString()
    {
        $factory = $this->createFactory();
        
        $this->assertInstanceof(
            MiddlewareInterface::class,
            $factory->createMiddleware(MiddlewareWithoutParameters::class)
        );
    }
    
    public function testCreateFromArray()
    {
        $factory = $this->createFactory();
        
        $this->assertInstanceof(
            MiddlewareInterface::class,
            $factory->createMiddleware([MiddlewareWithoutParameters::class])
        );
    }
    
    public function testCreateFromArrayWithParameters()
    {
        $factory = $this->createFactory();
        
        $this->assertInstanceof(
            MiddlewareInterface::class,
            $factory->createMiddleware([MiddlewareWithBuildInParameter::class, 'number' => 20])
        );
    }
    
    public function testCreateFromCallable()
    {
        $factory = $this->createFactory();
        
        $this->assertInstanceof(
            MiddlewareInterface::class,
            $factory->createMiddleware(function($request, $handler) {
                return $handler->handle($request);
            }));
    }
    
    public function testThatUnresolvableMiddlewareThrowsInvalidMiddlewareException()
    {
        $this->expectException(InvalidMiddlewareException::class);
        
        $this->createFactory()->createMiddleware('Foo');
    }    
}