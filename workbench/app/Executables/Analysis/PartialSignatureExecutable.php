<?php

declare(strict_types=1);

namespace Workbench\App\Executables\Analysis;

use Havn\Executable\QueueableExecutable;
use Workbench\App\Models\SomeModel;

class PartialSignatureExecutable
{
    use QueueableExecutable;

    public function execute(SomeModel $user, string $action): void {}

    public function retryUntil(SomeModel $user): ?\DateTimeInterface
    {
        return null;
    }
}
