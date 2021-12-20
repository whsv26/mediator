<?php

namespace Whsv26\Tests;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Whsv26\Mediator\Contract\MediatorInterface;
use Whsv26\Mediator\DependencyInjection\MediatorExtension;
use Whsv26\Tests\Dummy\DummyQueryOne;
use PHPUnit\Framework\TestCase;

class MediatorTest extends TestCase
{
    public function testMediator(): void
    {
        $extension = new MediatorExtension();
        $container = new ContainerBuilder();
        $container->setParameter('kernel.project_dir', __DIR__.'/..');
        $extension->load([], $container);
        $container->compile();

        $mediator = $container->get(MediatorInterface::class);
        $response = $mediator->send(new DummyQueryOne());
        $this->assertEquals(1, $response->value);
    }
}