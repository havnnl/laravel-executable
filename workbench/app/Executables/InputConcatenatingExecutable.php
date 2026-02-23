<?php

declare(strict_types=1);

namespace Workbench\App\Executables;

use Havn\Executable\Executable;
use Workbench\App\InputService;

class InputConcatenatingExecutable
{
    use Executable;

    public function __construct(private InputService $inputService) {}

    public function execute(string ...$input): string
    {
        return $this->inputService->concatenate(...$input);
    }
}
