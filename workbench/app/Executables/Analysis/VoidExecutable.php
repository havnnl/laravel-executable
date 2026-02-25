<?php

declare(strict_types=1);

namespace Workbench\App\Executables\Analysis;

use Havn\Executable\Executable;

class VoidExecutable
{
    use Executable;

    public function execute(): void {}
}
