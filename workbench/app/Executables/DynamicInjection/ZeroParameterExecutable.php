<?php

declare(strict_types=1);

namespace Workbench\App\Executables\DynamicInjection;

use Havn\Executable\QueueableExecutable;

class ZeroParameterExecutable
{
    use QueueableExecutable;

    public function execute(): void
    {
        // ..
    }

    /**
     * Config method with no params on a zero-parameter execute().
     */
    public function displayName(): string
    {
        $_SERVER['_zero_param_display_name_called'] = true;

        return 'zero-param-job';
    }

    /**
     * @return array<int, string>
     */
    public function tags(): array
    {
        $_SERVER['_zero_param_tags_called'] = true;

        return ['zero-param'];
    }
}
