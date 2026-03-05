<?php

declare(strict_types=1);

namespace Workbench\App\Executables;

use Havn\Executable\QueueableExecutable;

class UseConcurrencyLimitByInheritedAttributeExecutable extends UseConcurrencyLimitByAttributeExecutable
{
    use QueueableExecutable;

    public function execute(mixed $return): mixed
    {
        return $return;
    }
}
