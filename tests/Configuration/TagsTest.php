<?php

declare(strict_types=1);

use Havn\Executable\Jobs\ExecutableJob;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Queue;
use Workbench\App\Executables\Configuration\ConfigMethodReturnsInputExecutable;
use Workbench\App\Executables\MultiParamQueueableExecutable;
use Workbench\App\Executables\PlainQueueableExecutable;
use Workbench\App\Models\SomeModel;

beforeEach(function () {
    Queue::fake();
});

it('can be dispatched without specifying tags for laravel horizon', function (): void {
    PlainQueueableExecutable::onQueue()->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->tags())->toBeNull();
    });
});

it('adds model tags for laravel horizon automatically', function () {
    MultiParamQueueableExecutable::onQueue()->execute(new SomeModel(['id' => 123]), new SomeModel(['id' => 456]));

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->tags())->toBe([
            SomeModel::class.':123',
            SomeModel::class.':456',
        ]);
    });
});

it('adds collection model tags for laravel horizon automatically', function () {
    PlainQueueableExecutable::onQueue()->execute(new Collection([
        new SomeModel(['id' => 123]), new SomeModel(['id' => 456]),
    ]));

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->tags())->toBe([
            SomeModel::class.':123',
            SomeModel::class.':456',
        ]);
    });
});

it('can set tags for laravel horizon on method', function () {
    ConfigMethodReturnsInputExecutable::onQueue()->execute(['tag-1', 'tag-2']);

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->tags())->toBe(['tag-1', 'tag-2']);
    });
});
