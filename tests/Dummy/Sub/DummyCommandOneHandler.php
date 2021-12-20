<?php

namespace Whsv26\Tests\Dummy\Sub;

use Fp\Functional\Either\Either;
use Whsv26\Mediator\Contract\CommandHandlerInterface;
use Whsv26\Tests\Dummy\DummyCommandOne;

/**
 * @implements CommandHandlerInterface<Either<false, true>, DummyCommandOne>
 */
class DummyCommandOneHandler implements CommandHandlerInterface
{
    /**
     * @param DummyCommandOne $command
     * @return Either<false, true>
     */
    public function handle(mixed $command): Either
    {
        return Either::cond(rand() === 0, true, false);
    }
}