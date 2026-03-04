<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Workbench\App\Executables\PlainQueueableExecutable;
use Workbench\App\Executables\UseTransactionByAttributeExecutable;
use Workbench\App\Executables\UseTransactionByAttributeWithAttemptsExecutable;
use Workbench\App\Executables\UseTransactionByInheritedAttributeExecutable;
use Workbench\App\Executables\UseTransactionExecutable;
use Workbench\App\Executables\UseTransactionWithAttemptsExecutable;

it('does not execute in transaction in sync by default', function () {
    DB::shouldReceive('transaction')->never();

    PlainQueueableExecutable::sync()->execute('input');
});

it('does not execute in transaction on queue by default', function () {
    DB::shouldReceive('transaction')->never();

    PlainQueueableExecutable::onQueue()->execute('input');
});

it('can be executed in transaction on queue by implementing interface', function () {
    $result = null;

    DB::shouldReceive('transaction')->once()
        ->andReturnUsing(function ($callback) use (&$result) {
            return $result = $callback();
        });

    UseTransactionExecutable::onQueue()->execute('I ran in a database transaction');

    expect($result)->toBe('I ran in a database transaction');
});

it('can be executed in transaction in sync by implementing interface', function () {
    $result = null;

    DB::shouldReceive('transaction')->once()
        ->andReturnUsing(function ($callback) use (&$result) {
            return $result = $callback();
        });

    UseTransactionExecutable::sync()->execute('I ran in a database transaction');

    expect($result)->toBe('I ran in a database transaction');
});

it('defaults to 1 transaction attempt in sync', function () {
    DB::shouldReceive('transaction')->once()
        ->withArgs(fn ($callback, $attempts) => is_callable($callback) && $attempts === 1)
        ->andReturnUsing(fn ($callback) => $callback());

    UseTransactionExecutable::sync()->execute('input');
});

it('defaults to 1 transaction attempt on queue', function () {
    DB::shouldReceive('transaction')->once()
        ->withArgs(fn ($callback, $attempts) => is_callable($callback) && $attempts === 1)
        ->andReturnUsing(fn ($callback) => $callback());

    UseTransactionExecutable::onQueue()->execute('input');
});

it('respects transactionAttempts property in sync', function () {
    DB::shouldReceive('transaction')->once()
        ->withArgs(fn ($callback, $attempts) => is_callable($callback) && $attempts === 3)
        ->andReturnUsing(fn ($callback) => $callback());

    UseTransactionWithAttemptsExecutable::sync()->execute('input');
});

it('respects transactionAttempts property on queue', function () {
    DB::shouldReceive('transaction')->once()
        ->withArgs(fn ($callback, $attempts) => is_callable($callback) && $attempts === 3)
        ->andReturnUsing(fn ($callback) => $callback());

    UseTransactionWithAttemptsExecutable::onQueue()->execute('input');
});

it('executes in transaction in sync when ExecuteInTransaction attribute is used', function () {
    $result = null;

    DB::shouldReceive('transaction')->once()
        ->andReturnUsing(function ($callback) use (&$result) {
            return $result = $callback();
        });

    UseTransactionByAttributeExecutable::sync()->execute('attribute transaction');

    expect($result)->toBe('attribute transaction');
});

it('executes in transaction on queue when ExecuteInTransaction attribute is used', function () {
    $result = null;

    DB::shouldReceive('transaction')->once()
        ->andReturnUsing(function ($callback) use (&$result) {
            return $result = $callback();
        });

    UseTransactionByAttributeExecutable::onQueue()->execute('attribute transaction');

    expect($result)->toBe('attribute transaction');
});

it('defaults to 1 transaction attempt with ExecuteInTransaction attribute', function () {
    DB::shouldReceive('transaction')->once()
        ->withArgs(fn ($callback, $attempts) => is_callable($callback) && $attempts === 1)
        ->andReturnUsing(fn ($callback) => $callback());

    UseTransactionByAttributeExecutable::sync()->execute('input');
});

it('respects attempts from ExecuteInTransaction attribute', function () {
    DB::shouldReceive('transaction')->once()
        ->withArgs(fn ($callback, $attempts) => is_callable($callback) && $attempts === 3)
        ->andReturnUsing(fn ($callback) => $callback());

    UseTransactionByAttributeWithAttemptsExecutable::sync()->execute('input');
});

it('inherits ExecuteInTransaction attribute from parent class', function () {
    DB::shouldReceive('transaction')->once()
        ->withArgs(fn ($callback, $attempts) => is_callable($callback) && $attempts === 3)
        ->andReturnUsing(fn ($callback) => $callback());

    UseTransactionByInheritedAttributeExecutable::sync()->execute('inherited');
});
