<?php

namespace Whsv26\Tests\Dummy;

use Fp\Functional\Either\Either;
use Whsv26\Mediator\Contract\CommandHandlerInterface;

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
        return Either::right(true);
    }
}
