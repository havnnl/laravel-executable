<?php

declare(strict_types=1);

namespace Workbench\App\Executables\Analysis;

use Havn\Executable\QueueableExecutable;
use Workbench\App\Models\SomeModel;

class FailedWithoutThrowableExecutable
{
    use QueueableExecutable;

    public function execute(SomeModel $user): void {}

    public function failed(SomeModel $user): void {}
}
