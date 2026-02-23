<?php

declare(strict_types=1);

namespace Workbench\App;

class InputService
{
    public function concatenate(string ...$input): string
    {
        return implode(' ', $input);
    }
}
