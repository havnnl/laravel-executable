# Single-task action classes for Laravel.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/havn/laravel-executable.svg?style=flat-square)](https://packagist.org/packages/havn/laravel-executable)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/havnnl/laravel-executable/php-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/havnnl/laravel-executable/actions?query=workflow%3Aphp-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/havnnl/laravel-executable/php-code-style-fixer.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/havnnl/laravel-executable/actions?query=workflow%3Aphp-code-style-fixer+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/havn/laravel-executable.svg?style=flat-square)](https://packagist.org/packages/havn/laravel-executable)

Fully queueable, with a testing API that makes you like writing tests more than the actual code.

Read the full documentation at  [docs.havn.nl](https://docs.havn.nl/laravel-executable/).

## Installation

```bash
composer require havn/laravel-executable
```

**Requirements:** PHP 8.3+ | Laravel 12+

## Quick Example

A plain PHP class with a trait and an `execute()` method:

```php
use Havn\Executable\QueueableExecutable;

class ProcessPayment
{
    use QueueableExecutable;

    public function __construct(
        private PaymentGateway $gateway,
    ) {}

    public function execute(Payment $payment): void
    {
        $this->gateway->charge($payment);
        
        $payment->update(['status' => 'completed']);
    }
}
```

Four execution modes:

```php
// Sync — runs immediately, returns the result
ProcessPayment::sync()->execute($payment);

// Queue — dispatches to the queue
ProcessPayment::onQueue()->execute($payment);

// Prepare — returns a job without dispatching (for chains and batches)
$job = ProcessPayment::prepare()->execute($payment);

// Test — runs the real code in a testable context
ProcessPayment::test()->execute($payment);
```

Queue configuration at dispatch time:

```php
ProcessPayment::onQueue('high-priority')
    ->delay(60)
    ->tries(3)
    ->execute($payment);
```

## Testing

Mock, spy, or assert. All built in:

```php
// Mock
ProcessPayment::mock()
    ->shouldExecute()
    ->with($payment)
    ->once();

// Spy
ProcessPayment::spy();
ProcessPayment::sync()->execute($payment);
ProcessPayment::assert()->executed()->with($payment);

// Queue assertions
Queue::fake();
ProcessPayment::onQueue()->execute($payment);
ProcessPayment::assert()->queued()->on('high-priority')->with($payment)->once();
```

## Contributing

Contributions are welcome. Please see [CONTRIBUTING](https://github.com/havnnl/.github/blob/main/CONTRIBUTING.md) for
details.

## Security

Please review [our security policy](../../security/policy) for reporting security vulnerabilities.

## License

The MIT License (MIT). See [License File](LICENSE.md) for details.

## Credits

- [Henk Koop](https://github.com/henkkoop)
- [All Contributors](../../contributors)

---
Made with care by [Havn](https://havn.nl)
