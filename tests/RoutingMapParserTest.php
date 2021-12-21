<?php

namespace Whsv26\Tests;

use Whsv26\Tests\Dummy\DummyCommandOne;
use Whsv26\Tests\Dummy\DummyQueryTwoHandler;
use Whsv26\Tests\Dummy\DummyQueryOne;
use Whsv26\Tests\Dummy\Sub\DummyCommandOneHandler;
use Whsv26\Tests\Dummy\Sub\DummyQueryOneHandler;
use Whsv26\Tests\Dummy\Sub\DummyQueryTwo;
use PHPUnit\Framework\TestCase;
use Whsv26\Mediator\Parsing\RoutingMapParser;

class RoutingMapParserTest extends TestCase
{
    public function testParsing(): void
    {
        $parser = new RoutingMapParser();

        $this->assertEquals(
            [
                DummyQueryOne::class => DummyQueryOneHandler::class,
                DummyQueryTwo::class => DummyQueryTwoHandler::class,
                DummyCommandOne::class => DummyCommandOneHandler::class,
            ],
            $parser->parseDirRecursive(__DIR__ . '/Dummy')
        );

        $this->assertEquals(
            [
                DummyQueryOne::class => DummyQueryOneHandler::class,
                DummyQueryTwo::class => DummyQueryTwoHandler::class,
                DummyCommandOne::class => DummyCommandOneHandler::class,
            ],
            $parser->parseDirRecursive(__DIR__ . '/../')
        );
    }
}
