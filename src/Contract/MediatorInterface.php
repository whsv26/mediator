<?php

namespace Whsv26\Mediator\Contract;

interface MediatorInterface
{
    /**
     * @template TResponse
     * @param RequestInterface<TResponse> $request
     * @return TResponse
     */
    public function send(RequestInterface $request): mixed;

    /**
     * @template TResponse
     * @param CommandInterface<TResponse> $command
     * @return TResponse
     */
    public function sendCommand(CommandInterface $command): mixed;

    /**
     * @template TResponse
     * @param QueryInterface<TResponse> $query
     * @return TResponse
     */
    public function sendQuery(QueryInterface $query): mixed;

    /**
     * @param iterable<object> $events
     * @return void
     */
    public function publish(iterable $events): void;
}
