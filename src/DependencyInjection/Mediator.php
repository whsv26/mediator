<?php

namespace Whsv26\Mediator\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Whsv26\Mediator\Contract\MediatorInterface;
use Whsv26\Mediator\Contract\RequestInterface;
use Whsv26\Mediator\Exception\RequestHandlerNotFoundException;
use Whsv26\Mediator\Exception\UnroutedCommandException;
use Whsv26\Mediator\Exception\UnroutedQueryException;
use Whsv26\Mediator\Exception\UnroutedRequestException;

/**
 * @psalm-type Request = class-string
 * @psalm-type RequestHandler = class-string
 */
class Mediator implements MediatorInterface
{
    /**
     * @param array<Request, RequestHandler> $routingMap
     */
    public function __construct(
        private ContainerInterface $container,
        private array $routingMap
    ) { }

    /**
     * @template TResponse
     * @param RequestInterface<TResponse> $request
     * @return TResponse
     */
    public function send(RequestInterface $request): mixed
    {
        if (array_key_exists($request::class, $this->routingMap)) {
            $handlerClass = $this->routingMap[$request::class];
            $handler = $this->container->get($handlerClass)
                ?? throw new RequestHandlerNotFoundException($handlerClass);

            /**
             * @var TResponse
             * @psalm-suppress MixedMethodCall
             */
            return $handler->handle($request);

        } else {
            throw new UnroutedRequestException($request::class);
        }
    }
}