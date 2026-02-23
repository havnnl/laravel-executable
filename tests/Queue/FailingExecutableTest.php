<?php

declare(strict_types=1);

use Workbench\App\Executables\FailingExecutable;

beforeEach(function () {
    $_SERVER['_failing_executable_exception'] = null;
    $_SERVER['_failing_executable_arguments'] = null;
});

afterEach(function () {
    unset($_SERVER['_failing_executable_exception']);
    unset($_SERVER['_failing_executable_arguments']);
});

it('calls failed hook with exception and arguments', function () {
    try {
        FailingExecutable::onQueue()->execute('some input');
    } catch (Throwable $e) {

    }

    expect($_SERVER['_failing_executable_exception'])->toEqual($e)
        ->and($_SERVER['_failing_executable_arguments'])->toBe(['some input']);
});
