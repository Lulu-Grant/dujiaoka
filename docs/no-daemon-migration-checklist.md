# No-Daemon Migration Checklist

## Goal

Modernize the project so that core business behavior does not depend on:

- `supervisord`
- long-running `php artisan queue:work`
- a permanently running local queue worker

The target system should still work correctly when deployed as a conventional PHP web app without a process supervisor.

## Status Snapshot

Completed:

- delayed order expiration no longer depends on queue delay
- notification and callback side effects now support synchronous-by-default execution
- project `Dockerfile` no longer starts `queue:work` or a custom `supervisord` wrapper
- Debian deployment guide now documents cron / scheduler instead of Supervisor

Still to do:

- simplify container and platform deployment docs around cron ownership
- decide whether `docker-compose.yml` should document an external scheduler sidecar or stay intentionally minimal
- continue shrinking legacy queue concepts out of the code and docs

## 1. Current Process-Coupled Areas

### Deployment and runtime assumptions

Confirmed sources:

- `Dockerfile`
- `debian_manual.md`

Previous behavior:

- `Dockerfile` started `php artisan queue:work` in the background and then launched `supervisord`
- Debian install guide explicitly required Supervisor and a permanent queue worker

Current state:

- project runtime no longer starts a background queue worker
- Debian install guide now uses cron / Laravel scheduler instructions

Why this matters:

- core behavior becomes operationally fragile
- failure modes move from request-time visibility into background process drift
- deployments become more complex than a normal PHP app should require

## 2. Queue-Dependent Jobs Currently in Use

Jobs found under `app/Jobs`:

- `ApiHook`
- `BarkPush`
- `CouponBack`
- `MailSend`
- `OrderExpired`
- `ServerJiang`
- `TelegramPush`
- `WorkWeiXinPush`

Current queue trigger points:

- `OrderProcessService::createOrder()`
  - dispatches `OrderExpired` with delay
- `OrderProcessService::completedOrder()`
  - dispatches `ServerJiang`
  - dispatches `TelegramPush`
  - dispatches `BarkPush`
  - dispatches `WorkWeiXinPush`
  - dispatches `ApiHook`
- `OrderProcessService::processManual()`
  - dispatches `MailSend`
- `OrderProcessService::processAuto()`
  - dispatches `MailSend`
- `OrderExpired::handle()`
  - dispatches `CouponBack`
- `OrderUpdated` listener
  - dispatches `MailSend`

## 3. Which Behaviors Actually Need Daemons Today

### Must not depend on a daemon

These should work synchronously or through request-safe application logic:

- create order
- complete payment
- auto fulfillment
- manual fulfillment state change
- inventory updates
- coupon usage and rollback correctness
- pay gateway validation
- order lookup and status polling

### Can become optional asynchronous enhancements

These are important but should not block order correctness:

- email sending
- bark push
- telegram push
- server jiang push
- work weixin push
- external API hook callback

### Currently the most daemon-coupled business rule

- `OrderExpired` delayed dispatch

This is the main behavior that truly assumes a queue worker is alive in the background.

## 4. Recommended Target Design

### Core principle

Core business state transitions should complete inside the request or inside explicitly triggered application commands, not inside an always-on worker.

### Target split

#### Synchronous by default

- order creation
- order completion
- fulfillment
- coupon state changes
- inventory updates
- order status changes

#### Best-effort side effects

- notifications
- webhook callbacks

These can run:

- synchronously in simple deployments
- asynchronously only when an external queue infrastructure is intentionally enabled

#### Scheduled maintenance instead of delayed jobs

Replace delayed queue expiration with a periodic command such as:

- scan all unpaid orders older than X minutes
- expire them in batch
- trigger coupon rollback in the same command path

This should be driven by:

- system cron
- platform scheduler
- container platform scheduled jobs

Not by a permanent local queue worker.

## 5. Concrete Migration Steps

### Phase A: Remove queue as a hard dependency

1. Keep `QUEUE_CONNECTION=sync` as the supported default.
2. Ensure all existing tests pass in sync mode.
3. Treat jobs as implementation details, not operational prerequisites.

### Phase B: Replace delayed expiration

Status: completed

Current problem:

- `OrderProcessService::createOrder()` dispatches `OrderExpired` with delay

Target:

- create an Artisan command that expires eligible orders
- later wire it to cron or a platform scheduler

Expected outcome:

- no background worker needed for order expiry
- business rule remains intact

### Phase C: Reclassify notifications

Status: completed

Current jobs:

- `MailSend`
- `ApiHook`
- `BarkPush`
- `TelegramPush`
- `ServerJiang`
- `WorkWeiXinPush`

Target:

- wrap them behind a notification dispatcher
- default execution mode can be sync
- optional async mode can be enabled later behind configuration

Expected outcome:

- behavior still works without queue worker
- deployment no longer depends on supervisor

### Phase D: Simplify deployment assets

Status: mostly completed

Update:

- `Dockerfile`
- `docker-compose.yml`
- `debian_manual.md`

Target:

- remove `supervisord`
- remove automatic background `queue:work`
- document cron-based expiration instead

## 6. Priority Ranking

### Highest priority

1. replace delayed `OrderExpired` queue usage
2. decouple notification dispatch from hard queue dependency
3. remove supervisor-based Docker startup

Current result:

- all three highest-priority items are now completed

### Medium priority

1. add an explicit order expiration command
2. centralize notification strategy behind one service
3. update manuals and deploy docs

### Lower priority

1. support optional external queue providers later
2. add dedicated async adapters only if future scale requires them

## 7. Recommended First Implementation Step

Start with the order expiration path.

Reason:

- it is the only business rule that currently depends on delayed queue execution
- it has a clean replacement model using scheduled scanning
- removing it weakens the project’s dependence on a permanent worker immediately

## 8. Proposed Next Refactor

Implemented:

- an order expiration command
- an expiration service method that finds and expires eligible unpaid orders
- order creation no longer schedules a delayed queue job

Follow-up:

- keep deployment docs aligned with cron-driven scheduling
- continue reducing leftover queue-centric language in non-runtime documentation
