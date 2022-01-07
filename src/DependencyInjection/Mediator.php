<?php

namespace Whsv26\Mediator\DependencyInjection;

use Fp\Functional\Option\Option;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Whsv26\Mediator\Contract\CommandInterface;
use Whsv26\Mediator\Contract\MediatorInterface;
use Whsv26\Mediator\Contract\QueryInterface;
use Whsv26\Mediator\Contract\RequestInterface;

/**
 * @internal
 */
final class Mediator implements MediatorInterface
{
    public function __construct(
        private MessageBusInterface $queryBus,
        private MessageBusInterface $commandBus,
        private MessageBusInterface $eventBus,
    ) { }

    /**
     * @template TResponse
     * @param RequestInterface<TResponse> $request
     * @return TResponse
     */
    public function send(RequestInterface $request): mixed
    {
        /** @var TResponse */
        return match (true) {
            $request instanceof CommandInterface => $this->sendCommand($request),
            $request instanceof QueryInterface => $this->sendQuery($request),
        };
    }

    /**
     * @template TResponse
     * @param CommandInterface<TResponse> $command
     * @return TResponse
     */
    public function sendCommand(CommandInterface $command): mixed
    {
        $envelope = $this->commandBus->dispatch($command);
        $stamp = $envelope->last(HandledStamp::class);

        /** @var TResponse */
        return Option::fromNullable($stamp)
            ->filterOf(HandledStamp::class)
            ->map(fn(HandledStamp $stamp): mixed => $stamp->getResult())
            ->getUnsafe();
    }

    /**
     * @template TResponse
     * @param QueryInterface<TResponse> $query
     * @return TResponse
     */
    public function sendQuery(QueryInterface $query): mixed
    {
        $envelope = $this->queryBus->dispatch($query);
        $stamp = $envelope->last(HandledStamp::class);

        /** @var TResponse */
        return Option::fromNullable($stamp)
            ->filterOf(HandledStamp::class)
            ->map(fn(HandledStamp $stamp): mixed => $stamp->getResult())
            ->getUnsafe();
    }

    /**
     * @inheritDoc
     */
    public function publish(iterable $events): void
    {
        foreach ($events as $event) {
            $this->eventBus->dispatch($event);
        }
    }
}
