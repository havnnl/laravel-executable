<?php

declare(strict_types=1);

namespace Havn\Executable\Testing\Analysis;

/**
 * @internal
 */
final readonly class ParameterMismatch
{
    public function __construct(
        public string $className,
        public string $methodName,
        public string $parameterName,
        public string $message,
    ) {}
}
