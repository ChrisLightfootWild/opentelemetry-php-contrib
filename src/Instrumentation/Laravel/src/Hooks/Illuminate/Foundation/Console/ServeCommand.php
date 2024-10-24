<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Instrumentation\Laravel\Hooks\Illuminate\Foundation\Console;

use Illuminate\Foundation\Console\ServeCommand as FoundationServeCommand;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\Context as InstrumentationContext;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\HookManagerInterface;
use OpenTelemetry\Contrib\Instrumentation\Laravel\Hooks\Hook;
use OpenTelemetry\Contrib\Instrumentation\Laravel\LaravelInstrumentation;

/**
 * Instrument Laravel's local PHP development server.
 */
class ServeCommand implements Hook
{
    public function instrument(
        LaravelInstrumentation $instrumentation,
        HookManagerInterface $hookManager,
        InstrumentationContext $context,
    ): void {
        $hookManager->hook(
            FoundationServeCommand::class,
            'handle',
            preHook: static function (FoundationServeCommand $serveCommand, array $params, string $class, string $function, ?string $filename, ?int $lineno) {
                foreach ($_ENV as $key => $value) {
                    if (str_starts_with($key, 'OTEL_') && !in_array($key, FoundationServeCommand::$passthroughVariables)) {
                        FoundationServeCommand::$passthroughVariables[] = $key;
                    }
                }
            },
        );
    }
}
