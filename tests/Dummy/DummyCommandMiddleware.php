<?php

declare(strict_types=1);

namespace Whsv26\Tests\Dummy;

use Closure;
use Whsv26\Mediator\Contract\CommandInterface;
use Whsv26\Mediator\Contract\CommandMiddlewareInterface;

/**
 * @internal
 */
class DummyCommandMiddleware implements CommandMiddlewareInterface
{
    public function __construct(
        private DummyService $dummyService
    ) { }

    /**
     * @template TResponse
     * @template TCommand of CommandInterface<TResponse>
     *
     * @param TCommand $command
     * @param Closure(TCommand): TResponse $next
     *
     * @return TResponse
     */
    public function handle(CommandInterface $command, Closure $next): mixed
    {
        echo $this->dummyService->x . self::class . ' before' . PHP_EOL;

        $result = $next($command);

        echo $this->dummyService->x . self::class . ' after' . PHP_EOL;

        return $result;
    }
}
