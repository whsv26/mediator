<?php

namespace Whsv26\Tests\Dummy;

use Whsv26\Mediator\Contract\MediatorInterface;
use Whsv26\Mediator\Contract\QueryHandlerInterface;

/**
 * @implements QueryHandlerInterface<Foo, DummyQueryThree>
 */
class DummyQueryThreeHandler implements QueryHandlerInterface
{
    public function __construct(
        private MediatorInterface $mediator
    ) { }

    /**
     * @param DummyQueryThree $query
     * @return Foo
     */
    public function handle(mixed $query): Foo
    {
        $foo = $this->mediator->send(new DummyQueryOne());
        $foo->value++;
        return $foo;
    }
}
