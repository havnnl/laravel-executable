<?php

declare(strict_types=1);

namespace Havn\Executable\Testing\Exceptions;

use LogicException;

/**
 * @internal
 */
final class ExecutionAssertionsNotAvailable extends LogicException
{
    public function __construct(string $executableClass)
    {
        parent::__construct("Execution assertions not available for [$executableClass]. The current environment is not [testing]");
    }
}
