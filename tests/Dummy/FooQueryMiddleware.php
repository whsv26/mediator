<?php

declare(strict_types=1);

namespace Whsv26\Tests\Dummy;

use Closure;
use Whsv26\Mediator\Contract\QueryInterface;
use Whsv26\Mediator\Contract\QueryMiddlewareInterface;

/**
 * @internal
 */
class FooQueryMiddleware implements QueryMiddlewareInterface
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
        echo '<foo>';

        $result = $next($query);

        echo '</foo>';

        return $result;
    }
}
