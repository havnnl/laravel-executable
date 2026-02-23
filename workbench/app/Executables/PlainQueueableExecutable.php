<?php

declare(strict_types=1);

namespace Workbench\App\Executables;

use Havn\Executable\QueueableExecutable;

class PlainQueueableExecutable
{
    use QueueableExecutable;

    public function execute(mixed $input = null): mixed
    {
        return $input;
    }
}
