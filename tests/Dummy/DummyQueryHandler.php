<?php

namespace Dummy;

use Whsv26\Mediator\Contract\QueryHandlerInterface;

use Fp\Functional\Option\Option;


/**
 * @implements QueryHandlerInterface<bool, DummyQuery>
 */
class DummyQueryHandler implements QueryHandlerInterface
{
    /**
     * @param DummyQuery $query
     * @return bool
     */
    public function handle(mixed $query): bool
    {
        throw new \RuntimeException('???');
    }
}