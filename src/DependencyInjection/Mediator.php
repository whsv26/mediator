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
 * @internal
 * @psalm-type Request = class-string
 * @psalm-type RequestHandler = class-string
 * @psalm-type RequestHandlerInterface = (CommandHandlerInterface|QueryHandlerInterface)
 */
final class Mediator implements MediatorInterface
{
    /**
     * @var Seq<CommandMiddlewareInterface>
     */
    private Seq $commandPipes;

    /**
     * @var Seq<QueryMiddlewareInterface>
     */
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
        $this->commandPipes = ArrayList::collect($commandPipes)->reverse();
        $this->queryPipes = ArrayList::collect($queryPipes)->reverse();
    }

    /**
     * @template TResponse
     * @param RequestInterface<TResponse> $request
     * @return TResponse
     */
    public function send(RequestInterface $request): mixed
    {
        /**
         * @var null|RequestHandlerInterface $handler
         */
        $handler = $this->locator->get($request::class);

        if (empty($handler)) {
            throw new RequestHandlerNotFoundException();
        }

        $pipes = match (true) {
            $handler instanceof CommandHandlerInterface => $this->commandPipes,
            $handler instanceof QueryHandlerInterface => $this->queryPipes,
        };

        /**
         * @psalm-suppress MixedArgument
         */
        $pipeline = $pipes->fold(
            fn($req) => $handler->handle($req),
            fn($acc, $cur) => fn($req) => $cur->handle($req, $acc)
        );

        /** @var TResponse */
        return $pipeline($request);
    }
}
