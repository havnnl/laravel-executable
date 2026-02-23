<?php

declare(strict_types=1);

namespace Workbench\App\Executables\Testing;

use Havn\Executable\QueueableExecutable;

class FailedExecutable
{
    use QueueableExecutable;

    public function execute(): void
    {
        $this->fail('some message');
    }
}
