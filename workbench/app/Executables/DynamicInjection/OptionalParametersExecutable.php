<?php

declare(strict_types=1);

namespace Workbench\App\Executables\DynamicInjection;

use Havn\Executable\QueueableExecutable;

class OptionalParametersExecutable
{
    use QueueableExecutable;

    public function execute(string $orderId, int $amount): void
    {
        // ..
    }

    /**
     * Config method with all optional/default parameters.
     * Should work when no matching execute params are provided.
     */
    public function displayName(string $prefix = 'default', int $version = 1): string
    {
        $_SERVER['_optional_params_prefix'] = $prefix;
        $_SERVER['_optional_params_version'] = $version;

        return $prefix.'-v'.$version;
    }
}
