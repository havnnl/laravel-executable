<?php

declare(strict_types=1);

use Havn\Executable\Testing\Exceptions\CannotSpyExecutable;
use Havn\Executable\Testing\Exceptions\CannotTestExecutable;
use Havn\Executable\Testing\Exceptions\CannotTestMultipleExecutables;
use Havn\Executable\Testing\Exceptions\ExecutionAssertionsNotAvailable;
use Havn\Executable\Testing\Exceptions\ExecutionNotAvailable;
use Havn\Executable\Testing\Facades\Execution;
use Illuminate\Support\Facades\App;
use Workbench\App\Executables\PlainQueueableExecutable;

it('cannot spy when not testing', function () {
    App::shouldReceive('runningUnitTests')->andReturnFalse();

    PlainQueueableExecutable::spy();
})->throws(CannotSpyExecutable::class);

it('cannot test when not testing', function () {
    App::shouldReceive('runningUnitTests')->andReturnFalse();

    PlainQueueableExecutable::test();
})->throws(CannotTestExecutable::class);

it('cannot assert when not testing', function () {
    App::shouldReceive('runningUnitTests')->andReturnFalse();

    PlainQueueableExecutable::assert();
})->throws(ExecutionAssertionsNotAvailable::class);

it('cannot fake when not testing', function () {
    App::shouldReceive('runningUnitTests')->andReturnFalse();

    Execution::getFacadeRoot();
})->throws(ExecutionNotAvailable::class);

it('cannot test multiple executables', function () {
    PlainQueueableExecutable::test()->execute('hello');
    PlainQueueableExecutable::test()->execute('world');
})->throws(CannotTestMultipleExecutables::class);
