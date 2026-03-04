<?php

declare(strict_types=1);

use Workbench\App\Executables\PurelyVariadicWithLifecycleExecutable;
use Workbench\App\Executables\VariadicWithConfigureExecutable;
use Workbench\App\Executables\VariadicWithFailedExecutable;
use Workbench\App\Executables\VariadicWithNamedLifecycleExecutable;
use Workbench\App\Executables\VariadicWithVariadicConfigureExecutable;
use Workbench\App\Executables\VariadicWithVariadicFailedExecutable;
use Workbench\App\Executables\VariadicWithVariadicLifecycleExecutable;

afterEach(function () {
    unset(
        $_SERVER['_variadic_named_lifecycle_name'],
        $_SERVER['_variadic_variadic_lifecycle_input'],
        $_SERVER['_variadic_configure_name'],
        $_SERVER['_variadic_failed_name'],
        $_SERVER['_variadic_failed_exception'],
        $_SERVER['_variadic_variadic_configure_input'],
        $_SERVER['_variadic_variadic_failed_input'],
        $_SERVER['_variadic_variadic_failed_exception'],
    );
});

it('passes named params to lifecycle method from variadic execute', function () {
    VariadicWithNamedLifecycleExecutable::test()->execute('foo', 'bar', 'baz');

    expect($_SERVER['_variadic_named_lifecycle_name'])->toBe('foo');
});

it('supports purely variadic execute with parameterless lifecycle method', function () {
    $result = PurelyVariadicWithLifecycleExecutable::test()->execute('hello', 'world');

    expect($result)->toBe('hello,world');
});

it('passes only variadic args to variadic lifecycle method', function () {
    VariadicWithVariadicLifecycleExecutable::test()->execute('foo', 'bar', 'baz');

    expect($_SERVER['_variadic_variadic_lifecycle_input'])->toBe(['bar', 'baz']);
});

it('handles variadic execute with zero variadic arguments', function () {
    $result = VariadicWithNamedLifecycleExecutable::test()->execute('foo');

    expect($result)->toBe('foo:')
        ->and($_SERVER['_variadic_named_lifecycle_name'])->toBe('foo');
});

it('passes named params to configure method from variadic execute', function () {
    $job = VariadicWithConfigureExecutable::prepare()->execute('foo', 'bar', 'baz');

    expect($_SERVER['_variadic_configure_name'])->toBe('foo')
        ->and($job->queue)->toBe('custom-foo');
});

it('passes named params to failed method from variadic execute', function () {
    try {
        VariadicWithFailedExecutable::onQueue()->execute('foo', 'bar', 'baz');
    } catch (Throwable) {
    }

    expect($_SERVER['_variadic_failed_name'])->toBe('foo')
        ->and($_SERVER['_variadic_failed_exception'])->toBeInstanceOf(Exception::class);
});

it('passes variadic args to variadic configure method from variadic execute', function () {
    $job = VariadicWithVariadicConfigureExecutable::prepare()->execute('foo', 'bar', 'baz');

    expect($_SERVER['_variadic_variadic_configure_input'])->toBe(['bar', 'baz'])
        ->and($job->queue)->toBe('variadic-bar-baz');
});

it('passes variadic args to variadic failed method from variadic execute', function () {
    try {
        VariadicWithVariadicFailedExecutable::onQueue()->execute('foo', 'bar', 'baz');
    } catch (Throwable) {
    }

    expect($_SERVER['_variadic_variadic_failed_input'])->toBe(['bar', 'baz'])
        ->and($_SERVER['_variadic_variadic_failed_exception'])->toBeInstanceOf(Exception::class);
});
