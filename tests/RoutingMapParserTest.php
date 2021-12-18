<?php

use Dummy\DummyQueryHandlerTwo;
use Dummy\DummyQueryOne;
use Dummy\Sub\DummyQueryHandlerOne;
use Dummy\Sub\DummyQueryTwo;
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
        ];

        $this->assertEquals($expected, $routingMap);
    }
}