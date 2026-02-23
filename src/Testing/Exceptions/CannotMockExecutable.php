<?php

declare(strict_types=1);

namespace Havn\Executable\Testing\Exceptions;

use LogicException;

/**
 * @internal
 */
final class CannotMockExecutable extends LogicException
{
    public function __construct(string $executableClass)
    {
        parent::__construct("Cannot mock [$executableClass]. The current environment is not [testing]");
    }
}
