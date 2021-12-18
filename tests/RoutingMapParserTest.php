<?php

use Dummy\DummyQuery;
use Dummy\DummyQueryHandler;
use PHPUnit\Framework\TestCase;
use Whsv26\Mediator\Parsing\RoutingMapParser;

class RoutingMapParserTest extends TestCase
{
    public function testParsing(): void
    {
        $parser = new RoutingMapParser();
        $routingMap = $parser->parseDirRecursive(__DIR__ . '/Dummy');

        $this->assertEquals([DummyQuery::class => DummyQueryHandler::class], $routingMap);
    }
}