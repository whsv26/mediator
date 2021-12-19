<?php

namespace Whsv26\Tests\Dummy;

use Whsv26\Tests\Dummy\Sub\DummyQueryTwo;
use Whsv26\Mediator\Contract\QueryHandlerInterface;

/**
 * @implements QueryHandlerInterface<int, DummyQueryTwo>
 */
class DummyQueryHandlerTwo implements QueryHandlerInterface
{
    /**
     * @param DummyQueryTwo $query
     * @return bool
     */
    public function handle(mixed $query): bool
    {
        throw new \RuntimeException('???');
    }
}