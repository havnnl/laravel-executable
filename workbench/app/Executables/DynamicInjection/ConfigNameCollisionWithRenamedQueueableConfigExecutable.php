<?php

declare(strict_types=1);

namespace Workbench\App\Executables\DynamicInjection;

use Havn\Executable\Config\QueueableConfig;
use Havn\Executable\QueueableExecutable;

class ConfigNameCollisionWithRenamedQueueableConfigExecutable
{
    use QueueableExecutable;

    public function execute(string $config, int $amount): void
    {
        // $config here is a plain string, not QueueableConfig
    }

    /**
     * Uses a different name for QueueableConfig while also receiving
     * the $config string from execute().
     */
    public function configure(QueueableConfig $queueConfig, string $config): void
    {
        $_SERVER['_config_collision_renamed_queue_config'] = $queueConfig;
        $_SERVER['_config_collision_renamed_config'] = $config;

        $queueConfig->tries(9);
    }
}
