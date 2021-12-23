<?php

declare(strict_types=1);

namespace Whsv26\Mediator\Contract;

use Closure;

interface QueryMiddlewareInterface
{
    /**
     * @template TResponse
     * @template TQuery of QueryInterface<TResponse>
     *
     * @param TQuery $query
     * @param Closure(TQuery): TResponse $next
     *
     * @return TResponse
     */
    public function handle(QueryInterface $query, Closure $next): mixed;
}