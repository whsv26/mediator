<?php

namespace Whsv26\Mediator\DependencyInjection;

use Fp\Collections\Map;
use Fp\Functional\Option\Option;
use Fp\Streams\Stream;
use PhpParser\Comment\Doc;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\ParserFactory;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Whsv26\Mediator\Contract\CommandHandlerInterface;
use Whsv26\Mediator\Contract\QueryHandlerInterface;

use function Fp\classOf;
use function Fp\Collection\firstOf;
use function Fp\Collection\head;
use function Fp\Json\regExpMatch;

/**
 * @psalm-type TQuery = class-string
 * @psalm-type TCommand = class-string
 * @psalm-type THandled = TQuery|TCommand
 * @psalm-type TQueryHandler = class-string
 * @psalm-type TCommandHandler = class-string
 * @psalm-type THandler = TQueryHandler|TCommandHandler
 */
class MediatorExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configDir = new FileLocator(__DIR__ . '/../../config');

        // Load the bundle's service declarations
        $loader = new PhpFileLoader($container, $configDir);
        $loader->load('services.php');

        // Apply our config schema to the given app's configs
        $schema = new ConfigSchema();
        $options = $this->processConfiguration($schema, $configs);

        $routingMapParser = new RoutingMapParser();
        $projectDir = $container->getParameter('kernel.project_dir');
        $routingMap = $routingMapParser->parseDirRecursive($projectDir);

//        $repo = $container->getDefinition(DocumentRepository::class);
//        $repo->replaceArgument(0, $options['storageDir']);
    }
}