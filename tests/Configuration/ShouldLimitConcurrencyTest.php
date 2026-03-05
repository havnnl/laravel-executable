<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Workbench\App\Executables\PlainQueueableExecutable;
use Workbench\App\Executables\UseConcurrencyLimitByAttributeExecutable;
use Workbench\App\Executables\UseConcurrencyLimitByAttributeWithOptionsExecutable;
use Workbench\App\Executables\UseConcurrencyLimitByInheritedAttributeExecutable;
use Workbench\App\Executables\UseConcurrencyLimitByMethodAndAttributeExecutable;
use Workbench\App\Executables\UseConcurrencyLimitExecutable;
use Workbench\App\Executables\UseConcurrencyLimitWithOptionsExecutable;
use Workbench\App\Executables\UseConcurrencyLimitWithParamsExecutable;
use Workbench\App\Executables\UseConcurrencyLimitWithTransactionExecutable;
use Workbench\App\Models\SomeModel;

it('does not limit concurrency in sync by default', function () {
    Cache::shouldReceive('withoutOverlapping')->never();

    PlainQueueableExecutable::sync()->execute('input');
});

it('does not limit concurrency on queue by default', function () {
    Cache::shouldReceive('withoutOverlapping')->never();

    PlainQueueableExecutable::onQueue()->execute('input');
});

it('limits concurrency in sync when concurrencyLimit method is defined', function () {
    Cache::shouldReceive('withoutOverlapping')
        ->once()
        ->withArgs(function ($key, $callback, $lockFor, $waitFor) {
            return (bool) expect($key)->toBe('test-concurrency')
                ->and($lockFor)->toBe(0)
                ->and($waitFor)->toBe(10);
        })
        ->andReturnUsing(fn ($key, $callback) => $callback());

    $result = UseConcurrencyLimitExecutable::sync()->execute('concurrency limited');

    expect($result)->toBe('concurrency limited');
});

it('limits concurrency on queue when concurrencyLimit method is defined', function () {
    Cache::shouldReceive('withoutOverlapping')
        ->once()
        ->withArgs(function ($key, $callback, $lockFor, $waitFor) {
            return (bool) expect($key)->toBe('test-concurrency')
                ->and($lockFor)->toBe(0)
                ->and($waitFor)->toBe(10);
        })
        ->andReturnUsing(fn ($key, $callback) => $callback());

    UseConcurrencyLimitExecutable::onQueue()->execute('concurrency limited');
});

it('passes custom options to concurrency limiter', function () {
    $store = Mockery::mock();
    $store->shouldReceive('withoutOverlapping')
        ->once()
        ->withArgs(function ($key, $callback, $lockFor, $waitFor) {
            return (bool) expect($key)->toBe('test-concurrency')
                ->and($lockFor)->toBe(120)
                ->and($waitFor)->toBe(30);
        })
        ->andReturnUsing(fn ($key, $callback) => $callback());

    Cache::shouldReceive('store')->with('redis')->once()->andReturn($store);

    $result = UseConcurrencyLimitWithOptionsExecutable::sync()->execute('custom options');

    expect($result)->toBe('custom options');
});

it('passes execute parameters to concurrencyLimit method', function () {
    $model = SomeModel::query()->create();

    Cache::shouldReceive('withoutOverlapping')
        ->once()
        ->withArgs(fn ($key, $callback) => (bool) expect($key)->toBe("model-{$model->id}"))
        ->andReturnUsing(fn ($key, $callback) => $callback());

    $result = UseConcurrencyLimitWithParamsExecutable::sync()->execute($model);

    expect($result)->toBe("processed-{$model->id}");
});

it('applies concurrency limit before transaction when both are used in sync', function () {
    $order = [];

    Cache::shouldReceive('withoutOverlapping')
        ->once()
        ->andReturnUsing(function ($key, $callback) use (&$order) {
            $order[] = 'concurrency';

            return $callback();
        });

    DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) use (&$order) {
        $order[] = 'transaction';

        return $callback();
    });

    UseConcurrencyLimitWithTransactionExecutable::sync()->execute('both');

    expect($order)->toBe(['concurrency', 'transaction']);
});

it('applies concurrency limit before transaction when both are used on queue', function () {
    $order = [];

    Cache::shouldReceive('withoutOverlapping')
        ->once()
        ->andReturnUsing(function ($key, $callback) use (&$order) {
            $order[] = 'concurrency';

            return $callback();
        });

    DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) use (&$order) {
        $order[] = 'transaction';

        return $callback();
    });

    UseConcurrencyLimitWithTransactionExecutable::onQueue()->execute('both');

    expect($order)->toBe(['concurrency', 'transaction']);
});

it('limits concurrency in sync when ConcurrencyLimit attribute is used', function () {
    Cache::shouldReceive('withoutOverlapping')
        ->once()
        ->withArgs(function ($key, $callback, $lockFor, $waitFor) {
            return (bool) expect($key)->toBe('test-concurrency')
                ->and($lockFor)->toBe(0)
                ->and($waitFor)->toBe(10);
        })
        ->andReturnUsing(fn ($key, $callback) => $callback());

    $result = UseConcurrencyLimitByAttributeExecutable::sync()->execute('attribute limited');

    expect($result)->toBe('attribute limited');
});

it('limits concurrency on queue when ConcurrencyLimit attribute is used', function () {
    Cache::shouldReceive('withoutOverlapping')
        ->once()
        ->withArgs(function ($key, $callback, $lockFor, $waitFor) {
            return (bool) expect($key)->toBe('test-concurrency')
                ->and($lockFor)->toBe(0)
                ->and($waitFor)->toBe(10);
        })
        ->andReturnUsing(fn ($key, $callback) => $callback());

    UseConcurrencyLimitByAttributeExecutable::onQueue()->execute('attribute limited');
});

it('passes custom options from ConcurrencyLimit attribute', function () {
    $store = Mockery::mock();
    $store->shouldReceive('withoutOverlapping')
        ->once()
        ->withArgs(function ($key, $callback, $lockFor, $waitFor) {
            return (bool) expect($key)->toBe('test-concurrency')
                ->and($lockFor)->toBe(120)
                ->and($waitFor)->toBe(30);
        })
        ->andReturnUsing(fn ($key, $callback) => $callback());

    Cache::shouldReceive('store')->with('redis')->once()->andReturn($store);

    $result = UseConcurrencyLimitByAttributeWithOptionsExecutable::sync()->execute('custom attribute');

    expect($result)->toBe('custom attribute');
});

it('inherits ConcurrencyLimit attribute from parent class', function () {
    Cache::shouldReceive('withoutOverlapping')
        ->once()
        ->withArgs(function ($key, $callback, $lockFor, $waitFor) {
            return (bool) expect($key)->toBe('test-concurrency')
                ->and($lockFor)->toBe(0)
                ->and($waitFor)->toBe(10);
        })
        ->andReturnUsing(fn ($key, $callback) => $callback());

    $result = UseConcurrencyLimitByInheritedAttributeExecutable::sync()->execute('inherited');

    expect($result)->toBe('inherited');
});

it('prioritizes concurrencyLimit method over ConcurrencyLimit attribute', function () {
    Cache::shouldReceive('withoutOverlapping')
        ->once()
        ->withArgs(function ($key, $callback, $lockFor, $waitFor) {
            return (bool) expect($key)->toBe('method-key')
                ->and($lockFor)->toBe(0)
                ->and($waitFor)->toBe(10);
        })
        ->andReturnUsing(fn ($key, $callback) => $callback());

    $result = UseConcurrencyLimitByMethodAndAttributeExecutable::sync()->execute('method wins');

    expect($result)->toBe('method wins');
});
