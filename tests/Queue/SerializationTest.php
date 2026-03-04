<?php

declare(strict_types=1);

use Havn\Executable\Config\QueueableConfig;
use Havn\Executable\Jobs\ExecutableJob;
use Havn\Executable\Support\ExecutableArguments;
use Illuminate\Contracts\Database\ModelIdentifier;
use Workbench\App\Executables\PlainQueueableExecutable;
use Workbench\App\Models\SomeModel;
use Workbench\App\Models\SomeOtherModel;

beforeEach(function () {
    /** @var SomeModel $model */
    $model = SomeModel::query()->create();
    $model->someOtherModels()->save(new SomeOtherModel);
});

it('is serializes models in arguments', function () {
    $executable = new PlainQueueableExecutable;
    $model = SomeModel::query()->first();
    $job = new ExecutableJob($executable, ExecutableArguments::from(['input' => $model]), new QueueableConfig);

    $serialized = serialize($job);

    expect($serialized)->toContain(ModelIdentifier::class);
});

it('is serializes model collections in arguments', function () {
    $executable = new PlainQueueableExecutable;
    $modelCollection = SomeModel::query()->get();
    $job = new ExecutableJob($executable, ExecutableArguments::from(['input' => $modelCollection]), new QueueableConfig);

    $serialized = serialize($job);

    expect($serialized)->toContain(ModelIdentifier::class);
});

it('unserializes models in arguments', function () {
    $executable = new PlainQueueableExecutable;
    $model = SomeModel::query()->first();
    $job = new ExecutableJob($executable, ExecutableArguments::from(['input' => $model]), new QueueableConfig);

    $serialized = serialize($job);

    /** @var ExecutableJob $job */
    $job = unserialize($serialized);

    expect($job->arguments()['input'])->toBeInstanceOf(SomeModel::class)
        ->and($model->is($job->arguments()['input']))->toBeTrue();
});

it('serializes and unserializes variadic (mixed-key) arguments', function () {
    $executable = new PlainQueueableExecutable;
    $job = new ExecutableJob($executable, ExecutableArguments::from(['name' => 'foo', 0 => 'bar', 1 => 'baz']), new QueueableConfig);

    $serialized = serialize($job);

    /** @var ExecutableJob $job */
    $job = unserialize($serialized);

    expect($job->arguments())->toBe(['name' => 'foo', 0 => 'bar', 1 => 'baz']);
});

it('serializes arguments as a plain array for backward compatibility', function () {
    $executable = new PlainQueueableExecutable;
    $job = new ExecutableJob($executable, ExecutableArguments::from(['input' => 5]), new QueueableConfig);

    $data = $job->__serialize();

    expect($data["\0*\0arguments"])->toBe(['input' => 5]);
});
