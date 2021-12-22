<?php

declare(strict_types=1);

namespace Whsv26\Tests\Dummy;

use Closure;
use Whsv26\Mediator\Contract\MiddlewareInterface;
use Whsv26\Mediator\Contract\RequestInterface;

class DummyMiddlewareOne implements MiddlewareInterface
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
        echo self::class . ' before' . PHP_EOL;

        $result = $next($request);

        echo self::class . ' after' . PHP_EOL;

        return $result;
    }
}
