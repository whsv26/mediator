<?php

namespace Whsv26\Mediator;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Whsv26\Mediator\DependencyInjection\MediatorCompilerPass;

class MediatorBundle extends Bundle
{
    public const ALIAS = 'mediator';

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new MediatorCompilerPass($this->getContainerExtension()));
    }
}
