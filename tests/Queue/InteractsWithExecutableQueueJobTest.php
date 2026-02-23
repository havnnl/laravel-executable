<?php

declare(strict_types=1);

use Havn\Executable\Jobs\ExecutableJob;
use Illuminate\Bus\Batch;
use Workbench\App\Executables\ClosureInvokingQueueableExecutable;

beforeEach(function () {
    $this->executableJob = Mockery::mock(ExecutableJob::class);

    $this->executable = new ClosureInvokingQueueableExecutable($this->executableJob);
});

it('gets attempts from ExecutableJob', function () {
    $this->executableJob
        ->shouldReceive('attempts')
        ->once()
        ->andReturn(3);

    $result = $this->executable->execute((fn () => $this->attempts())->bindTo($this->executable, $this->executable));

    expect($result)->toBe(3);
});

it('calls delete on ExecutableJob', function () {
    $this->executableJob
        ->shouldReceive('delete')
        ->once();

    $this->executable->execute((fn () => $this->delete())->bindTo($this->executable, $this->executable));
});

it('calls fail on ExecutableJob', function () {
    $this->executableJob
        ->shouldReceive('fail')
        ->with('message')
        ->once();

    $this->executable->execute((fn () => $this->fail('message'))->bindTo($this->executable, $this->executable));
});

it('calls release on ExecutableJob', function () {
    $this->executableJob
        ->shouldReceive('release')
        ->with(60)
        ->once();

    $this->executable->execute((fn () => $this->release(60))->bindTo($this->executable, $this->executable));
});

it('calls prependToChain on ExecutableJob', function () {
    $this->executableJob
        ->shouldReceive('prependToChain')
        ->with($job = new StdClass)
        ->once();

    $result = $this->executable->execute((fn () => $this->prependToChain($job))->bindTo($this->executable, $this->executable));

    expect($result)->toBe($this->executable);
});

it('calls appendToChain on ExecutableJob', function () {
    $this->executableJob
        ->shouldReceive('appendToChain')
        ->with($job = new StdClass)
        ->once();

    $result = $this->executable->execute((fn () => $this->appendToChain($job))->bindTo($this->executable, $this->executable));

    expect($result)->toBe($this->executable);
});

it('gets batch from ExecutableJob', function () {
    $this->executableJob
        ->shouldReceive('batch')
        ->once()
        ->andReturn($batch = Mockery::mock(Batch::class));

    $result = $this->executable->execute((fn () => $this->batch())->bindTo($this->executable, $this->executable));

    expect($result)->toBe($batch);
});
