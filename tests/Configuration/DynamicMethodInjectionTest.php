<?php

declare(strict_types=1);

use Havn\Executable\Config\QueueableConfig;
use Havn\Executable\Jobs\ExecutableJob;
use Havn\Executable\Jobs\ExecutableUniqueJob;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Queue;
use Workbench\App\Executables\DynamicInjection\ConfigNameCollisionExecutable;
use Workbench\App\Executables\DynamicInjection\ConfigNameCollisionWithRenamedQueueableConfigExecutable;
use Workbench\App\Executables\DynamicInjection\FullSignatureExecutable;
use Workbench\App\Executables\DynamicInjection\OptionalParametersExecutable;
use Workbench\App\Executables\DynamicInjection\PartialConfigureExecutable;
use Workbench\App\Executables\DynamicInjection\PartialFailedExecutable;
use Workbench\App\Executables\DynamicInjection\PartialSignatureExecutable;
use Workbench\App\Executables\DynamicInjection\PartialUniqueIdExecutable;
use Workbench\App\Executables\DynamicInjection\ServiceInjectionExecutable;
use Workbench\App\Executables\DynamicInjection\SubclassFailedExecutable;
use Workbench\App\Executables\DynamicInjection\ThrowableNameCollisionExecutable;
use Workbench\App\Executables\DynamicInjection\ZeroParameterExecutable;

afterEach(function () {
    unset(
        $_SERVER['_partial_signature_display_name_order_id'],
        $_SERVER['_partial_signature_tags_amount'],
        $_SERVER['_partial_signature_middleware_order_id'],
        $_SERVER['_partial_signature_tries_amount'],
        $_SERVER['_service_injection_cache_instance'],
        $_SERVER['_service_injection_order_id'],
        $_SERVER['_service_injection_tags_cache_instance'],
        $_SERVER['_service_injection_tags_amount'],
        $_SERVER['_partial_failed_exception'],
        $_SERVER['_partial_failed_order_id'],
        $_SERVER['_partial_configure_config'],
        $_SERVER['_partial_configure_amount'],
        $_SERVER['_zero_param_display_name_called'],
        $_SERVER['_zero_param_tags_called'],
        $_SERVER['_optional_params_prefix'],
        $_SERVER['_optional_params_version'],
        $_SERVER['_partial_unique_id_order_id'],
        $_SERVER['_subclass_failed_exception'],
        $_SERVER['_subclass_failed_order_id'],
        $_SERVER['_full_sig_failed_exception'],
        $_SERVER['_full_sig_failed_order_id'],
        $_SERVER['_full_sig_failed_amount'],
        $_SERVER['_config_collision_config'],
        $_SERVER['_config_collision_amount'],
        $_SERVER['_config_collision_renamed_queue_config'],
        $_SERVER['_config_collision_renamed_config'],
        $_SERVER['_throwable_collision_throwable'],
        $_SERVER['_throwable_collision_amount'],
    );
});

describe('named resolution with partial signatures', function () {
    beforeEach(function () {
        Queue::fake();
    });

    it('resolves displayName with a single named parameter from execute payload', function () {
        PartialSignatureExecutable::onQueue()->execute('ORD-123', 500, 'USD');

        Queue::assertPushed(function (ExecutableJob $job) {
            return expect($job->displayName)->toBe('order-ORD-123');
        });
    });

    it('resolves tags method with a partial named parameter', function () {
        PartialSignatureExecutable::onQueue()->execute('ORD-123', 500, 'USD');

        Queue::assertPushed(function (ExecutableJob $job) {
            return expect($job->tags())->toBe(['amount:500']);
        });
    });

    it('resolves middleware method with a partial named parameter', function () {
        PartialSignatureExecutable::onQueue()->execute('ORD-123', 500, 'USD');

        Queue::assertPushed(function (ExecutableJob $job) {
            return expect($job->middleware())->toBe(['order-middleware:ORD-123']);
        });
    });

    it('resolves uniqueId method with a partial named parameter', function () {
        PartialUniqueIdExecutable::onQueue()->execute('ORD-123', 500);

        Queue::assertPushed(function (ExecutableUniqueJob $job) {
            return expect($job->uniqueId)->toContain('ORD-123');
        });
    });

    it('resolves tries method with a partial named parameter for high amount', function () {
        PartialSignatureExecutable::onQueue()->execute('ORD-123', 1500, 'USD');

        Queue::assertPushed(function (ExecutableJob $job) {
            return expect($job->tries)->toBe(5);
        });
    });

    it('resolves tries method with a partial named parameter for low amount', function () {
        PartialSignatureExecutable::onQueue()->execute('ORD-456', 200, 'EUR');

        Queue::assertPushed(function (ExecutableJob $job) {
            return expect($job->tries)->toBe(3);
        });
    });
});

describe('service injection in config methods', function () {
    beforeEach(function () {
        Queue::fake();
    });

    it('resolves both a container service and a named execute parameter in displayName', function () {
        ServiceInjectionExecutable::onQueue()->execute('ORD-789', 250);

        Queue::assertPushed(function (ExecutableJob $job) {
            expect($_SERVER['_service_injection_cache_instance'])->toBeInstanceOf(Repository::class)
                ->and($_SERVER['_service_injection_order_id'])->toBe('ORD-789');

            return expect($job->displayName)->toBe('cached-order-ORD-789');
        });
    });

    it('resolves both a container service and a named execute parameter in tags', function () {
        ServiceInjectionExecutable::onQueue()->execute('ORD-789', 250);

        Queue::assertPushed(function (ExecutableJob $job) {
            expect($job->tags())->toBe(['amount:250']);

            // tags() is called lazily, so $_SERVER values are set after the call above
            expect($_SERVER['_service_injection_tags_cache_instance'])->toBeInstanceOf(Repository::class)
                ->and($_SERVER['_service_injection_tags_amount'])->toBe(250);

            return true;
        });
    });
});

describe('configure with QueueableConfig and partial execute params', function () {
    beforeEach(function () {
        Queue::fake();
    });

    it('receives both config object and named amount from execute payload', function () {
        PartialConfigureExecutable::onQueue()->execute('ORD-100', 1500, 'GBP');

        Queue::assertPushed(function (ExecutableJob $job) {
            expect($_SERVER['_partial_configure_config'])->toBeInstanceOf(QueueableConfig::class)
                ->and($_SERVER['_partial_configure_amount'])->toBe(1500)
                ->and($job->tries)->toBe(5);

            return true;
        });
    });

    it('applies configure hook with lower amount', function () {
        PartialConfigureExecutable::onQueue()->execute('ORD-200', 500, 'EUR');

        Queue::assertPushed(function (ExecutableJob $job) {
            expect($_SERVER['_partial_configure_amount'])->toBe(500)
                ->and($job->tries)->toBe(3);

            return true;
        });
    });
});

describe('configure when execute() param name collides with QueueableConfig param name', function () {
    beforeEach(function () {
        Queue::fake();
    });

    it('injects QueueableConfig by type even when execute has a $config string parameter', function () {
        ConfigNameCollisionExecutable::onQueue()->execute('some-config-value', 1500);

        Queue::assertPushed(function (ExecutableJob $job) {
            expect($_SERVER['_config_collision_config'])->toBeInstanceOf(QueueableConfig::class)
                ->and($_SERVER['_config_collision_amount'])->toBe(1500)
                ->and($job->tries)->toBe(7);

            return true;
        });
    });

    it('passes execute $config string to configure when QueueableConfig uses a different name', function () {
        ConfigNameCollisionWithRenamedQueueableConfigExecutable::onQueue()->execute('my-config-string', 1500);

        Queue::assertPushed(function (ExecutableJob $job) {
            expect($_SERVER['_config_collision_renamed_queue_config'])->toBeInstanceOf(QueueableConfig::class)
                ->and($_SERVER['_config_collision_renamed_config'])->toBe('my-config-string')
                ->and($job->tries)->toBe(9);

            return true;
        });
    });
});

describe('failed with Throwable and partial execute params', function () {
    it('receives both exception and named orderId from execute payload', function () {
        try {
            PartialFailedExecutable::onQueue()->execute('ORD-FAIL', 999, 'USD');
        } catch (Throwable $e) {
            // exception expected
        }

        expect($_SERVER['_partial_failed_exception'])->toBeInstanceOf(Throwable::class)
            ->and($_SERVER['_partial_failed_exception']->getMessage())->toBe('Order processing failed')
            ->and($_SERVER['_partial_failed_order_id'])->toBe('ORD-FAIL');
    });

    it('receives exception on first parameter with subclass type-hint', function () {
        try {
            SubclassFailedExecutable::onQueue()->execute('ORD-SUB', 500);
        } catch (Throwable $e) {
            // exception expected
        }

        expect($_SERVER['_subclass_failed_exception'])->toBeInstanceOf(\Exception::class)
            ->and($_SERVER['_subclass_failed_exception']->getMessage())->toBe('Subclass failure')
            ->and($_SERVER['_subclass_failed_order_id'])->toBe('ORD-SUB');
    });
});

describe('failed when execute() param name collides with Throwable param name', function () {
    it('injects Throwable instance even when execute has a $throwable string parameter', function () {
        try {
            ThrowableNameCollisionExecutable::onQueue()->execute('some-throwable-string', 1500);
        } catch (Throwable $e) {
            // exception expected
        }

        expect($_SERVER['_throwable_collision_throwable'])->toBeInstanceOf(Throwable::class)
            ->and($_SERVER['_throwable_collision_amount'])->toBe(1500);
    });
});

describe('edge cases', function () {
    beforeEach(function () {
        Queue::fake();
    });

    it('handles zero-parameter execute with zero-parameter config methods', function () {
        ZeroParameterExecutable::onQueue()->execute();

        Queue::assertPushed(function (ExecutableJob $job) {
            expect($job->displayName)->toBe('zero-param-job')
                ->and($job->tags())->toBe(['zero-param']);

            return true;
        });
    });

    it('resolves config method with mismatched parameter names using defaults', function () {
        OptionalParametersExecutable::onQueue()->execute('custom-prefix', 42);

        Queue::assertPushed(function (ExecutableJob $job) {
            // Parameter names (prefix, version) don't match execute params (orderId, amount).
            // Named map provides orderId and amount keys, but displayName expects prefix and version.
            // Since no names match, the optional defaults are used.
            expect($_SERVER['_optional_params_prefix'])->toBe('default')
                ->and($_SERVER['_optional_params_version'])->toBe(1);

            return expect($job->displayName)->toBe('default-v1');
        });
    });

    it('resolves named arguments passed in declaration order', function () {
        PartialSignatureExecutable::onQueue()->execute(orderId: 'ORD-NAMED', amount: 700, currency: 'USD');

        Queue::assertPushed(function (ExecutableJob $job) {
            return expect($job->displayName)->toBe('order-ORD-NAMED');
        });
    });

    it('resolves named arguments passed in different order', function () {
        PartialSignatureExecutable::onQueue()->execute(currency: 'EUR', amount: 800, orderId: 'ORD-REORDER');

        Queue::assertPushed(function (ExecutableJob $job) {
            expect($job->displayName)->toBe('order-ORD-REORDER')
                ->and($job->tags())->toBe(['amount:800']);

            return true;
        });
    });

    it('resolves mixed positional and named arguments', function () {
        PartialSignatureExecutable::onQueue()->execute('ORD-MIXED', amount: 900, currency: 'GBP');

        Queue::assertPushed(function (ExecutableJob $job) {
            expect($job->displayName)->toBe('order-ORD-MIXED')
                ->and($job->tags())->toBe(['amount:900']);

            return true;
        });
    });
});

describe('in-flight job backwards compatibility', function () {
    it('resolves tags with integer-keyed arguments via positional fallback', function () {
        $job = new ExecutableJob(new FullSignatureExecutable, [0 => 'ORD-OLD', 1 => 300], new QueueableConfig);

        expect($job->tags())->toBe(['order:ORD-OLD', 'amount:300']);
    });

    it('resolves failed with integer-keyed arguments via positional fallback', function () {
        $exception = new \Exception('In-flight failure');
        $job = new ExecutableJob(new FullSignatureExecutable, [0 => 'ORD-OLD', 1 => 300], new QueueableConfig);

        $job->failed($exception);

        expect($_SERVER['_full_sig_failed_exception'])->toBe($exception)
            ->and($_SERVER['_full_sig_failed_order_id'])->toBe('ORD-OLD')
            ->and($_SERVER['_full_sig_failed_amount'])->toBe(300);
    });
});
