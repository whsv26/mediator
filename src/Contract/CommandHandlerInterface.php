<?php

declare(strict_types=1);

namespace Whsv26\Mediator\Contract;

/**
 * @template TResponse
 * @template TCommand of CommandInterface<TResponse>
 */
interface CommandHandlerInterface
{
    public const TAG = 'mediator.command_handler';

    /**
     * @param TCommand $command
     * @return TResponse
     */
    public function handle(mixed $command): mixed;
}
