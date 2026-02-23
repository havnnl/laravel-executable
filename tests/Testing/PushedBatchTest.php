<?php

declare(strict_types=1);

use Havn\Executable\Testing\Queueing\PushedBatch;
use Havn\Executable\Testing\Queueing\PushedJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Testing\Fakes\PendingBatchFake;
use Symfony\Component\VarDumper\VarDumper;
use Workbench\App\Executables\UseTransactionExecutable;
use Workbench\App\Jobs\SimpleEncryptedJob;
use Workbench\App\Jobs\SimpleJob;

beforeEach(function () {
    $this->batch = Mockery::mock(PendingBatchFake::class);
    $this->sut = new PushedBatch($this->batch);
});

it('checks connection', function () {
    $this->batch->shouldReceive('connection')->andReturn('some-connection');

    expect($this->sut->isOnConnection('some-connection'))->toBeTrue()
        ->and($this->sut->isOnConnection('some-other-connection'))->toBeFalse();
});

it('checks queue', function () {
    $this->batch->shouldReceive('queue')->andReturn('some-queue');

    expect($this->sut->isOnQueue('some-queue'))->toBeTrue()
        ->and($this->sut->isOnQueue('some-other-queue'))->toBeFalse();
});

it('checks name', function () {
    $this->batch->name = 'some-name';

    expect($this->sut->hasName('some-name'))->toBeTrue()
        ->and($this->sut->hasName('some-other-name'))->toBeFalse();
});

it('checks having progress callbacks', function () {
    $this->batch->shouldReceive('progressCallbacks')->andReturn([fn () => 'hello']);

    expect($this->sut->containsProgressCallback())->toBeTrue()
        ->and($this->sut->containsProgressCallback(fn ($callback) => $callback() == 'hello'))->toBeTrue()
        ->and($this->sut->containsProgressCallback(fn ($callback) => $callback() == 'goodbye'))->toBeFalse();
});

it('checks having before callbacks', function () {
    $this->batch->shouldReceive('beforeCallbacks')->andReturn([fn () => 'hello']);

    expect($this->sut->containsBeforeCallback())->toBeTrue()
        ->and($this->sut->containsBeforeCallback(fn ($callback) => $callback() == 'hello'))->toBeTrue()
        ->and($this->sut->containsBeforeCallback(fn ($callback) => $callback() == 'goodbye'))->toBeFalse();
});

it('checks having then callbacks', function () {
    $this->batch->shouldReceive('thenCallbacks')->andReturn([fn () => 'hello']);

    expect($this->sut->containsThenCallback())->toBeTrue()
        ->and($this->sut->containsThenCallback(fn ($callback) => $callback() == 'hello'))->toBeTrue()
        ->and($this->sut->containsThenCallback(fn ($callback) => $callback() == 'goodbye'))->toBeFalse();
});

it('checks having catch callbacks', function () {
    $this->batch->shouldReceive('catchCallbacks')->andReturn([fn () => 'hello']);

    expect($this->sut->containsCatchCallback())->toBeTrue()
        ->and($this->sut->containsCatchCallback(fn ($callback) => $callback() == 'hello'))->toBeTrue()
        ->and($this->sut->containsCatchCallback(fn ($callback) => $callback() == 'goodbye'))->toBeFalse();
});

it('checks having finally callbacks', function () {
    $this->batch->shouldReceive('finallyCallbacks')->andReturn([fn () => 'hello']);

    expect($this->sut->containsFinallyCallback())->toBeTrue()
        ->and($this->sut->containsFinallyCallback(fn ($callback) => $callback() == 'hello'))->toBeTrue()
        ->and($this->sut->containsFinallyCallback(fn ($callback) => $callback() == 'goodbye'))->toBeFalse();
});

it('checks having option', function () {
    $this->batch->options['key'] = 'hello';

    expect($this->sut->hasOption('key'))->toBeTrue()
        ->and($this->sut->hasOption('key', fn ($value) => $value == 'hello'))->toBeTrue()
        ->and($this->sut->hasOption('key', fn ($value) => $value == 'goodbye'))->toBeFalse()
        ->and($this->sut->hasOption('other-key'))->toBeFalse();
});

it('checks having x jobs', function () {
    $this->batch->jobs = collect([
        new SimpleJob,
        UseTransactionExecutable::prepare()->execute('some input'),
    ]);

    expect($this->sut->hasCount(2))->toBeTrue()
        ->and($this->sut->hasCount(3))->toBeFalse();
});

it('checks containing jobs', function () {
    $this->batch->jobs = collect([
        new SimpleJob,
        UseTransactionExecutable::prepare()->execute('some input'),
    ]);

    expect($this->sut->contains(SimpleJob::class))->toBeTrue()
        ->and($this->sut->contains(UseTransactionExecutable::class))->toBeTrue()
        ->and($this->sut->contains(SimpleEncryptedJob::class))->toBeFalse()
        ->and($this->sut->contains(fn (SimpleJob $job) => true))->toBeTrue()
        ->and($this->sut->contains(fn (UseTransactionExecutable $job) => true))->toBeTrue()
        ->and($this->sut->contains(fn (PushedJob $job) => true))->toBeTrue()
        ->and($this->sut->contains(fn (SimpleJob $job) => false))->toBeFalse()
        ->and($this->sut->contains(fn (UseTransactionExecutable $job) => false))->toBeFalse()
        ->and($this->sut->contains(fn (PushedJob $job) => false))->toBeFalse();
});

it('checks exactly containing jobs', function () {
    $this->batch->jobs = collect([
        new SimpleJob,
        UseTransactionExecutable::prepare()->execute('some input'),
    ]);

    expect($this->sut->containsExactly([SimpleJob::class, UseTransactionExecutable::class]))->toBeTrue()
        ->and($this->sut->containsExactly([UseTransactionExecutable::class, SimpleJob::class]))->toBeTrue()
        ->and($this->sut->containsExactly([SimpleEncryptedJob::class, SimpleJob::class]))->toBeFalse()
        ->and($this->sut->containsExactly([SimpleJob::class]))->toBeFalse()
        ->and($this->sut->containsExactly([UseTransactionExecutable::class, SimpleJob::class]))->toBeTrue()
        ->and($this->sut->containsExactly([fn (PushedJob $job) => true]))->toBeFalse()
        ->and($this->sut->containsExactly([fn (PushedJob $job) => true, fn (PushedJob $job) => true]))->toBeTrue()
        ->and($this->sut->containsExactly([fn (UseTransactionExecutable $job) => true, fn (SimpleJob $job) => true]))->toBeTrue()
        ->and($this->sut->containsExactly([fn (UseTransactionExecutable $job) => true, fn (SimpleEncryptedJob $job) => true]))->toBeFalse()
        ->and($this->sut->containsExactly([fn (PushedJob $job) => true, fn (PushedJob $job) => false]))->toBeFalse()
        ->and($this->sut->containsExactly([fn (PushedJob $job) => true, fn (PushedJob $job) => true, fn (PushedJob $job) => true]))->toBeFalse();
});

it('returns summary in array', function () {
    $this->batch->name = 'some name';
    $this->batch->shouldReceive('connection')->andReturn('some-connection');
    $this->batch->shouldReceive('queue')->andReturn('some-queue');
    $this->batch->shouldReceive('allowsFailures')->andReturnTrue();
    $this->batch->jobs = collect([
        new SimpleJob,
        UseTransactionExecutable::prepare()->execute('some input'),
    ]);
    $this->batch->shouldReceive('progressCallbacks')->andReturn([
        fn () => '',
    ]);
    $this->batch->shouldReceive('beforeCallbacks')->andReturn([
        fn () => '', fn () => '',
    ]);
    $this->batch->shouldReceive('thenCallbacks')->andReturn([
        fn () => '', fn () => '', fn () => '',
    ]);
    $this->batch->shouldReceive('catchCallbacks')->andReturn([
        fn () => '', fn () => '', fn () => '', fn () => '',
    ]);
    $this->batch->shouldReceive('finallyCallbacks')->andReturn([
        fn () => '', fn () => '', fn () => '', fn () => '', fn () => '',
    ]);

    expect($this->sut->summary())->toEqual([
        'name' => 'some name',
        'connection' => 'some-connection',
        'queue' => 'some-queue',
        'allowsFailures' => true,
        'jobs_count' => 2,
        'jobs' => [
            [
                'job' => SimpleJob::class,
                'chain' => [],
            ],
            [
                'executable' => UseTransactionExecutable::class,
                'arguments' => ['return' => 'some input'],
                'chain' => [],
            ],
        ],
        'progress_callbacks_count' => 1,
        'before_callbacks_count' => 2,
        'then_callbacks_count' => 3,
        'catch_callbacks_count' => 4,
        'finally_callbacks_count' => 5,
    ]);
});

it('outputs batch summary on dump', function () {
    $dumpedValues = [];

    VarDumper::setHandler(function ($value) use (&$dumpedValues) {
        $dumpedValues[] = $value;
    });

    Bus::fake();

    Bus::batch([new SimpleJob])->name('test-batch')->dispatch();

    $batches = Bus::batched(fn () => true);
    $batch = new PushedBatch($batches[0]);

    $batch->dump();

    expect($dumpedValues)->toHaveCount(1)
        ->and($dumpedValues[0])->toBe($batch->summary());
});

it('asserts connection', function () {
    $this->batch->shouldReceive('connection')->andReturn('redis');

    expect($this->sut->assertIsOnConnection('redis'))->toBe($this->sut);
});

it('fails asserting connection', function () {
    $this->batch->shouldReceive('connection')->andReturn('sync');

    expect(fn () => $this->sut->assertIsOnConnection('redis'))
        ->toThrow(PHPUnit\Framework\ExpectationFailedException::class, 'Batch is on connection [sync] instead of [redis]');
});

it('asserts queue', function () {
    $this->batch->shouldReceive('queue')->andReturn('high-priority');

    expect($this->sut->assertIsOnQueue('high-priority'))->toBe($this->sut);
});

it('fails asserting queue', function () {
    $this->batch->shouldReceive('queue')->andReturn('default');

    expect(fn () => $this->sut->assertIsOnQueue('high-priority'))
        ->toThrow(PHPUnit\Framework\ExpectationFailedException::class, 'Batch is on queue [default] instead of [high-priority]');
});

it('asserts allows failures', function () {
    $this->batch->shouldReceive('allowsFailures')->andReturnTrue();

    expect($this->sut->assertAllowsFailures())->toBe($this->sut);
});

it('fails asserting allows failures', function () {
    $this->batch->shouldReceive('allowsFailures')->andReturnFalse();

    expect(fn () => $this->sut->assertAllowsFailures())
        ->toThrow(PHPUnit\Framework\ExpectationFailedException::class, 'Batch does not allow failures');
});

it('asserts has name', function () {
    $this->batch->name = 'order-processing';

    expect($this->sut->assertHasName('order-processing'))->toBe($this->sut);
});

it('fails asserting has name', function () {
    $this->batch->name = 'user-processing';

    expect(fn () => $this->sut->assertHasName('order-processing'))
        ->toThrow(PHPUnit\Framework\ExpectationFailedException::class, 'Batch has name [user-processing] instead of [order-processing]');
});

it('asserts contains progress callback', function () {
    $this->batch->shouldReceive('progressCallbacks')->andReturn([fn () => 'progress']);

    expect($this->sut->assertContainsProgressCallback())->toBe($this->sut);
});

it('fails asserting contains progress callback', function () {
    $this->batch->shouldReceive('progressCallbacks')->andReturn([]);

    expect(fn () => $this->sut->assertContainsProgressCallback())
        ->toThrow(PHPUnit\Framework\ExpectationFailedException::class, 'Batch does not contain expected progress callback');
});

it('asserts contains before callback', function () {
    $this->batch->shouldReceive('beforeCallbacks')->andReturn([fn () => 'before']);

    expect($this->sut->assertContainsBeforeCallback())->toBe($this->sut);
});

it('fails asserting contains before callback', function () {
    $this->batch->shouldReceive('beforeCallbacks')->andReturn([]);

    expect(fn () => $this->sut->assertContainsBeforeCallback())
        ->toThrow(PHPUnit\Framework\ExpectationFailedException::class, 'Batch does not contain expected before callback');
});

it('asserts contains then callback', function () {
    $this->batch->shouldReceive('thenCallbacks')->andReturn([fn () => 'then']);

    expect($this->sut->assertContainsThenCallback())->toBe($this->sut);
});

it('fails asserting contains then callback', function () {
    $this->batch->shouldReceive('thenCallbacks')->andReturn([]);

    expect(fn () => $this->sut->assertContainsThenCallback())
        ->toThrow(PHPUnit\Framework\ExpectationFailedException::class, 'Batch does not contain expected then callback');
});

it('asserts contains catch callback', function () {
    $this->batch->shouldReceive('catchCallbacks')->andReturn([fn () => 'catch']);

    expect($this->sut->assertContainsCatchCallback())->toBe($this->sut);
});

it('fails asserting contains catch callback', function () {
    $this->batch->shouldReceive('catchCallbacks')->andReturn([]);

    expect(fn () => $this->sut->assertContainsCatchCallback())
        ->toThrow(PHPUnit\Framework\ExpectationFailedException::class, 'Batch does not contain expected catch callback');
});

it('asserts contains finally callback', function () {
    $this->batch->shouldReceive('finallyCallbacks')->andReturn([fn () => 'finally']);

    expect($this->sut->assertContainsFinallyCallback())->toBe($this->sut);
});

it('fails asserting contains finally callback', function () {
    $this->batch->shouldReceive('finallyCallbacks')->andReturn([]);

    expect(fn () => $this->sut->assertContainsFinallyCallback())
        ->toThrow(PHPUnit\Framework\ExpectationFailedException::class, 'Batch does not contain expected finally callback');
});

it('asserts has option', function () {
    $this->batch->options['timeout'] = 3600;

    expect($this->sut->assertHasOption('timeout'))->toBe($this->sut);
    expect($this->sut->assertHasOption('timeout', fn ($v) => $v === 3600))->toBe($this->sut);
});

it('fails asserting has option', function () {
    $this->batch->options = [];

    expect(fn () => $this->sut->assertHasOption('timeout'))
        ->toThrow(PHPUnit\Framework\ExpectationFailedException::class, 'Batch does not have expected option: [timeout]');
});

it('asserts has count', function () {
    $this->batch->jobs = collect([
        new SimpleJob,
        UseTransactionExecutable::prepare()->execute('some input'),
    ]);

    expect($this->sut->assertHasCount(2))->toBe($this->sut);
});

it('fails asserting has count', function () {
    $this->batch->jobs = collect([new SimpleJob]);

    expect(fn () => $this->sut->assertHasCount(2))
        ->toThrow(PHPUnit\Framework\ExpectationFailedException::class, 'Batch has [1] jobs instead of [2]');
});

it('asserts contains job', function () {
    $this->batch->jobs = collect([
        new SimpleJob,
        UseTransactionExecutable::prepare()->execute('some input'),
    ]);

    expect($this->sut->assertContains(SimpleJob::class))->toBe($this->sut);
    expect($this->sut->assertContains(UseTransactionExecutable::class))->toBe($this->sut);
});

it('fails asserting contains job', function () {
    $this->batch->jobs = collect([new SimpleJob]);

    expect(fn () => $this->sut->assertContains(SimpleEncryptedJob::class))
        ->toThrow(PHPUnit\Framework\ExpectationFailedException::class, 'Batch does not contain expected job');
});

it('asserts contains exactly', function () {
    $this->batch->jobs = collect([
        new SimpleJob,
        UseTransactionExecutable::prepare()->execute('some input'),
    ]);

    expect($this->sut->assertContainsExactly([SimpleJob::class, UseTransactionExecutable::class]))->toBe($this->sut);
    expect($this->sut->assertContainsExactly([UseTransactionExecutable::class, SimpleJob::class]))->toBe($this->sut);
});

it('fails asserting contains exactly', function () {
    $this->batch->jobs = collect([new SimpleJob]);

    expect(fn () => $this->sut->assertContainsExactly([SimpleJob::class, UseTransactionExecutable::class]))
        ->toThrow(PHPUnit\Framework\ExpectationFailedException::class, 'Batch does not contain exactly the expected jobs');
});
