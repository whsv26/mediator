<?php

declare(strict_types=1);

namespace Whsv26\Mediator\Contract;

/**
 * @template TResult
 * @template TQuery of QueryInterface<TResult>
 */
interface QueryHandlerInterface
{
    /**
     * @param TQuery $query
     * @return TResult
     */
    public function handle(mixed $query): mixed;
}
