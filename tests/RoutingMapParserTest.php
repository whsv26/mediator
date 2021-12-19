<?php

namespace Whsv26\Tests;

use Whsv26\Tests\Dummy\DummyCommandOne;
use Whsv26\Tests\Dummy\DummyQueryHandlerTwo;
use Whsv26\Tests\Dummy\DummyQueryOne;
use Whsv26\Tests\Dummy\Sub\DummyCommandHandlerOne;
use Whsv26\Tests\Dummy\Sub\DummyQueryHandlerOne;
use Whsv26\Tests\Dummy\Sub\DummyQueryTwo;
use PHPUnit\Framework\TestCase;
use Whsv26\Mediator\Parsing\RoutingMapParser;

class RoutingMapParserTest extends TestCase
{
    public function testParsing(): void
    {
        $parser = new RoutingMapParser();
        $routingMap = $parser->parseDirRecursive(__DIR__ . '/Dummy');

        $expected = [
            DummyQueryOne::class => DummyQueryHandlerOne::class,
            DummyQueryTwo::class => DummyQueryHandlerTwo::class,
            DummyCommandOne::class => DummyCommandHandlerOne::class,
        ];

        $this->assertEquals($expected, $routingMap);
    }
}