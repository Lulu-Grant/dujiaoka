# Legacy Runtime Baseline

## Purpose

This document records the first verified local runtime baseline that can boot the legacy application successfully enough for further assessment and migration work.

## Verified Runtime

- PHP binary: `/opt/homebrew/opt/php@7.4/bin/php`
- PHP version: `7.4.33`
- Composer binary: `/opt/homebrew/bin/composer`
- MariaDB service: local Homebrew service
- Redis service: local Homebrew service

Important:

- the system default PHP is still `8.5.4`
- the project should use the explicit PHP 7.4 binary for legacy verification work

## Why This Baseline Matters

The application cannot install or boot correctly on the current default PHP `8.5.4`.

By contrast, the application does boot under PHP `7.4`, which confirms that:

- the legacy codebase is still restorable
- the current modernization effort can proceed from a known-good historical runtime
- future upgrade work can be compared against an actual baseline rather than static guesses

## Verified Commands

### PHP Version

```bash
/opt/homebrew/opt/php@7.4/bin/php -v
```

Result:

- PHP `7.4.33`

### Laravel Version

```bash
/opt/homebrew/opt/php@7.4/bin/php artisan --version
```

Result:

- Laravel Framework `6.20.42`

### Route Registration

```bash
/opt/homebrew/opt/php@7.4/bin/php artisan route:list
```

Result:

- route registration succeeds
- public storefront, admin routes, install routes, and payment routes are all visible

### Platform Requirements

```bash
/opt/homebrew/opt/php@7.4/bin/php /opt/homebrew/bin/composer check-platform-reqs
```

Result:

- platform requirements pass under PHP `7.4`

## Test Verification

### PHPUnit

```bash
/opt/homebrew/opt/php@7.4/bin/php vendor/bin/phpunit --testsuite=Feature
```

Result:

- PHPUnit runs successfully against the restored local baseline
- the example feature test passes once localhost database access is allowed

Current test database baseline:

- database: `dujiaoka_test`
- host: `127.0.0.1`
- user: `dujiaoka`

Supporting changes:

- local `install.lock` created
- legacy SQL imported into local app and test databases
- `phpunit.xml` moved from in-memory SQLite to MySQL for this legacy schema
- `site_url()` hardened so missing `SERVER_PORT` no longer crashes test requests

## Current Limitation

The project is now bootable in an installed local legacy state, but it still relies on a restored historical runtime and imported SQL schema.

Missing baseline prerequisites still include:

- business-focused test fixtures beyond the placeholder feature test
- a repeatable seed/reset workflow for the MySQL test database
- cleanup of remaining legacy assumptions in config and installation flow

## Recommended Usage

For legacy verification work, prefer commands like:

```bash
/opt/homebrew/opt/php@7.4/bin/php artisan <command>
/opt/homebrew/opt/php@7.4/bin/php /opt/homebrew/bin/composer <command>
```

## Recommended Next Steps

1. build a repeatable legacy setup script or guide using PHP `7.4`
2. decide whether to create a local installed fixture state for tests
3. start writing real business tests on top of the restored baseline
4. only after baseline verification, begin dependency replacement and framework upgrade work
