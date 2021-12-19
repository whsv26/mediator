<?php

namespace Whsv26\Tests\Dummy\Sub;

use Whsv26\Tests\Dummy\DummyQueryOne;
use Whsv26\Mediator\Contract\QueryHandlerInterface;

/**
 * @implements QueryHandlerInterface<bool, DummyQueryOne>
 */
class DummyQueryHandlerOne implements QueryHandlerInterface
{
    /**
     * @param DummyQueryOne $query
     * @return bool
     */
    public function handle(mixed $query): bool
    {
        throw new \RuntimeException('???');
    }
}