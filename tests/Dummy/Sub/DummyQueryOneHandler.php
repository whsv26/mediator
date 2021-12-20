<?php

namespace Whsv26\Tests\Dummy\Sub;

use Whsv26\Tests\Dummy\DummyQueryOne;
use Whsv26\Mediator\Contract\QueryHandlerInterface;
use Whsv26\Tests\Dummy\Foo;

/**
 * @implements QueryHandlerInterface<bool, DummyQueryOne>
 */
class DummyQueryOneHandler implements QueryHandlerInterface
{
    /**
     * @param DummyQueryOne $query
     * @return Foo
     */
    public function handle(mixed $query): Foo
    {
        return new Foo(1);
    }
}