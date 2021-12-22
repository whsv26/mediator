<?php

namespace Whsv26\Mediator\DependencyInjection;

use Fp\Collections\ArrayList;
use Fp\Collections\Seq;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Whsv26\Mediator\Contract\CommandHandlerInterface;
use Whsv26\Mediator\Contract\CommandMiddlewareInterface;
use Whsv26\Mediator\Contract\MediatorInterface;
use Whsv26\Mediator\Contract\QueryHandlerInterface;
use Whsv26\Mediator\Contract\QueryMiddlewareInterface;
use Whsv26\Mediator\Contract\RequestInterface;
use Whsv26\Mediator\Exception\RequestHandlerNotFoundException;

/**
 * @psalm-type Request = class-string
 * @psalm-type RequestHandler = class-string
 */
class Mediator implements MediatorInterface
{
    private Seq $commandPipes;
    private Seq $queryPipes;

    /**
     * @param iterable<CommandMiddlewareInterface> $commandPipes
     * @param iterable<QueryMiddlewareInterface> $queryPipes
     */
    public function __construct(
        private ServiceLocator $locator,
        iterable $commandPipes,
        iterable $queryPipes,
    ) {
        $this->commandPipes = ArrayList::collect($commandPipes);
        $this->queryPipes = ArrayList::collect($queryPipes);
    }

    /**
     * @template TResponse
     * @param RequestInterface<TResponse> $request
     * @return TResponse
     * @psalm-suppress all
     */
    public function send(RequestInterface $request): mixed
    {
        $handler = $this->locator->get($request::class);

        if (empty($handler)) {
            throw new RequestHandlerNotFoundException();
        }

        $pipes = match (true) {
            $handler instanceof CommandHandlerInterface => $this->commandPipes,
            $handler instanceof QueryHandlerInterface => $this->queryPipes,
        };

        $pipeline = $pipes->reverse()->fold(
            fn($req) => $handler->handle($req),
            fn($acc, $cur) => fn($req) => $cur->handle($req, $acc)
        );

        return $pipeline($request);
    }
}
