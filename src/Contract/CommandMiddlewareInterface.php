<?php

declare(strict_types=1);

namespace Whsv26\Mediator\Contract;

use Closure;

interface CommandMiddlewareInterface
{
    public const TAG = 'mediator.command_middleware';

    /**
     * @template TResponse
     * @template TCommand of CommandInterface<TResponse>
     *
     * @param TCommand $command
     * @param Closure(TCommand): TResponse $next
     *
     * @return TResponse
     */
    public function handle(CommandInterface $command, Closure $next): mixed;
}
