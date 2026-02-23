<?php

declare(strict_types=1);

namespace Workbench\App\Executables\Testing;

use Havn\Executable\QueueableExecutable;

class ReleasedExecutable
{
    use QueueableExecutable;

    public function execute(mixed $input): mixed
    {
        $this->release(5);

        return $input;
    }
}
