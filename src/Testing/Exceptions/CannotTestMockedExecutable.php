<?php

declare(strict_types=1);

namespace Havn\Executable\Testing\Exceptions;

use LogicException;

/**
 * @internal
 */
final class CannotTestMockedExecutable extends LogicException
{
    public function __construct(string $executableClass)
    {
        parent::__construct(
            "Cannot test mocked executable [$executableClass]."
        );
    }
}
