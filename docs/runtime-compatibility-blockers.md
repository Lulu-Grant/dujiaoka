# Runtime Compatibility Blockers

## Audit Context

This file records the first verified runtime blockers discovered while attempting to install dependencies on the local machine.

Observed local environment:

- PHP: `8.5.4`
- Composer: `2.9.5`

Command executed:

```bash
composer install --no-interaction
```

## Verified Result

Dependency installation fails before application boot because the locked dependency set is not compatible with PHP `8.5.4`.

Additional verification was performed with:

```bash
composer install --no-interaction --ignore-platform-reqs
```

This allowed dependencies to be downloaded, but the application still failed during Composer's Laravel bootstrapping step.

## Confirmed Blocking Packages

### 1. `bacon/bacon-qr-code`

- Locked version: `1.0.3`
- Constraint issue: requires PHP `^5.4|^7.0`
- Direct impact: blocks `simplesoftwareio/simple-qrcode 2.0.0`

### 2. `germey/geetest`

- Locked version: `v3.1.0`
- Constraint issue: requires PHP `^7.3`
- Direct impact: blocks geetest integration under current PHP

### 3. `phpspec/prophecy`

- Locked version: `1.13.0`
- Constraint issue: supports `^7.2 || ~8.0, <8.1`
- Direct impact: blocks dev dependency installation and test environment setup

### 4. `simplesoftwareio/simple-qrcode`

- Locked version: `2.0.0`
- Constraint issue: depends on `bacon/bacon-qr-code 1.0.*`
- Direct impact: keeps QR code generation chain on a legacy PHP-incompatible path

## What This Means

The project currently does not have a viable direct path to install on a modern PHP 8.5 runtime using the existing lock file.

Even with platform requirements ignored, the app still fails at runtime bootstrap.

Verified failure point:

- `artisan package:discover`
- `artisan --version`
- `artisan route:list`

Observed cause:

- deprecation notices from legacy Symfony Console, Symfony Debug, Dotenv, Guzzle Promise, and Laravel 6 internals are escalated into fatal errors under PHP `8.5`
- deprecated `PDO::MYSQL_ATTR_SSL_CA` usage also appears during bootstrap

This confirms:

- the repository needs either a legacy runtime baseline or a dependency modernization pass before normal local execution
- framework upgrade planning cannot start from "latest PHP first"
- test automation setup depends on resolving package compatibility first
- forced dependency installation alone is not enough to make the app boot on PHP `8.5`

## Recommended Next Step Options

### Option A: Establish a legacy runtime baseline first

Use an older PHP runtime compatible with the lock file, likely in the PHP 7.4 or early PHP 8.0 range, to:

- install dependencies
- boot the application
- capture current behavior
- write first regression tests

Best for:

- preserving behavior before modernization
- reducing unknowns in the current business flow

### Option B: Begin dependency modernization before runtime verification

Replace or upgrade the blocking packages and then attempt installation on a newer PHP runtime.

Best for:

- accelerating modernization
- reducing investment in outdated runtime tooling

Risks:

- harder to distinguish existing business issues from upgrade regressions

## Recommendation

Prefer Option A first if the goal is controlled modernization.

Reason:

The project has minimal tests and heavy payment coupling. Establishing one known-good legacy runtime gives us a safer baseline before package replacement and framework upgrades.
