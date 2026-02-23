<?php

declare(strict_types=1);

use Havn\Executable\Exceptions\CannotUseConditionalExecution;
use Illuminate\Support\Facades\Queue;
use Workbench\App\Executables\PlainQueueableExecutable;

it('executes synchronously with when() based on condition', function (bool|Closure $condition, bool $shouldExecute) {
    $result = PlainQueueableExecutable::sync()
        ->when($condition)
        ->execute('foo');

    if ($shouldExecute) {
        expect($result)->toBe('foo');
    } else {
        expect($result)->toBeNull();
    }
})->with([
    'boolean true executes' => [true, true],
    'boolean false does not execute' => [false, false],
    'closure true executes' => [fn () => true, true],
    'closure false does not execute' => [fn () => false, false],
]);

it('executes synchronously with unless() based on condition', function (bool|Closure $condition, bool $shouldExecute) {
    $result = PlainQueueableExecutable::sync()
        ->unless($condition)
        ->execute('foo');

    if ($shouldExecute) {
        expect($result)->toBe('foo');
    } else {
        expect($result)->toBeNull();
    }
})->with([
    'boolean false executes' => [false, true],
    'boolean true does not execute' => [true, false],
    'closure false executes' => [fn () => false, true],
    'closure true does not execute' => [fn () => true, false],
]);

it('dispatches to queue with when() based on condition', function (bool|Closure $condition, bool $shouldPush) {
    Queue::fake();

    PlainQueueableExecutable::onQueue()
        ->when($condition)
        ->execute('test-input');

    if ($shouldPush) {
        PlainQueueableExecutable::assert()->queued();
    } else {
        PlainQueueableExecutable::assert()->notQueued();
    }
})->with([
    'boolean true dispatches' => [true, true],
    'boolean false does not dispatch' => [false, false],
    'closure true dispatches' => [fn () => true, true],
    'closure false does not dispatch' => [fn () => false, false],
]);

it('dispatches to queue with unless() based on condition', function (bool|Closure $condition, bool $shouldPush) {
    Queue::fake();

    PlainQueueableExecutable::onQueue()
        ->unless($condition)
        ->execute('test-input');

    if ($shouldPush) {
        PlainQueueableExecutable::assert()->queued();
    } else {
        PlainQueueableExecutable::assert()->notQueued();
    }
})->with([
    'boolean false dispatches' => [false, true],
    'boolean true does not dispatch' => [true, false],
    'closure false dispatches' => [fn () => false, true],
    'closure true does not dispatch' => [fn () => true, false],
]);

it('can chain multiple conditional checks', function () {
    Queue::fake();

    PlainQueueableExecutable::onQueue()
        ->when(true)
        ->unless(false)
        ->execute('test-input');

    PlainQueueableExecutable::assert()->queued();
});

it('respects all conditions when multiple are chained', function () {
    Queue::fake();

    PlainQueueableExecutable::onQueue()
        ->when(true)
        ->unless(true)
        ->execute('test-input');

    PlainQueueableExecutable::assert()->notQueued();
});

it('evaluates multiple chained when conditions', function (bool $first, bool $second, bool $third, bool $shouldExecute) {
    $result = PlainQueueableExecutable::sync()
        ->when($first)
        ->when($second)
        ->when($third)
        ->execute('foo');

    if ($shouldExecute) {
        expect($result)->toBe('foo');
    } else {
        expect($result)->toBeNull();
    }
})->with([
    'all true executes' => [true, true, true, true],
    'any false does not execute' => [true, false, true, false],
]);

it('evaluates all conditions in order', function () {
    $evaluationOrder = [];

    $result = PlainQueueableExecutable::sync()
        ->when(function () use (&$evaluationOrder) {
            $evaluationOrder[] = 'first';

            return true;
        })
        ->when(function () use (&$evaluationOrder) {
            $evaluationOrder[] = 'second';

            return true;
        })
        ->unless(function () use (&$evaluationOrder) {
            $evaluationOrder[] = 'third';

            return false;
        })
        ->execute('foo');

    expect($result)->toBe('foo')
        ->and($evaluationOrder)->toBe(['first', 'second', 'third']);
});

it('stops evaluating conditions on first failure', function () {
    $evaluationOrder = [];

    $result = PlainQueueableExecutable::sync()
        ->when(function () use (&$evaluationOrder) {
            $evaluationOrder[] = 'first';

            return true;
        })
        ->when(function () use (&$evaluationOrder) {
            $evaluationOrder[] = 'second';

            return false;
        })
        ->when(function () use (&$evaluationOrder) {
            $evaluationOrder[] = 'third';

            return true;
        })
        ->execute('foo');

    expect($result)->toBeNull()
        ->and($evaluationOrder)->toBe(['first', 'second']); // Third condition should not be evaluated
});

it('throws exception when using when() with prepare execution', function () {
    PlainQueueableExecutable::prepare()->when(true);
})
    ->throws(CannotUseConditionalExecution::class, 'Cannot use when() with prepare execution mode. Conditional execution is only supported for queue and sync modes.');

it('throws exception when using unless() with prepare execution', function () {
    PlainQueueableExecutable::prepare()->unless(true);
})->throws(CannotUseConditionalExecution::class, 'Cannot use unless() with prepare execution mode. Conditional execution is only supported for queue and sync modes.');

it('throws exception when using when() with test execution', function () {
    PlainQueueableExecutable::test()->when(true);
})->throws(CannotUseConditionalExecution::class, 'Cannot use when() with test execution mode. Conditional execution is only supported for queue and sync modes.');

it('throws exception when using unless() with test execution', function () {
    PlainQueueableExecutable::test()->unless(true);
})->throws(CannotUseConditionalExecution::class, 'Cannot use unless() with test execution mode. Conditional execution is only supported for queue and sync modes.');
