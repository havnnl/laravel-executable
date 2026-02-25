<?php

declare(strict_types=1);

namespace Workbench\App\Executables\Analysis;

use Havn\Executable\Jobs\ExecutableJob;

use function PHPStan\Testing\assertType;

class ExecuteReturnTypeFixture
{
    public function syncReturnsString(): void
    {
        assertType('string', StringReturnExecutable::sync()->execute());
    }

    public function queueReturnsString(): void
    {
        assertType('string', StringReturnExecutable::onQueue()->execute());
    }

    public function testReturnsString(): void
    {
        assertType('string', StringReturnExecutable::test()->execute());
    }

    public function prepareReturnsExecutableJob(): void
    {
        assertType(ExecutableJob::class, StringReturnExecutable::prepare()->execute());
    }

    public function syncReturnsVoid(): void
    {
        assertType('null', VoidExecutable::sync()->execute());
    }
}
