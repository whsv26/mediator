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
}
