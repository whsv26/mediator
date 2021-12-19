<?php

namespace Whsv26\Tests\Dummy\Sub;

use Whsv26\Mediator\Contract\CommandHandlerInterface;
use Whsv26\Tests\Dummy\DummyCommandOne;

/**
 * @implements CommandHandlerInterface<bool, DummyCommandOne>
 */
class DummyCommandHandlerOne implements CommandHandlerInterface
{
    /**
     * @param DummyCommandOne $query
     * @return bool
     */
    public function handle(mixed $query): bool
    {
        throw new \RuntimeException('???');
    }
}