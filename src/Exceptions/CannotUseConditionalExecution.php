<?php

declare(strict_types=1);

namespace Havn\Executable\Exceptions;

use Havn\Executable\ExecutionMode;
use LogicException;

/**
 * @internal
 */
final class CannotUseConditionalExecution extends LogicException
{
    public function __construct(string $method, ExecutionMode $executionMode)
    {
        $modeName = strtolower($executionMode->name);

        parent::__construct(
            "Cannot use {$method}() with {$modeName} execution mode. Conditional execution is only supported for queue and sync modes."
        );
    }
}
