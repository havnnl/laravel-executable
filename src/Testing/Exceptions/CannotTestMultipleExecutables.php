<?php

declare(strict_types=1);

namespace Havn\Executable\Testing\Exceptions;

use LogicException;

/**
 * @internal
 */
final class CannotTestMultipleExecutables extends LogicException
{
    public function __construct(string $executableClass)
    {
        parent::__construct(
            "Cannot call test() multiple times in one test (already testing [$executableClass]). ".
            'Use sync() for additional executions or split into separate tests.'
        );
    }
}
