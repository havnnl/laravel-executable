<?php

declare(strict_types=1);

namespace Havn\Executable\Contracts;

use Havn\Executable\ExecutionMode;
use Havn\Executable\PendingExecution;

/**
 * @see ExecutionMode::TEST
 *
 * @internal
 *
 * @phpstan-method PendingExecution execute(...$args)
 */
interface TestedExecutableMethods {}
