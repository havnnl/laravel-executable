<?php

declare(strict_types=1);

namespace Workbench\App\Executables\Analysis;

use Havn\Executable\QueueableExecutable;

class StringReturnExecutable
{
    use QueueableExecutable;

    public function execute(): string
    {
        return 'result';
    }
}
