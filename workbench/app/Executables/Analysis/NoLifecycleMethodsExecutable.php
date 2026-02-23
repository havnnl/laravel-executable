<?php

declare(strict_types=1);

namespace Workbench\App\Executables\Analysis;

use Havn\Executable\QueueableExecutable;
use Workbench\App\Models\SomeModel;

class NoLifecycleMethodsExecutable
{
    use QueueableExecutable;

    public function execute(SomeModel $user): void {}
}
