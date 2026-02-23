<?php

declare(strict_types=1);

namespace Workbench\App\Executables\Testing;

use Havn\Executable\QueueableExecutable;

class DeletedExecutable
{
    use QueueableExecutable;

    public function execute(): void
    {
        $this->delete();
    }
}
