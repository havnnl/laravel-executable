<?php

declare(strict_types=1);

namespace Workbench\App\Executables\DynamicInjection;

use Havn\Executable\QueueableExecutable;
use Illuminate\Contracts\Cache\Repository;

class ServiceInjectionExecutable
{
    use QueueableExecutable;

    public function execute(string $orderId, int $amount): void
    {
        // ..
    }

    /**
     * Config method that type-hints a service (Repository) alongside an execute parameter.
     * Both should resolve correctly: the service via the container, the parameter by name.
     */
    public function displayName(Repository $cache, string $orderId): string
    {
        $_SERVER['_service_injection_cache_instance'] = $cache;
        $_SERVER['_service_injection_order_id'] = $orderId;

        return 'cached-order-'.$orderId;
    }

    /**
     * Tags method that type-hints a service alongside an execute parameter.
     *
     * @return array<int, string>
     */
    public function tags(Repository $cache, int $amount): array
    {
        $_SERVER['_service_injection_tags_cache_instance'] = $cache;
        $_SERVER['_service_injection_tags_amount'] = $amount;

        return ['amount:'.$amount];
    }
}
