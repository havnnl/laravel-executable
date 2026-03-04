<?php

declare(strict_types=1);

namespace Workbench\App\Executables;

use Havn\Executable\Attributes\ConcurrencyLimit;
use Havn\Executable\QueueableExecutable;
use Workbench\App\Models\SomeModel;

class UseConcurrencyLimitWithParamsExecutable
{
    use QueueableExecutable;

    public function concurrencyLimit(SomeModel $model): ConcurrencyLimit
    {
        return new ConcurrencyLimit(
            key: "model-{$model->id}",
        );
    }

    public function execute(SomeModel $model): string
    {
        return "processed-{$model->id}";
    }
}
