<?php

declare(strict_types=1);

use Havn\Executable\Jobs\ExecutableJob;
use Illuminate\Support\Facades\Queue;
use Workbench\App\Executables\Configuration\WithoutRelationsByAttributeExecutable;
use Workbench\App\Executables\Configuration\WithoutRelationsByConfigHookExecutable;
use Workbench\App\Executables\Configuration\WithoutRelationsByPropertyExecutable;
use Workbench\App\Executables\PlainQueueableExecutable;
use Workbench\App\Models\SomeModel;
use Workbench\App\Models\SomeOtherModel;

beforeEach(function (): void {
    Queue::fake();

    $model = SomeModel::query()->create();
    $model->someOtherModels()->save(new SomeOtherModel);
});

it('serializes models without relations when executable property is false', function (): void {
    config()->set('executable.serialize_models_with_relations', true);

    $model = SomeModel::query()->with('someOtherModels')->first();

    WithoutRelationsByPropertyExecutable::onQueue()->execute($model);

    Queue::assertPushed(function (ExecutableJob $job) {
        $serialized = serialize($job);

        return str_contains($serialized, SomeModel::class)
            && ! str_contains($serialized, 'someOtherModels');
    });
});

it('serializes models without relations when attribute is added', function (): void {
    config()->set('executable.serialize_models_with_relations', true);

    $model = SomeModel::query()->with('someOtherModels')->first();

    WithoutRelationsByAttributeExecutable::onQueue()->execute($model);

    Queue::assertPushed(function (ExecutableJob $job) {
        $serialized = serialize($job);

        return str_contains($serialized, SomeModel::class)
            && ! str_contains($serialized, 'someOtherModels');
    });
});

it('serializes models without relations when configured by hook', function (): void {
    config()->set('executable.serialize_models_with_relations', true);

    $model = SomeModel::query()->with('someOtherModels')->first();

    WithoutRelationsByConfigHookExecutable::onQueue()->execute($model);

    Queue::assertPushed(function (ExecutableJob $job) {
        $serialized = serialize($job);

        return str_contains($serialized, SomeModel::class)
            && ! str_contains($serialized, 'someOtherModels');
    });
});

it('serializes models without relations when config is false', function (): void {
    config()->set('executable.serialize_models_with_relations', false);

    $model = SomeModel::query()->with('someOtherModels')->first();

    PlainQueueableExecutable::onQueue()->execute($model);

    Queue::assertPushed(function (ExecutableJob $job) {
        $serialized = serialize($job);

        return str_contains($serialized, SomeModel::class)
            && ! str_contains($serialized, 'someOtherModels');
    });
});

it('serializes models with relations when config is true', function (): void {
    config()->set('executable.serialize_models_with_relations', true);

    $model = SomeModel::query()->with('someOtherModels')->first();

    PlainQueueableExecutable::onQueue()->execute($model);

    Queue::assertPushed(function (ExecutableJob $job) {
        $serialized = serialize($job);

        return str_contains($serialized, SomeModel::class)
            && str_contains($serialized, 'someOtherModels');
    });
});
