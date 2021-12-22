<?php

namespace Whsv26\Mediator\DependencyInjection;

use Fp\Collections\Seq;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Whsv26\Mediator\Contract\CommandHandlerInterface;
use Whsv26\Mediator\Contract\MediatorInterface;
use Whsv26\Mediator\Contract\MiddlewareInterface;
use Whsv26\Mediator\Contract\QueryHandlerInterface;
use Whsv26\Mediator\Contract\RequestInterface;
use Whsv26\Mediator\Exception\RequestHandlerNotFoundException;

/**
 * @psalm-type Request = class-string
 * @psalm-type RequestHandler = class-string
 */
class Mediator implements MediatorInterface
{
    /**
     * @param Seq<MiddlewareInterface> $commandPipes
     * @param Seq<MiddlewareInterface> $queryPipes
     */
    public function __construct(
        private ServiceLocator $locator,
        private Seq $commandPipes,
        private Seq $queryPipes,
    ) { }

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
