<?php

declare(strict_types=1);

namespace Havn\Executable\Support;

use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * @internal
 */
trait InvokesExecutableMethods
{
    /**
     * @param  array<string, mixed>  $arguments
     */
    protected function invoke(object $target, string $method, array $arguments): mixed
    {
        try {
            return app()->call([$target, $method], $arguments);
        } catch (BindingResolutionException) {
            // fall back to positional splat
            return $target->{$method}(...array_values($arguments));
        }
    }
}
