<?php

declare(strict_types=1);

namespace Workbench\App\Executables;

use Closure;
use Havn\Executable\Jobs\ExecutableJob;
use Havn\Executable\QueueableExecutable;

class ClosureInvokingQueueableExecutable
{
    use QueueableExecutable;

    public function __construct(ExecutableJob $executableQueueJob)
    {
        $this->executableJob = $executableQueueJob;
    }

    public function execute(Closure $closure): mixed
    {
        return $closure();
    }
}
