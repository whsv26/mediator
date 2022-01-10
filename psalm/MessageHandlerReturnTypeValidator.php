<?php

declare(strict_types=1);


use Fp\Functional\Option\Option;
use Psalm\CodeLocation;
use Psalm\Internal\Provider\ClassLikeStorageProvider;
use Psalm\Issue\InvalidReturnType;
use Psalm\IssueBuffer;
use Psalm\Plugin\EventHandler\AfterClassLikeAnalysisInterface;
use Psalm\Plugin\EventHandler\Event\AfterClassLikeAnalysisEvent;
use Psalm\Storage\ClassLikeStorage;
use Psalm\Storage\FunctionLikeParameter;
use Psalm\Storage\MethodStorage;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Atomic\TVoid;
use Psalm\Type\Union;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Whsv26\Mediator\Contract\CommandInterface;
use Whsv26\Mediator\Contract\QueryInterface;
use Whsv26\Mediator\Contract\RequestInterface;

use function Fp\Collection\at;
use function Fp\Collection\firstOf;
use function Fp\Collection\head;
use function Fp\Evidence\proveFalse;

/**
 * @psalm-suppress InternalMethod,InternalProperty
 */
class MessageHandlerReturnTypeValidator implements AfterClassLikeAnalysisInterface
{
    public static function afterStatementAnalysis(AfterClassLikeAnalysisEvent $event): void
    {
        Option::do(function () use ($event) {
            $handlerMethod = yield Option::some($event->getClasslikeStorage())
                ->filter(fn(ClassLikeStorage $store) => array_key_exists(
                    strtolower(MessageHandlerInterface::class),
                    $store->class_implements
                ))
                ->flatMap(fn(ClassLikeStorage $class) => at($class->methods, '__invoke'));

            $handlerMethodLocation = yield Option::fromNullable($handlerMethod->location);
            $handlerReturnType = $handlerMethod->return_type ?? new Union([new TVoid()]);

            $messageClass = yield Option::some($handlerMethod)
                ->flatMap(fn(MethodStorage $method) => head($method->params))
                ->flatMap(fn(FunctionLikeParameter $param) => Option::fromNullable($param->type))
                ->flatMap(fn(Union $union) => firstOf($union->getAtomicTypes(), TNamedObject::class))
                ->map(fn(TNamedObject $object) => $object->value);

            $codebase = $event->getCodebase();

            $messageReturnType = yield Option::some($codebase->classlike_storage_provider)
                ->filter(fn(ClassLikeStorageProvider $provider) => $provider->has($messageClass))
                ->map(fn(ClassLikeStorageProvider $provider) => $provider->get($messageClass))
                ->map(fn(ClassLikeStorage $class) => $class->template_extended_offsets ?? [])
                ->flatMap(function (array $offsets) {
                    return at($offsets, CommandInterface::class)
                        ->orElse(fn() => at($offsets, QueryInterface::class))
                        ->orElse(fn() => at($offsets, RequestInterface::class));
                })
                ->flatMap(fn(array $templates) => head($templates))
                ->filterOf(Union::class);


            yield proveFalse($codebase->isTypeContainedByType($handlerReturnType, $messageReturnType));

            self::issueInvalidReturnType($handlerMethodLocation);
        });
    }

    private static function issueInvalidReturnType(CodeLocation $location): void
    {
        $message = 'Command/Query return type must be compatible with handler return type';

        IssueBuffer::accepts(new InvalidReturnType($message, $location));
    }
}
