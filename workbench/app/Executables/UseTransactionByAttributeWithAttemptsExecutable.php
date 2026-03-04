<?php

declare(strict_types=1);

namespace Workbench\App\Executables;

use Havn\Executable\Config\ExecuteInTransaction;
use Havn\Executable\QueueableExecutable;

#[ExecuteInTransaction(attempts: 3)]
class UseTransactionByAttributeWithAttemptsExecutable
{
    use QueueableExecutable;

    public function execute(mixed $return): mixed
    {
        return $return;
    }
}
