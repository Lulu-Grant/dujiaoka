# Laravel 桥接升级路线

更新时间：2026-06-01

本文档记录 PHP 8.1 打通后，Laravel 7 继续升级到 Laravel 8 的真实阻塞和执行顺序。

## 当前结论

- PHP 8.1 已可运行 Composer 平台检查、artisan、全量 PHPUnit 和后台 smoke。
- `codex/php81-laravel8-upgrade` 已将升级分支推进到 Laravel 8.83。
- 当前分支放弃 PHP 7.4 主线兼容，默认运行时改为 PHP 8.1+。
- Dcat 暂未删除；先带着 `dcat/laravel-admin` 与 `dcat/easy-excel` 验证 Laravel 8。
- 后续仍不要一次性跨 Laravel 8 直接进入 Laravel 10。

## 已验证命令

```bash
sh scripts/composer81-docker check-platform-reqs
sh scripts/php81-docker artisan migrate:status --no-ansi
sh scripts/php81-docker vendor/bin/phpunit --configuration phpunit.php81.xml
PHP81_DOCKER_PORT=8031 sh scripts/serve-php81-docker
APP_URL=http://127.0.0.1:8031 ADMIN_USERNAME=admin-shell-tester ADMIN_PASSWORD=secret123 ./scripts/smoke-admin-shell
```

当前验证目标：

- PHP 8.1 PHPUnit：`OK (417 tests, 4292 assertions)`
- PHP 8.1 后台 smoke：通过
- Laravel 版本：`8.83.29`

## Laravel 8 依赖处理

已处理：

- `laravel/framework` 升级到 `^8.83`。
- `nunomaduro/collision` 升级到 `^5.11`。
- `dragonmantank/cron-expression` 升级到 3.x。
- `ramsey/uuid` 升级到 4.x。
- `vlucas/phpdotenv` 升级到 5.6.x。
- Symfony 组件链保持在可安装范围。
- `psr/log` 固定为 `^1.1`，避免不必要扩大接口签名变化。
- Composer `config.platform.php` 固定为 `8.1.34`。

仍需观察：

- `facade/ignition` 仍保留 `^2.17`，后续 Laravel 10+ 再处理。
- `dcat/laravel-admin` 仍参与后台登录、认证、中间件和权限白名单。
- `dcat/easy-excel` 仍服务后台导入导出能力。
- `yansongda/pay` 仍服务官方支付宝 / 官方微信。

## 包处理顺序

1. Laravel 8 实验链：
   - `dragonmantank/cron-expression`
   - `ramsey/uuid`
   - `vlucas/phpdotenv`
   - Symfony contracts
2. 后台兼容层：
   - `dcat/laravel-admin`
   - `dcat/easy-excel`
3. 支付保留链：
   - `yansongda/pay`

## 实验分支验收

Laravel 8 升级分支必须满足：

- Composer install / package discovery 通过。
- `artisan migrate:status` 通过。
- PHP 8.1 全量 PHPUnit 通过。
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
