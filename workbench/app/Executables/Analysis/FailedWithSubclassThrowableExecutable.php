<?php

declare(strict_types=1);

namespace Workbench\App\Executables\Analysis;

use Exception;
use Havn\Executable\QueueableExecutable;
use Workbench\App\Models\SomeModel;

class FailedWithSubclassThrowableExecutable
{
    use QueueableExecutable;

    public function execute(SomeModel $user): void {}

    public function failed(Exception $e, SomeModel $user): void {}
}
