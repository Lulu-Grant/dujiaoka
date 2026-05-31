# Laravel 桥接升级路线

更新时间：2026-06-01

本文档记录 PHP 8.1 打通后，Laravel 6 继续升级到桥接版本前的真实阻塞和执行顺序。

## 当前结论

- PHP 8.1 已可运行 Composer 平台检查、artisan、全量 PHPUnit 和后台 smoke。
- `codex/laravel7-bridge-experiment` 已将实验分支推进到 Laravel 7.30.7。
- 当前实验分支在 PHP 7.4 与 PHP 8.1 下均可通过全量 PHPUnit。
- 推荐桥接顺序仍是先 Laravel 7，再评估 Laravel 8；不要从 Laravel 6 直接跳到 Laravel 10。

## 已验证命令

```bash
sh scripts/composer81-docker check-platform-reqs
sh scripts/php81-docker artisan migrate:status --no-ansi
sh scripts/php81-docker vendor/bin/phpunit --configuration phpunit.php81.xml
PHP81_DOCKER_PORT=8031 sh scripts/serve-php81-docker
APP_URL=http://127.0.0.1:8031 ADMIN_USERNAME=admin-shell-tester ADMIN_PASSWORD=secret123 ./scripts/smoke-admin-shell
```

当前验证结果：

- PHP 8.1 PHPUnit：`OK (414 tests, 4254 assertions)`
- PHP 7.4 PHPUnit：`OK (414 tests, 4254 assertions)`
- PHP 8.1 后台 smoke：通过
- PHP 7.4 后台 smoke：通过
- Laravel 版本：`7.30.7`

## Composer dry-run 阻塞

### Laravel 7

执行：

```bash
sh scripts/composer81-docker why-not laravel/framework '7.*'
sh scripts/composer81-docker require laravel/framework:^7.30 --with-all-dependencies --dry-run --no-interaction --no-progress
```

已处理：

- `laravel/framework` 升级到 `^7.30`。
- `facade/ignition` 升级到 `^2.17`。
- `nunomaduro/collision` 升级到 `^4.3`。
- Symfony 组件链进入 5.4，并将 contracts / string / translation 约束在 PHP 7.4 可解析范围。
- `vlucas/phpdotenv` 升级到 4.x。
- `ramsey/uuid` 固定为 `4.7.6`，`brick/math` 固定到 0.12 线，避免 PHP 8-only 语法进入实验分支。
- `psr/log` 固定为 `^1.1`，避免 PHP 8-only 接口签名进入实验分支。

### Laravel 8

执行：

```bash
sh scripts/composer81-docker why-not laravel/framework '8.*'
sh scripts/composer81-docker require laravel/framework:^8.83 --with-all-dependencies --dry-run --no-interaction --no-progress
```

后续阻塞：

- Laravel 8 要求 Symfony 5.4 组件链。
- `dragonmantank/cron-expression` 需要从 2.x 升到 3.x。
- `ramsey/uuid` 需要从 3.x 升到 4.x。
- `vlucas/phpdotenv` 需要升级到 5.4+。
- Laravel 8 仍需要重新评估当前为 PHP 7.4 双运行时保留的 `ramsey/uuid`、`psr/log` 和 Symfony contracts 约束。

## 包处理顺序

1. 后台兼容层：
   - `dcat/laravel-admin`
   - `dcat/easy-excel`
2. 支付保留链：
   - `yansongda/pay`
3. Laravel 8 实验链：
   - `dragonmantank/cron-expression`
   - `ramsey/uuid`
   - `vlucas/phpdotenv`
   - Symfony contracts

## 实验分支验收

Laravel 桥接实验分支必须满足：

- Composer install / package discovery 通过。
- `artisan migrate:status` 通过。
- PHP 7.4 全量 PHPUnit 通过。
- PHP 8.1 全量 PHPUnit 通过。
- PHP 7.4 后台 smoke 通过。
- PHP 8.1 后台 smoke 通过。
- 保留支付通道异常路径测试通过。
- Dcat 旧入口 query-preserving 跳转测试通过。
- 不恢复 PayPal / Stripe / Coinbase / Mapay / TokenPay / PayJS / Vpay / Paysapi。

## 停止条件

遇到以下情况时停止实验，不混入主线：

- 必须删除 `config/admin.php` 或 `routes/admin/routes.php` 才能继续。
- Dcat 登录、认证、中间件或权限白名单不可用。
- 支付回调 URL 语义需要变更。
- 需要恢复退役支付通道。
- 需要一次性跨 Laravel 7 / 8 直接进入 Laravel 10。
