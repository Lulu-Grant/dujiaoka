# Dujiaoka Modernization Roadmap

## 1. Current Assessment

This repository is a legacy Laravel monolith with strong business value but high modernization pressure.

Current baseline:

- Framework: Laravel 6
- Admin panel: Dcat Admin 2.x
- Frontend build: Laravel Mix 5 / Webpack
- Runtime target in documentation: PHP 7.4 era
- Current local runtime: PHP 8.5.4
- Database bootstrap: raw SQL import via installer
- Test coverage: almost none

Primary risks:

- Framework and dependency stack are multiple generations behind.
- Payment integrations are tightly coupled to controllers and legacy SDKs.
- Installation and deployment are not reproducible in a modern CI/CD workflow.
- Sensitive operational data is too close to the repository workflow.
- No meaningful regression suite protects core order and payment behavior.

## 2. Strategic Goal

The project should evolve from an abandoned, environment-sensitive legacy application into a maintainable commerce platform with:

- repeatable setup
- testable business flows
- replaceable payment providers
- safer secrets handling
- an upgrade path toward newer Laravel and PHP versions

## 3. Recommended Migration Strategy

Do not attempt a direct jump to the latest Laravel version first.

Recommended order:

1. Stabilize and secure the current system.
2. Extract and test the core business domain.
3. Replace fragile infrastructure patterns.
4. Upgrade framework and tooling in controlled steps.
5. Reassess whether admin and frontend should remain in the monolith.

## 4. Change Log Discipline

From this point onward, every important modernization milestone should be recorded in:

- [refactor-upgrade-log.md](/Users/apple/Documents/dujiaoshuka/docs/refactor-upgrade-log.md)
- execution should follow [rectification-execution-plan.md](/Users/apple/Documents/dujiaoshuka/docs/rectification-execution-plan.md) as the default phase-by-phase operating plan

Important milestones include:

- new assessment conclusions
- runtime baseline changes
- test coverage breakthroughs
- service extraction and domain refactors
- deployment model changes
- security remediation steps
- framework or dependency upgrade blockers
- major bug fixes discovered during modernization

## 5. Phase Plan

### Phase 0: Freeze and Baseline

Goal: make the current system observable and reproducible before changing behavior.

Tasks:

- create a local developer setup guide for this fork
- document required PHP, Composer, Node, Redis, MySQL versions
- confirm whether the app still boots on a historically correct runtime
- inventory all payment gateways and mark each as active, deprecated, or broken
- capture the main order lifecycle from order creation to fulfillment
- record current routes, cron jobs, queue workers, and admin entry points

Deliverables:

- environment baseline document
- payment gateway inventory
- order lifecycle diagram

Exit criteria:

- the team can boot the project in one repeatable way
- we know which business flows still work

### Phase 1: Security and Repository Hygiene

Goal: remove obvious operational risk without changing product behavior.

Tasks:

- stop tracking real environment files and rotate any leaked secrets
- audit default admin credentials and installation defaults
- review installer behavior that writes `.env` and imports SQL directly
- review webhook and callback endpoints for signature and replay protection
- audit outbound hook jobs and external notification requests
- confirm file upload exposure and public asset write paths

Priority issues already identified:

- tracked `.env` file in repository
- seeded admin account in `database/sql/install.sql`
- raw installer SQL import instead of structured migrations
- legacy payment SDK usage

Deliverables:

- secret rotation checklist
- security hardening backlog
- repository hygiene rules

Exit criteria:

- no real secrets remain in git history for active environments
- default credentials and unsafe bootstrap defaults are removed or disabled

### Phase 2: Test Harness for Core Commerce Flows

Goal: create a safety net before deeper refactors.

Minimum business flows to cover:

- create order
- validate goods and stock
- apply coupon
- choose payment gateway
- complete payment
- auto-fulfill card inventory
- expire unpaid order
- search order by number and email

Recommended test layers:

- unit tests for price calculation and order state transitions
- feature tests for public order endpoints
- integration tests for payment callback validation where possible

Deliverables:

- first business test suite
- fixtures for goods, cards, coupons, and orders

Exit criteria:

- core order lifecycle has automated regression coverage
- refactors can be verified without manual-only testing

### Phase 3: Domain Refactor

Goal: reduce coupling in the core commerce logic.

Current pain points:

- `OrderProcessService` owns too many responsibilities
- payment logic is scattered across controller classes
- controller code mixes validation, orchestration, HTML rendering, and gateway handling

Refactor targets:

- split order creation, payment confirmation, fulfillment, and notifications into separate services
- define explicit order state transition rules
- extract a payment gateway contract and gateway adapters
- move large inline payment page generation out of controllers
- isolate external notifications behind dedicated clients

Suggested target modules:

- `Domain/Order`
- `Domain/Fulfillment`
- `Domain/Payment`
- `Infrastructure/Payments`
- `Infrastructure/Notifications`

Exit criteria:

- core business logic is no longer trapped in a few oversized classes
- adding or removing a payment provider becomes localized work

### Phase 4: Data and Installation Modernization

Goal: replace one-shot installer behavior with standard application lifecycle patterns.

Tasks:

- convert `database/sql/install.sql` into Laravel migrations and seeders
- replace installer-driven `.env` writing with environment-based deployment
- define one supported bootstrap path for local, staging, and production
- separate seed data from operational bootstrap data

Exit criteria:

- database schema is migration-driven
- installation is scriptable and environment-safe

### Phase 5: Framework and Toolchain Upgrade

Goal: upgrade safely after the app is testable and better structured.

Suggested sequence:

1. modernize code to reduce Laravel 6 assumptions
2. remove or replace packages that block framework upgrades
3. upgrade PHP target deliberately
4. move from Mix/Webpack to Vite
5. upgrade Laravel stepwise with test verification at each stage

Known upgrade blockers:

- Dcat Admin version compatibility
- legacy PayPal SDK
- old controller patterns and facades used by payment code
- installer assumptions tied to old config structure

Exit criteria:

- app runs on a modern supported PHP/Laravel baseline
- frontend assets build with supported tooling

### Phase 6: Admin and Frontend Decision Point

Goal: decide the long-term product shape after core stabilization.

Options:

- keep the monolith and modernize the admin in place
- replace only the admin panel
- gradually split public storefront and back office from the monolith

Decision factors:

- how much Dcat Admin blocks future upgrades
- how much custom admin behavior exists
- whether the storefront needs a redesigned public API

## 5. Priority Backlog

Immediate priority:

1. verify a reproducible runtime with Composer and dependencies installed
2. rotate secrets and stop relying on tracked environment files
3. write the first order lifecycle tests
4. document active payment providers and retire dead ones

High priority:

1. break up `OrderProcessService`
2. define a payment gateway abstraction
3. replace SQL installer with migrations

Medium priority:

1. migrate frontend build to Vite
2. reconsider Dcat Admin as the long-term back office
3. improve observability, logging, and queue monitoring

## 6. Architecture Recommendation

Short term recommendation:

- keep the current monolith running
- avoid a rewrite-first strategy
- prioritize domain extraction and test coverage

Long term recommendation:

- treat this repository as a migration source, not necessarily the final architecture
- once business rules are extracted and covered by tests, reevaluate whether the storefront and admin should remain coupled

## 7. First Execution Sprint

Recommended Sprint 1 scope:

- add project audit notes
- confirm runtime and dependency installation path
- create test fixtures and the first order tests
- build a payment provider inventory table
- start a security cleanup checklist

Definition of done for Sprint 1:

- one developer can set up the app locally
- core order creation path has initial automated coverage
- the team knows which payment gateways are safe to keep, freeze, or remove
