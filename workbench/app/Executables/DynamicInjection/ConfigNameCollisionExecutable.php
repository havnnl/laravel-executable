<?php

declare(strict_types=1);

namespace Workbench\App\Executables\DynamicInjection;

use Havn\Executable\Config\QueueableConfig;
use Havn\Executable\QueueableExecutable;

class ConfigNameCollisionExecutable
{
    use QueueableExecutable;

    public function execute(string $config, int $amount): void
    {
        // $config here is a plain string, not QueueableConfig
    }

    /**
     * The QueueableConfig parameter shares the name $config with execute().
     * Should still receive the QueueableConfig instance, not the string.
     */
    public function configure(QueueableConfig $config, int $amount): void
    {
        $_SERVER['_config_collision_config'] = $config;
        $_SERVER['_config_collision_amount'] = $amount;

        $config->tries(7);
    }
}
