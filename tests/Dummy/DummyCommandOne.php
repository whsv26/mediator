<?php

namespace Whsv26\Tests\Dummy;

use Fp\Functional\Either\Either;
use Whsv26\Mediator\Contract\CommandInterface;

/**
 * @implements CommandInterface<Either<false, true>>
 */
class DummyCommandOne implements CommandInterface
{

}