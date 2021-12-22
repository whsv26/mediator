<?php

declare(strict_types=1);

namespace Whsv26\Tests\Dummy;

use Closure;
use Whsv26\Mediator\Contract\QueryMiddlewareInterface;
use Whsv26\Mediator\Contract\RequestInterface;

class DummyQueryMiddleware implements QueryMiddlewareInterface
{
    /**
     * @template TResponse
     * @template TRequest of RequestInterface<TResponse>
     *
     * @param TRequest $request
     * @param Closure(TRequest): TResponse $next
     *
     * @return TResponse
     */
    public function handle(mixed $request, Closure $next): mixed
    {
        echo 'before_query_middleware ';

        $result = $next($request);

        echo 'after_query_middleware';

        return $result;
    }
}
