<?php

namespace Whsv26\Tests;

use Fp\Collections\ArrayList;
use PHPUnit\Framework\TestCase;
use Whsv26\Mediator\Parsing\HandlerMapParser;
use Whsv26\Tests\Dummy\DummyCommandOne;
use Whsv26\Tests\Dummy\DummyCommandOneHandler;
use Whsv26\Tests\Dummy\DummyQueryOne;
use Whsv26\Tests\Dummy\DummyQueryOneHandler;
use Whsv26\Tests\Dummy\DummyQueryTwo;
use Whsv26\Tests\Dummy\DummyQueryTwoHandler;

class HandlerMapParserTest extends TestCase
{
    public function testParsing(): void
    {
        $parser = new HandlerMapParser();

        $parsed = [
            $parser->parseFile(__DIR__ . '/Dummy/DummyQueryOneHandler.php'),
            $parser->parseFile(__DIR__ . '/Dummy/DummyQueryTwoHandler.php'),
            $parser->parseFile(__DIR__ . '/Dummy/DummyCommandOneHandler.php'),
        ];

        $this->assertEquals(
            [
                [DummyQueryOne::class, DummyQueryOneHandler::class],
                [DummyQueryTwo::class, DummyQueryTwoHandler::class],
                [DummyCommandOne::class, DummyCommandOneHandler::class],
            ],
            ArrayList::collect($parsed)->filterMap(fn($pair) => $pair)->toArray()
        );
    }
}
