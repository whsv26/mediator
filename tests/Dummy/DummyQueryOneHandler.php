<?php

namespace Whsv26\Tests\Dummy;

use Whsv26\Mediator\Contract\QueryHandlerInterface;

/**
 * @implements QueryHandlerInterface<Foo, DummyQueryOne>
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
