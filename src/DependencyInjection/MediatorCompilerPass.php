<?php

declare(strict_types=1);

namespace Whsv26\Mediator\DependencyInjection;

use Fp\Collections\HashSet;
use Fp\Collections\Seq;
use Fp\Functional\Option\Option;
use Fp\Streams\Stream;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\Argument\ServiceLocatorArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Reference;
use Whsv26\Mediator\Contract\CommandHandlerInterface;
use Whsv26\Mediator\Contract\CommandMiddlewareInterface;
use Whsv26\Mediator\Contract\MediatorInterface;
use Whsv26\Mediator\Contract\QueryHandlerInterface;
use Whsv26\Mediator\Contract\QueryMiddlewareInterface;
use Whsv26\Mediator\Parsing\HandlerMapParser;

use function Fp\Evidence\proveClassString;
use function Fp\Evidence\proveOf;
use function Fp\Evidence\proveString;
use function Fp\Reflection\getReflectionClass;

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

        $commandMiddlewares = $this->findCommandMiddlewares()->toArray();
        $queryMiddlewares = $this->findQueryMiddlewares()->toArray();
        $handlerMap = $this->parseHandlerMap(new HandlerMapParser());

        $container
            ->getDefinition(MediatorInterface::class)
            ->setArguments([
                new ServiceLocatorArgument($handlerMap),
                new IteratorArgument($commandMiddlewares),
                new IteratorArgument($queryMiddlewares),
            ]);
    }

    /**
     * @psalm-type Request = class-string
     * @psalm-type RequestHandler = Reference
     * @return array<Request, RequestHandler>
     */
    private function parseHandlerMap(HandlerMapParser $parser): array
    {
        $commandHandlers = $this->findServices(CommandHandlerInterface::TAG);
        $queryHandlers = $this->findServices(QueryHandlerInterface::TAG);

        return $commandHandlers
            ->appendedAll($queryHandlers)
            ->filterOf(Reference::class)
            ->filterMap(fn(Reference $ref) => proveClassString((string) $ref))
            ->filterMap(fn(string $id) => getReflectionClass($id)->toOption())
            ->filterMap(fn(ReflectionClass $class) => proveString($class->getFileName()))
            ->filterMap(fn(string $file) => $parser->parseFile($file))
            ->map(fn(array $pair) => [$pair[0], new Reference($pair[1])])
            ->toAssocArray(fn(array $pair) => $pair);
    }

    /**
     * @return Seq<Reference>
     */
    private function findCommandMiddlewares(): Seq
    {
        $taggedMiddlewares = $this->findServiceClasses(CommandMiddlewareInterface::TAG);

        $enabledMiddlewares = $this->extension
            ->flatMap(fn(MediatorExtension $ext) => $ext->getProcessedConfiguration())
            ->map(fn($configs) => $configs['command']['middlewares'] ?? [])
            ->getOrElse([]);

        return HashSet::collect($enabledMiddlewares)
            ->intersect($taggedMiddlewares->toHashSet())
            ->toArrayList()
            ->map(fn($middleware) => new Reference($middleware));
    }

    /**
     * @return Seq<Reference>
     */
    private function findQueryMiddlewares(): Seq
    {
        $taggedMiddlewares = $this->findServiceClasses(QueryMiddlewareInterface::TAG);

        $enabledMiddlewares = $this->extension
            ->flatMap(fn(MediatorExtension $ext) => $ext->getProcessedConfiguration())
            ->map(fn($configs) => $configs['query']['middlewares'] ?? [])
            ->getOrElse([]);

        return HashSet::collect($enabledMiddlewares)
            ->intersect($taggedMiddlewares->toHashSet())
            ->toArrayList()
            ->map(fn($middleware) => new Reference($middleware));
    }

    /**
     * @return Stream<Reference>
     */
    private function findServices(string $tag): Stream
    {
        $services = $this->container
            ->map(fn($container) => $this->findAndSortTaggedServices($tag, $container))
            ->getOrElse([]);

        return Stream::emits($services);
    }

    /**
     * @return Stream<class-string>
     */
    private function findServiceClasses(string $tag): Stream
    {
        return $this->findServices($tag)
            ->map(fn(Reference $ref) => (string) $ref)
            ->filterMap(fn($id) => proveClassString($id));
    }
}
