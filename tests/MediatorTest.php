<?php

namespace Whsv26\Tests;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Whsv26\Mediator\Contract\MediatorInterface;
use PHPUnit\Framework\TestCase;

class MediatorTest extends TestCase
{
    private function findMediatorService(ContainerBuilder $container): MediatorInterface
    {
        $mediator = $container->get(MediatorInterface::class);
        assert($mediator instanceof MediatorInterface);
        return $mediator;
    }
}
