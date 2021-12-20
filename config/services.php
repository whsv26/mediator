<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Whsv26\Mediator\Contract\MediatorInterface;
use Whsv26\Mediator\DependencyInjection\Mediator;
use Whsv26\Tests\Dummy\Sub\DummyQueryOneHandler;

use function Symfony\Component\DependencyInjection\Loader\Configurator\abstract_arg;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator) {
    $services = $configurator->services();
    $services
        ->defaults()
        ->autowire()       // Automatically injects dependencies in your services.
        ->autoconfigure(); // Automatically registers your services as commands, event subscribers, etc.

    $mediator = $services
        ->set(MediatorInterface::class, Mediator::class)
        ->args([
            service('service_container'),
            abstract_arg('Request to RequestHandler routing map')
        ]);

    // TODO
    $mediator->public();
    $services
        ->set(DummyQueryOneHandler::class)
        ->public();
};
