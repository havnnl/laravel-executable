<?php

declare(strict_types=1);

namespace Workbench\App\Executables\Configuration;

use Havn\Executable\QueueableExecutable;
use Illuminate\Queue\Attributes\Backoff;

#[Backoff([1, 5, 10])]
class BackoffArrayByAttributeExecutable
{
    use QueueableExecutable;

    public function execute(): void
    {
        // ..
    }
}
