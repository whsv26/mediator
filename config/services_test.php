<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Whsv26\Mediator\Contract\MediatorInterface;
use Whsv26\Mediator\DependencyInjection\Mediator;
use Whsv26\Tests\Dummy\DummyCommandMiddleware;
use Whsv26\Tests\Dummy\DummyQueryMiddleware;
use Whsv26\Tests\Dummy\DummyQueryThreeHandler;
use Whsv26\Tests\Dummy\DummyQueryTwoHandler;
use Whsv26\Tests\Dummy\DummyService;
use Whsv26\Tests\Dummy\DummyCommandOneHandler;
use Whsv26\Tests\Dummy\DummyQueryOneHandler;

use function Symfony\Component\DependencyInjection\Loader\Configurator\abstract_arg;

return static function (ContainerConfigurator $configurator) {
    $services = $configurator->services();
    $services
        ->defaults()
        ->autowire()       // Automatically injects dependencies in your services.
        ->autoconfigure(); // Automatically registers your services as commands, event subscribers, etc.

    $services
        ->set(MediatorInterface::class, Mediator::class)
        ->public()
        ->args([
            abstract_arg('Request to RequestHandler service locator'),
            abstract_arg('Command middlewares'),
            abstract_arg('Query middlewares'),
        ]);

    $services->set(DummyQueryOneHandler::class);
    $services->set(DummyQueryTwoHandler::class);
    $services->set(DummyQueryThreeHandler::class);

    $services->set(DummyCommandOneHandler::class);
    $services->set(DummyCommandMiddleware::class);

    $services->set(DummyQueryMiddleware::class);

    $services->set(DummyService::class)->args([1]);
};
