<?php

namespace Whsv26\Mediator\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Whsv26\Mediator\Contract\CommandInterface;
use Whsv26\Mediator\Contract\MediatorInterface;
use Whsv26\Mediator\Contract\QueryInterface;
use Whsv26\Mediator\Contract\RequestInterface;
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

    public function send(RequestInterface $request): mixed
    {
        if (array_key_exists($request::class, $this->routingMap)) {
            return $this->container
                ->get($this->routingMap[$request::class])
                ->handle($request);

        } elseif ($request instanceof QueryInterface) {
            throw new UnroutedQueryException($request::class);
        } elseif ($request instanceof CommandInterface) {
            throw new UnroutedCommandException($request::class);
        } else {
            throw new UnroutedRequestException($request::class);
        }
    }
}