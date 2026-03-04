<?php

declare(strict_types=1);

namespace Workbench\App\Executables;

use Havn\Executable\Attributes\ExecuteInTransaction;
use Havn\Executable\QueueableExecutable;

#[ExecuteInTransaction]
class UseTransactionByAttributeExecutable
{
    use QueueableExecutable;

    public function execute(mixed $return): mixed
    {
        return $return;
    }
}
