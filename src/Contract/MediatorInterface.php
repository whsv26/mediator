<?php

namespace Whsv26\Mediator\Contract;

use Whsv26\Mediator\Handled;

interface MediatorInterface
{
    /**
     * @template TResponse
     * @psalm-param RequestInterface<TResponse> $request
     * @return TResponse
     */
    public function send(RequestInterface $request): mixed;
}