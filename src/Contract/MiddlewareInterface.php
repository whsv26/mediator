<?php

declare(strict_types=1);

namespace Whsv26\Mediator\Contract;

use Closure;

interface MiddlewareInterface
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
    public function handle(mixed $request, Closure $next): mixed;
}
