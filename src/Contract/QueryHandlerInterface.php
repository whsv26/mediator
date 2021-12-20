<?php

declare(strict_types=1);

namespace Whsv26\Mediator\Contract;

/**
 * @template TResponse
 * @template TQuery of QueryInterface<TResponse>
 */
interface QueryHandlerInterface
{
    /**
     * @param TQuery $query
     * @return TResponse
     */
    public function handle(mixed $query): mixed;
}
