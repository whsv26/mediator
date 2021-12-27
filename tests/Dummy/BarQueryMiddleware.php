<?php

declare(strict_types=1);

namespace Whsv26\Tests\Dummy;

use Closure;
use Whsv26\Mediator\Contract\QueryInterface;
use Whsv26\Mediator\Contract\QueryMiddlewareInterface;

class BarQueryMiddleware implements QueryMiddlewareInterface
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
    public function handle(QueryInterface $query, Closure $next): mixed
    {
        echo '<bar>';

        $result = $next($query);

        echo '</bar>';

        return $result;
    }
}
