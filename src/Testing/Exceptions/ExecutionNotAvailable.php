<?php

declare(strict_types=1);

namespace Havn\Executable\Testing\Exceptions;

use LogicException;

/**
 * @internal
 */
final class ExecutionNotAvailable extends LogicException
{
    public function __construct()
    {
        parent::__construct('Execution facade not available. The current environment is not [testing]');
    }
}
