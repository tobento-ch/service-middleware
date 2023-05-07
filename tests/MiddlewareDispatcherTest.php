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
use Tobento\Service\Middleware\MiddlewareDispatcher;
use Tobento\Service\Middleware\MiddlewareDispatcherInterface;
use Tobento\Service\Middleware\AutowiringMiddlewareFactory;
use Tobento\Service\Middleware\FallbackHandler;
use Tobento\Service\Middleware\InvalidMiddlewareException;
use Tobento\Service\Container\Container;
use Tobento\Service\Middleware\Test\Mock\{
    MiddlewareWithParameters,
    MiddlewareWithBuildInParameter,
    MiddlewareWithoutParameters,
};
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
/**
 * MiddlewareDispatcherTest tests
 */
class MiddlewareDispatcherTest extends TestCase
{
    private function createMiddlewareDispatcher(): MiddlewareDispatcherInterface
    {
        // create response
        $response = (new Psr17Factory())->createResponse(404);

        // create middlware dispatcher
        return new MiddlewareDispatcher(
            new FallbackHandler($response),
            new AutowiringMiddlewareFactory(new Container())
        );
    }
    
    private function createServerRequest(): ServerRequestInterface
    {
        return (new Psr17Factory())->createServerRequest('GET', 'https://example.com');
    }
    
    public function testDispatcherIsInstanceofRequestHandlerInterface()
    {
        $this->assertInstanceof(
            RequestHandlerInterface::class,
            $this->createMiddlewareDispatcher()
        );
    }

    public function testNewMethod()
    {
        $md = $this->createMiddlewareDispatcher();
        $mdNew = $md->new();
        
        $this->assertInstanceof(MiddlewareDispatcher::class, $mdNew);
        $this->assertFalse($md === $mdNew);
    }
    
    public function testNewMethodKeepsAliases()
    {
        $md = $this->createMiddlewareDispatcher();
        $md->addAlias('name', MiddlewareWithParameters::class);
        
        $this->assertSame(
            [
                'name' => MiddlewareWithParameters::class,
            ],
            $md->new()->getAliases()
        );
    }
    
    public function testAddAliasMethod()
    {
        $md = $this->createMiddlewareDispatcher();
        
        $md->addAlias('name', MiddlewareWithParameters::class);
        
        $this->assertSame(
            [
                'name' => MiddlewareWithParameters::class,
            ],
            $md->getAliases()
        );
    }
    
    public function testAddAliasesMethod()
    {
        $md = $this->createMiddlewareDispatcher();
        
        $md->addAlias('alias', MiddlewareWithBuildInParameter::class);
        
        $md->addAliases([
            'name' => MiddlewareWithParameters::class,
        ]);
        
        $this->assertSame(
            [
                'alias' => MiddlewareWithBuildInParameter::class,
                'name' => MiddlewareWithParameters::class,
            ],
            $md->getAliases()
        );
    }
    
    public function testSetAliasesMethod()
    {
        $md = $this->createMiddlewareDispatcher();
        
        $md->addAlias('alias', MiddlewareWithBuildInParameter::class);
        
        $md->setAliases([
            'name' => MiddlewareWithParameters::class,
        ]);
        
        $this->assertSame(
            [
                'name' => MiddlewareWithParameters::class,
            ],
            $md->getAliases()
        );
    }

    public function testThatUnresolvableMiddlewareThrowsInvalidMiddlewareException()
    {
        $this->expectException(InvalidMiddlewareException::class);
        
        $md = $this->createMiddlewareDispatcher();
        
        $md->add(MiddlewareWithBuildInParameter::class);
        
        $response = $md->handle($this->createServerRequest());
    }
    
    public function testNoMiddlewareAddedCallsFallbackHandler()
    {
        $md = $this->createMiddlewareDispatcher();
        
        $response = $md->handle($this->createServerRequest());
        
        $this->assertSame(
            404,
            $response->getStatusCode()
        );
    }
    
    public function testNoMiddlewareCreatesResponseCallsFallbackHandler()
    {
        $md = $this->createMiddlewareDispatcher();

        $md->add(function($request, $handler): ResponseInterface {
            return $handler->handle($request);
        });
        
        $md->add(function($request, $handler): ResponseInterface {
            return $handler->handle($request);
        });        
        
        $response = $md->handle($this->createServerRequest());
        
        $this->assertSame(
            404,
            $response->getStatusCode()
        );
    }    

    public function testAddMethodWithCallableMiddleware()
    {
        $md = $this->createMiddlewareDispatcher();
        
        $md->add(function($request, $handler): ResponseInterface {

            $response = $handler->handle($request);
            $response->getBody()->write('Foo');

            return $response;
        });
        
        $response = $md->handle($this->createServerRequest());
        
        $this->assertSame(
            'Foo',
            (string)$response->getBody()
        );
    }
    
    public function testAddMethodWithBuildInParameters()
    {
        $md = $this->createMiddlewareDispatcher();
        
        $md->add([MiddlewareWithBuildInParameter::class, 'number' => 50]);
        
        $response = $md->handle($this->createServerRequest());
        
        $this->assertSame(
            'MiddlewareWithBuildInParameter',
            (string)$response->getBody()
        );
    }
    
    public function testAddMethodWithMultipleMiddlewares()
    {
        $md = $this->createMiddlewareDispatcher();
        
        $md->add(
            [MiddlewareWithBuildInParameter::class, 'number' => 50],
            MiddlewareWithoutParameters::class,
        );
        
        $response = $md->handle($this->createServerRequest());
        
        $this->assertSame(
            'MiddlewareWithoutParametersMiddlewareWithBuildInParameter',
            (string)$response->getBody()
        );
    }    

    public function testThatAliasIsUsedOnStringMiddleware()
    {
        $md = $this->createMiddlewareDispatcher();
        
        $md->addAlias('foo', MiddlewareWithoutParameters::class);
        
        $md->add('foo');
        
        $response = $md->handle($this->createServerRequest());
        
        $this->assertSame(
            'MiddlewareWithoutParameters',
            (string)$response->getBody()
        );
    } 
    
    public function testThatAliasIsUsedOnArrayMiddleware()
    {
        $md = $this->createMiddlewareDispatcher();
        
        $md->addAlias('foo', MiddlewareWithBuildInParameter::class);
        
        $md->add(['foo', 'number' => 50]);
        
        $response = $md->handle($this->createServerRequest());
        
        $this->assertSame(
            'MiddlewareWithBuildInParameter',
            (string)$response->getBody()
        );
    }    
    
    public function testMiddlewareIsProcessedInRightOrder()
    {
        $md = $this->createMiddlewareDispatcher();
        
        $md->add([MiddlewareWithBuildInParameter::class, 'number' => 50]);
        $md->add(MiddlewareWithoutParameters::class);
        
        $response = $md->handle($this->createServerRequest());
        
        $this->assertSame(
            'MiddlewareWithoutParametersMiddlewareWithBuildInParameter',
            (string)$response->getBody()
        );
    }
    
    public function testMiddlewareIsProcessedInRightOrderWithPriority()
    {
        $md = $this->createMiddlewareDispatcher();
        
        $md->add([MiddlewareWithBuildInParameter::class, 'number' => 50], priority: 10);
        $md->add(MiddlewareWithoutParameters::class, priority: 20);
        
        $response = $md->handle($this->createServerRequest());
        
        $this->assertSame(
            'MiddlewareWithBuildInParameterMiddlewareWithoutParameters',
            (string)$response->getBody()
        );
    }
    
    public function testThatCanDispatchMoreThanOnce()
    {
        $md = $this->createMiddlewareDispatcher();
        
        $md->add([MiddlewareWithBuildInParameter::class, 'number' => 50]);
        $md->add(MiddlewareWithoutParameters::class);
        
        $response = $md->handle($this->createServerRequest());
        
        $this->assertSame(
            'MiddlewareWithoutParametersMiddlewareWithBuildInParameter',
            (string)$response->getBody()
        );
        
        $response = $md->handle($this->createServerRequest());
        
        $this->assertSame(
            'MiddlewareWithoutParametersMiddlewareWithBuildInParameter',
            (string)$response->getBody()
        );
    }
    
    public function testThatNextMiddlewaresAreNotProcessedIfOneCreatesResponse()
    {
        $md = $this->createMiddlewareDispatcher();
        
        $md->add([MiddlewareWithBuildInParameter::class, 'number' => 50]);
        
        $md->add(function($request, $handler): ResponseInterface {

            return (new Psr17Factory())->createResponse(200);
        });
        
        $md->add(MiddlewareWithoutParameters::class);
        
        $response = $md->handle($this->createServerRequest());
        
        $this->assertSame(
            'MiddlewareWithBuildInParameter',
            (string)$response->getBody()
        );
    }    
}