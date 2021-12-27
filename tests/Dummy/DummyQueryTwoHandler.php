<?php

namespace Whsv26\Tests\Dummy;

use Whsv26\Mediator\Contract\QueryHandlerInterface;

/**
 * @internal
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
        return $this($query);
    }

    public function __invoke(DummyQueryTwo $query): int
    {
        return 1;
    }
}
