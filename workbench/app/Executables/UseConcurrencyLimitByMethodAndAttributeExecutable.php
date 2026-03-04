<?php

declare(strict_types=1);

namespace Workbench\App\Executables;

use Havn\Executable\Attributes\ConcurrencyLimit;
use Havn\Executable\QueueableExecutable;

#[ConcurrencyLimit('attribute-key')]
class UseConcurrencyLimitByMethodAndAttributeExecutable
{
    use QueueableExecutable;

    public function concurrencyLimit(): ConcurrencyLimit
    {
        return new ConcurrencyLimit(
            key: 'method-key',
        );
    }

    public function execute(mixed $return): mixed
    {
        return $return;
    }
}
