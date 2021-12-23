<?php

declare(strict_types=1);

namespace Whsv26\Mediator\Contract;

/**
 * @template TResponse
 * @template TQuery of QueryInterface<TResponse>
 */
interface QueryHandlerInterface
{
    public const TAG = 'mediator.query_handler';

    /**
     * @param TQuery $query
     * @return TResponse
     */
    public function handle(mixed $query): mixed;
}
