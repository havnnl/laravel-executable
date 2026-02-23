<?php

declare(strict_types=1);

namespace Havn\Executable\Testing\Exceptions;

use LogicException;

/**
 * @internal
 */
final class CannotQueueMockedExecutable extends LogicException
{
    public function __construct(string $executableClass)
    {
        parent::__construct(
            "Cannot queue mocked executable [$executableClass]."
        );
    }
}
