<?php

declare(strict_types=1);

namespace Havn\Executable\Attributes;

use Attribute;
use Illuminate\Cache\Repository;

/**
 * @see Repository::withoutOverlapping()
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class ConcurrencyLimit
{
    public function __construct(
        public string $key,
        public int $lockFor = 0,
        public int $waitFor = 10,
        public ?string $store = null,
    ) {}
}
