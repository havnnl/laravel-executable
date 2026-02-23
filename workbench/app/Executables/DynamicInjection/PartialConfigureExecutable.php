<?php

declare(strict_types=1);

namespace Workbench\App\Executables\DynamicInjection;

use Havn\Executable\Config\QueueableConfig;
use Havn\Executable\QueueableExecutable;

class PartialConfigureExecutable
{
    use QueueableExecutable;

    public function execute(string $orderId, int $amount, string $currency): void
    {
        // ..
    }

    /**
     * Configure method that declares QueueableConfig + partial execute params.
     * Should receive the config object and the named amount value.
     */
    public function configure(QueueableConfig $config, int $amount): void
    {
        $_SERVER['_partial_configure_config'] = $config;
        $_SERVER['_partial_configure_amount'] = $amount;

        $config->tries($amount > 1000 ? 5 : 3);
    }
}
