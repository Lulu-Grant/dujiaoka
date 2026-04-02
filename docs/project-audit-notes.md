# Project Audit Notes

## Confirmed Technical Baseline

- Laravel version in lock file: `v6.20.42`
- Dcat Admin version in lock file: `2.0.24-beta`
- Stripe SDK version in lock file: `v7.84.0`
- PayPal SDK version in lock file: `1.14.0`
- Frontend build tool: Laravel Mix 5
- Local PHP runtime observed during audit: `8.5.4`

Relevant files:

- `composer.json`
- `composer.lock`
- `package.json`
- `webpack.mix.js`
- `Dockerfile`

## Confirmed Structural Findings

### 1. This is a monolith with heavy controller and service coupling

Evidence:

- payment entry points are concentrated under `app/Http/Controllers/Pay`
- business logic is centralized in `app/Service`
- admin logic is tightly coupled to Dcat Admin under `app/Admin`

Most notable oversized files:

- `app/Http/Controllers/Pay/StripeController.php`
- `app/Service/OrderProcessService.php`
- `app/Service/OrderService.php`

### 2. Database lifecycle is not migration-driven

Evidence:

- there is no migrations directory under `database`
- installation imports `database/sql/install.sql` directly
- installer writes environment values and installation lock files itself

Relevant files:

- `app/Http/Controllers/Home/HomeController.php`
- `database/sql/install.sql`

### 3. Tests are placeholder-only

Evidence:

- `tests/Feature/ExampleTest.php`
- `tests/Unit/ExampleTest.php`

Impact:

- framework upgrades and payment refactors currently have no business regression protection

## Confirmed Security Concerns

### P0

- A real `.env` file exists in the working tree and appears to contain an application key.
- SQL bootstrap seeds a default admin account in `database/sql/install.sql`.

Relevant files:

- `.env`
- `database/sql/install.sql`

### P1

- installer writes `.env` directly from request input
- installer executes raw SQL from file contents
- outbound API hooks use `file_get_contents` against dynamic URLs

Relevant files:

- `app/Http/Controllers/Home/HomeController.php`
- `app/Jobs/ApiHook.php`

### P1

- multiple public payment callback endpoints rely on legacy per-gateway implementations
- callback verification quality is inconsistent across providers

Relevant files:

- `routes/common/pay.php`
- `app/Http/Controllers/Pay/*.php`

## Confirmed Upgrade Blockers

### Framework blockers

- Laravel 6-era app conventions
- old mail and queue configuration assumptions
- old middleware naming and bootstrap patterns

### Package blockers

- Dcat Admin compatibility
- legacy PayPal SDK
- older Stripe integration approach
- geetest and captcha ecosystem age

### Architecture blockers

- order creation, fulfillment, expiration, notifications, and coupon handling are too centralized
- payment logic mixes orchestration and presentation
- installation flow bypasses standard Laravel migration and deployment patterns

## Immediate Recommended Work

### Sprint 1

- document one reproducible runtime path
- install Composer and verify dependency resolution on a supported PHP version
- inventory payment gateways and classify each as keep, freeze, or remove
- add first tests around order creation and completion

### Sprint 2

- separate order orchestration from fulfillment and notifications
- define a gateway interface for payment providers
- begin replacing `install.sql` with migrations and seeders

## Known Audit Limits

- dependencies were not installed during this audit because `composer` is not available in the current local environment
- application boot, HTTP requests, and PHPUnit execution have not yet been verified
- findings are based on repository inspection, lock files, and source analysis
