<?php

declare(strict_types=1);

namespace Havn\Executable\Testing\Exceptions;

use LogicException;

/**
 * @internal
 */
final class CannotCheckArgumentsForJob extends LogicException
{
    public function __construct()
    {
        parent::__construct('Job must be an Executable to check arguments');
    }
}
