# Changelog

All notable changes to `laravel-executable` will be documented in this file.

## v1.2.1 - 2026-03-06

- Fix container resolving optional parameters that weren't passed

## v1.2.0 - 2026-03-05

- Add concurrency limiting via `concurrencyLimit()` method or `#[ConcurrencyLimit]` attribute
- Add `#[ExecuteInTransaction]` attribute as alternative to `ShouldExecuteInTransaction` interface
- Add `PushedJob::assertHasConcurrencyLimit()` and `PushedJob::assertExecutesInTransaction()` assertions
- Improve variadic parameter support on execute()

## v1.1.0 - 2026-02-25

- Add PHPStan extension to resolve execute() return types
- Add Laravel 13 support

## v1.0.1 - 2026-02-24

- Fix destructor assertions silenced by reference cycle in with()/withArgs()

## v1.0.0 - 2026-02-23

Initial release
