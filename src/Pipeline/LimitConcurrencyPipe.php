<?php

declare(strict_types=1);

namespace Havn\Executable\Pipeline;

use Closure;
use Havn\Executable\Attributes\ConcurrencyLimit;
use Illuminate\Support\Facades\Cache;

/**
 * @internal
 */
final class LimitConcurrencyPipe
{
    public function __construct(private ConcurrencyLimit $config) {}

    public function handle(mixed $passable, Closure $next): mixed
    {
        $callback = fn () => $next($passable);

        if ($this->config->store) {
            return Cache::store($this->config->store)->withoutOverlapping(
                $this->config->key, $callback, $this->config->lockFor, $this->config->waitFor,
            );
        }

        return Cache::withoutOverlapping(
            $this->config->key, $callback, $this->config->lockFor, $this->config->waitFor,
        );
    }
}
