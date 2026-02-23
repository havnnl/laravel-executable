<?php

declare(strict_types=1);

namespace Workbench\App\Executables\Configuration;

use DateTimeInterface;
use Havn\Executable\QueueableExecutable;
use Illuminate\Contracts\Cache\Repository;

class ConfigMethodReturnsInputExecutable
{
    use QueueableExecutable;

    public function execute(mixed $input): void
    {
        // ..
    }

    /**
     * @return array<int, int>|int|null
     */
    public function backoff(mixed $input): array|int|null
    {
        return is_int($input) || is_array($input)
            ? $input
            : null;
    }

    public function displayName(mixed $input): ?string
    {
        return is_string($input) ? $input : null;
    }

    public function retryUntil(mixed $input): ?DateTimeInterface
    {
        return $input instanceof DateTimeInterface ? $input : null;
    }

    public function tries(mixed $input): ?int
    {
        return is_int($input) ? $input : null;
    }

    public function uniqueFor(mixed $input): ?int
    {
        return is_int($input) ? $input : null;
    }

    public function uniqueId(mixed $input): int|string|null
    {
        return is_int($input) || is_string($input)
            ? $input
            : null;
    }

    /**
     * @return array<int, string>|null
     */
    public function tags(mixed $input): ?array
    {
        return is_array($input) ? $input : null;
    }

    public function uniqueVia(mixed $input): Repository
    {
        return $input instanceof Repository ? $input : resolve(Repository::class);
    }

    /**
     * @return array<int, mixed>
     */
    public function middleware(mixed $input): array
    {
        return is_array($input) ? $input : [];
    }
}
