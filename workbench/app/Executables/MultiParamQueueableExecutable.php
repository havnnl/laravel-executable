<?php

declare(strict_types=1);

namespace Workbench\App\Executables;

use Havn\Executable\QueueableExecutable;

class MultiParamQueueableExecutable
{
    use QueueableExecutable;

    public function execute(mixed $first = null, mixed $second = null): mixed
    {
        return $first;
    }
}
