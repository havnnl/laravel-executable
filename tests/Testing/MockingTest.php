<?php

declare(strict_types=1);

use Havn\Executable\Testing\Exceptions\CannotMockExecutable;
use Illuminate\Support\Facades\App;
use Mockery\Exception\InvalidCountException;
use Workbench\App\Executables\PlainQueueableExecutable;

it('cannot mock when not testing', function () {
    App::shouldReceive('runningUnitTests')->andReturnFalse();

    PlainQueueableExecutable::mock();

})->throws(CannotMockExecutable::class);

it('mocks resolved executable', function () {
    PlainQueueableExecutable::mock()->shouldExecute()->once()
        ->with('input')
        ->andReturn('I was mocked');

    $result = PlainQueueableExecutable::sync()->execute('input');

    expect($result)->toBe('I was mocked');
});

it('mocks injected executable', function () {
    PlainQueueableExecutable::mock()->shouldExecute()->once()
        ->with('input')
        ->andReturn('I was mocked');

    $result = app(PlainQueueableExecutable::class)->execute('input');

    expect($result)->toBe('I was mocked');
});

it('mocks injected executables running through PendingExecution', function () {
    PlainQueueableExecutable::mock()->shouldExecute()->once()
        ->with('input')
        ->andReturn('I was mocked');

    $result = app(PlainQueueableExecutable::class)->sync()->execute('input');

    expect($result)->toBe('I was mocked');
});

it('fails when a resolved mock unexpectedly does not execute', function () {
    PlainQueueableExecutable::mock()->shouldExecute()->once();

    PlainQueueableExecutable::sync();

    Mockery::close();
})->throws(InvalidCountException::class);

it('fails when a resolved mock unexpectedly executes', function () {
    PlainQueueableExecutable::mock()->shouldNeverExecute();

    PlainQueueableExecutable::sync()->execute('input');

    Mockery::close();
})->throws(InvalidCountException::class);

it('fails when an injected mock unexpectedly does not execute', function () {
    PlainQueueableExecutable::mock()->shouldExecute()->once();

    app(PlainQueueableExecutable::class);

    Mockery::close();
})->throws(InvalidCountException::class);

it('fails when an injected mock unexpectedly executes', function () {
    PlainQueueableExecutable::mock()->shouldNeverExecute();

    app(PlainQueueableExecutable::class)->execute('input');

    Mockery::close();
})->throws(InvalidCountException::class);
