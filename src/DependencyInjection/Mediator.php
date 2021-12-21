<?php

namespace Whsv26\Mediator\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Whsv26\Mediator\Contract\MediatorInterface;
use Whsv26\Mediator\Contract\RequestInterface;
use Whsv26\Mediator\Exception\RequestHandlerNotFoundException;

/**
 * @psalm-type Request = class-string
 * @psalm-type RequestHandler = class-string
 */
class Mediator implements MediatorInterface
{
    public function __construct(
        private ServiceLocator $locator
    ) { }

    /**
     * @template TResponse
     * @param RequestInterface<TResponse> $request
     * @return TResponse
     *
     * @psalm-suppress MixedMethodCall, MixedAssignment
     */
    public function send(RequestInterface $request): mixed
    {
        $handler = $this->locator->get($request::class);

        if (empty($handler)) {
            throw new RequestHandlerNotFoundException();
        }

        /**
         * @var TResponse
         */
        return $handler->handle($request);
    }
}
