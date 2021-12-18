<?php

declare(strict_types=1);

namespace Whsv26\Mediator\Contract;

/**
 * @template TResult
 * @template TCommand of CommandInterface<TResult>
 */
interface CommandHandlerInterface
{
    /**
     * @param TCommand $command
     * @return TResult
     */
    public function handle(mixed $command): mixed;
}
