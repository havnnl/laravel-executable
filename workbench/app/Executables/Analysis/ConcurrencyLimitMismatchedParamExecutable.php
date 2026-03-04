<?php

declare(strict_types=1);

namespace Workbench\App\Executables\Analysis;

use Havn\Executable\Attributes\ConcurrencyLimit;
use Havn\Executable\QueueableExecutable;
use Workbench\App\Models\SomeModel;

class ConcurrencyLimitMismatchedParamExecutable
{
    use QueueableExecutable;

    public function execute(SomeModel $user): void {}

    public function concurrencyLimit(string $label): ConcurrencyLimit
    {
        return new ConcurrencyLimit(key: 'test');
    }
}
