<?php

declare(strict_types=1);

use Havn\Executable\Config\QueueableConfig;
use Havn\Executable\Jobs\ExecutableJob;
use Workbench\App\Executables\PlainQueueableExecutable;

it('is instantiated from an executable instance and execution arguments', function () {
    $executable = new PlainQueueableExecutable;
    $job = new ExecutableJob($executable, ['input' => 'value'], new QueueableConfig);

    expect($job)->toBeInstanceOf(ExecutableJob::class)
        ->and($job->executableClass())->toBe(PlainQueueableExecutable::class)
        ->and($job->executable())->toBe($executable)
        ->and($job->arguments())->toBe(['input' => 'value']);
});

it('executes executable', function () {
    $executable = new PlainQueueableExecutable;

    $job = new ExecutableJob($executable, ['input' => 5], new QueueableConfig);

    expect($job->handle())->toBe(5);
});

it('sets protected property executableJob on Executable to ExecutableJob instance', function () {
    $executable = new PlainQueueableExecutable;
    $job = new ExecutableJob($executable, [], new QueueableConfig);

    $job->handle();

    $reflection = new ReflectionClass($executable);
    $property = $reflection->getProperty('executableJob');
    $property->setAccessible(true);

    expect($property->getValue($executable))->toBe($job);
});
