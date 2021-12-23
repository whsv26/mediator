<?php

namespace Whsv26\Tests;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Whsv26\Mediator\Contract\MediatorInterface;
use Whsv26\Mediator\DependencyInjection\MediatorCompilerPass;
use Whsv26\Mediator\DependencyInjection\MediatorExtension;
use Whsv26\Tests\Dummy\DummyCommandOne;
use Whsv26\Tests\Dummy\DummyQueryOne;
use PHPUnit\Framework\TestCase;

class MediatorTest extends TestCase
{
    private function findMediatorService(ContainerBuilder $container): MediatorInterface
    {
        $mediator = $container->get(MediatorInterface::class);
        assert($mediator instanceof MediatorInterface);
        return $mediator;
    }

    public function testQuerySending(): void
    {
        $extension = new MediatorExtension();
        $container = new ContainerBuilder();
        $container->addCompilerPass(new MediatorCompilerPass());
        $extension->load([], $container);
        $container->compile();
        $mediator = $this->findMediatorService($container);
        $response = $mediator->send(new DummyQueryOne());

        $this->assertEquals(1, $response->value);
    }

    public function testCommandSending(): void
    {
        $extension = new MediatorExtension();
        $container = new ContainerBuilder();
        $container->addCompilerPass(new MediatorCompilerPass());
        $extension->load([], $container);
        $container->compile();
        $mediator = $this->findMediatorService($container);
        $response = $mediator->send(new DummyCommandOne());

        $this->assertTrue($response->get());
    }

    public function testMiddlewares(): void
    {
        $extension = new MediatorExtension();
        $container = new ContainerBuilder();
        $container->addCompilerPass(new MediatorCompilerPass());
        $extension->load([], $container);
        $container->compile();

        $mediator = $this->findMediatorService($container);
        $mediator->send(new DummyQueryOne());
        $this->expectOutputString("before_query_middleware after_query_middleware");
    }

    public function testConfigs(): void
    {
        $configs = [
            [
                'middlewares' => [
                    [
                        'attribute' => 'a1',
                        'middleware' => 'm1',
                    ]
                ]
            ]
        ];

        $extension = new MediatorExtension();
        $container = new ContainerBuilder();
        $container->addCompilerPass(new MediatorCompilerPass());
        $extension->load($configs, $container);
        $container->compile();
        $mediator = $this->findMediatorService($container);
        $this->assertEquals(1, 1);
    }
}
