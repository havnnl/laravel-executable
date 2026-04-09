<?php

declare(strict_types=1);

use Havn\Executable\Tests\Concerns\SkipsLaravelVersions;
use Havn\Executable\Tests\TestCase;

uses(TestCase::class, SkipsLaravelVersions::class)->in(__DIR__);
