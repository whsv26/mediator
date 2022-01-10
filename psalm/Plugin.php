<?php

declare(strict_types=1);

namespace Whsv26\Mediator\Psalm;

use Psalm\Plugin\PluginEntryPointInterface;
use Psalm\Plugin\RegistrationInterface;
use SimpleXMLElement;

/**
 * Plugin entrypoint
 */
class Plugin implements PluginEntryPointInterface
{
    public function __invoke(RegistrationInterface $registration, ?SimpleXMLElement $config = null): void
    {
        $register =
            /**
             * @param class-string $hook
             */
            function(string $hook) use ($registration): void {
                class_exists($hook);
                $registration->registerHooksFromClass($hook);
            };

        $register(MessageHandlerReturnTypeValidator::class);
    }
}
