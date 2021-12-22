<?php

declare(strict_types=1);

namespace Whsv26\Tests\Dummy;

use Closure;
use Whsv26\Mediator\Contract\CommandMiddlewareInterface;
use Whsv26\Mediator\Contract\MiddlewareInterface;

class DummyCommandMiddleware implements CommandMiddlewareInterface
{
    public function __construct(
        private DummyService $dummyService
    ) { }

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
        echo $this->dummyService->x . self::class . ' before' . PHP_EOL;

        $result = $next($request);

        echo $this->dummyService->x . self::class . ' after' . PHP_EOL;

        return $result;
    }
}
