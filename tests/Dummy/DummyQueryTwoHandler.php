<?php

namespace Whsv26\Tests\Dummy;

use Whsv26\Tests\Dummy\Sub\DummyQueryTwo;
use Whsv26\Mediator\Contract\QueryHandlerInterface;

/**
 * @implements QueryHandlerInterface<int, DummyQueryTwo>
 */
class DummyQueryTwoHandler implements QueryHandlerInterface
{
    /**
     * @param DummyQueryTwo $query
     * @return int
     */
    public function handle(mixed $query): int
    {
        return 1;
    }
}