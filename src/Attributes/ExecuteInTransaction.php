<?php

declare(strict_types=1);

namespace Havn\Executable\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class ExecuteInTransaction
{
    public function __construct(
        public int $attempts = 1,
    ) {}
}
