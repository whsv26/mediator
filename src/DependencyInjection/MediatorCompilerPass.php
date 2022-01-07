<?php

declare(strict_types=1);

namespace Whsv26\Mediator\DependencyInjection;

use Fp\Functional\Option\Option;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Reference;
use Whsv26\Mediator\Contract\MediatorInterface;

use function Fp\Evidence\proveOf;

final class MediatorCompilerPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    /**
     * @var Option<MediatorExtension>
     */
    private Option $extension;

    /**
     * @var Option<ContainerBuilder>
     */
    private Option $container;

    public function __construct(?ExtensionInterface $extension = null)
    {
        $this->container = Option::none();
        $this->extension = Option::fromNullable($extension)
            ->flatMap(fn($ext) => proveOf($ext, MediatorExtension::class));
    }

    public function process(ContainerBuilder $container): void
    {
        $this->container = Option::some($container);

        $buses = $this->extension
            ->flatMap(fn(MediatorExtension $ext) => $ext->getProcessedConfiguration())
            ->map(fn(array $config) => $config['bus'])
            ->getUnsafe();

        $container
            ->getDefinition(MediatorInterface::class)
            ->setArguments([
                new Reference($buses['query']),
                new Reference($buses['command']),
                new Reference($buses['event']),
            ]);
    }
}
