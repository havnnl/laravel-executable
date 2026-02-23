<?php

declare(strict_types=1);

namespace Workbench\App\Executables\Analysis;

use Havn\Executable\QueueableExecutable;
use Workbench\App\Models\SomeModel;

class ExtraParameterExecutable
{
    use QueueableExecutable;

    public function execute(SomeModel $user): void {}

    /**
     * @return array<int, string>
     */
    public function tags(string $label): array
    {
        return [];
    }
}
