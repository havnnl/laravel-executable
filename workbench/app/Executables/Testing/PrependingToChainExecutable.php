<?php

declare(strict_types=1);

namespace Workbench\App\Executables\Testing;

use Havn\Executable\QueueableExecutable;

class PrependingToChainExecutable
{
    use QueueableExecutable;

    public function execute(mixed $job): void
    {
        $this->prependToChain($job);
    }
}
