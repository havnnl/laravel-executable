<?php

declare(strict_types=1);

namespace Havn\Executable\Tests\Concerns;

use Illuminate\Foundation\Application;

trait SkipsLaravelVersions
{
    public function skipBeforeLaravel(int|string $version): void
    {
        $version = (string) $version;

        if (version_compare(Application::VERSION, $version, '<')) {
            $this->markTestSkipped("Requires Laravel {$version}+.");
        }
    }
}
