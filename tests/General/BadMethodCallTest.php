<?php

declare(strict_types=1);

use Workbench\App\Executables\InputConcatenatingExecutable;
use Workbench\App\Executables\PlainQueueableExecutable;

describe('Executable trait', function () {
    it('throws BadMethodCallException for invalid static method', function () {
        InputConcatenatingExecutable::nonExistentMethod();
    })->throws(BadMethodCallException::class);

    it('throws BadMethodCallException for invalid instance method', function () {
        $executable = app(InputConcatenatingExecutable::class);
        $executable->nonExistentMethod();
    })->throws(BadMethodCallException::class);
});

describe('QueueableExecutable trait', function () {
    it('throws BadMethodCallException for invalid static method', function () {
        PlainQueueableExecutable::nonExistentMethod();
    })->throws(BadMethodCallException::class);

    it('throws BadMethodCallException for invalid instance method', function () {
        $executable = new PlainQueueableExecutable;
        $executable->nonExistentMethod();
    })->throws(BadMethodCallException::class);
});
