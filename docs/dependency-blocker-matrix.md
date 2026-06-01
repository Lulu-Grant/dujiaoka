# 依赖阻塞矩阵

更新时间：2026-06-01

本文档用于把当前升级前最关键的依赖阻塞整理成一张可执行矩阵。

## 当前矩阵

| 依赖 | 当前用途 | 阻塞级别 | 当前状态 | 建议动作 | 备注 |
| --- | --- | --- | --- | --- | --- |
| `laravel/framework 8.83.x` | 应用主框架 | P0 | Laravel 8.83 升级实验中 | 先完成 PHP 8.1 验证，再评估 Laravel 10+ | 不直接跨到 Laravel 10 |
| `facade/ignition ^2.17` | 开发异常页 | P1 | 保留过渡 | Laravel 10+ 阶段再替换 | 当前不是 Laravel 8 硬阻塞 |
| `nunomaduro/collision ^5.11` | 控制台异常输出 | P1 | 已进入 Laravel 8 组合 | 保持当前约束 | 与 PHP 8.1 主验证绑定 |
| `dcat/laravel-admin 2.*` | 后台控制台兼容层 | P0 | 保留过渡 | 先随 Laravel 8 验证，硬阻塞时再拆认证 | 当前只承担后台登录、认证中间件、权限白名单和旧入口兼容 |
| `dcat/easy-excel` | 后台导入导出辅助 | P2 | 保留过渡 | 后续替换 | 随后台壳导入导出能力成熟后统一处理 |
| `yansongda/pay ^2.10` | 官方支付宝 / 官方微信支付 | P2 | 保留过渡 | 观察后升级 | 当前不是第一阻塞点，先以回调安全测试护栏约束 |
| `swiftmailer/swiftmailer` | 邮件发送底层 | P2 | abandoned | v3.2 替换为 Symfony Mailer | 不混入 Laravel 8 第一轮 |
| `symfony/debug` | 旧调试链 | P2 | abandoned | v3.2 清理 | 不混入 Laravel 8 第一轮 |
| `paypal/rest-api-sdk-php ^1.14` | PayPal 支付 | P1 | 已从主锁文件移除 | 已处理 | PayPal 已退役 |
| `stripe/stripe-php` | Stripe 支付 | P1 | 已从主锁文件移除 | 已处理 | Stripe 已退役 |
| `xhat/payjs-laravel ^1.6` | PayJS 支付 | P2 | 已从主锁文件移除 | 已处理 | 已随 PayJS 通道退役退出新版本 |

## 当前维护支付范围

当前只保留：

- 官方支付宝
- 官方微信
- 易支付
- Epusdt

以下支付通道已退役，不再作为升级实验、RC 或 stable-ready 的维护目标：

- PayPal
- Stripe
- Coinbase
- Mapay
- TokenPay
- Paysapi
- Vpay
- PayJS

## 仍需优先处理

### P0：后台生态阻塞

#### `dcat/laravel-admin`

原因：

- 仍参与后台登录、认证中间件、权限白名单和旧入口兼容。
- 仍是后续 Laravel 10+ 升级中的最大框架级阻塞。

建议：

- Laravel 8 阶段先随框架升级验证，不直接删除。
- 如果 Dcat 阻塞 Laravel 8 安装、启动或后台登录，再单独开 `codex/dcat-auth-bridge` 拆认证和权限白名单。

#### `dcat/easy-excel`

原因：

- 跟随 Dcat 生态，主要影响后台导入导出。

建议：

- 暂不单独处理。
- 后续随后台壳导入导出能力统一替换。

### P1：保留支付通道回调安全

原因：

- PayPal / Stripe 退役后，支付升级阻塞明显下降。
- 官方支付宝、官方微信、易支付和 Epusdt 的回调异常路径已经有测试护栏。

建议：

- 支付回调改动时继续维护签名失败、重复通知、金额不一致、订单状态重复推进等异常不触发履约测试。
- 后台批量动作不得触碰支付密钥、商户号和支付路由。

## 推荐执行顺序

1. 完成 Laravel 8.83 / PHP 8.1 主线验证。
2. 继续降低后台耦合：
   - `dcat/laravel-admin`
   - `dcat/easy-excel`
3. 继续维护保留支付通道：
   - 官方支付宝
   - 官方微信
   - 易支付
   - Epusdt
4. 单独规划 Laravel 10+ 和 Dcat 退场。

## Laravel 8 实验分支最小命令集

```bash
sh scripts/composer81-docker install --no-interaction --no-progress
sh scripts/composer81-docker check-platform-reqs
sh scripts/php81-docker artisan migrate:status --no-ansi
sh scripts/php81-docker vendor/bin/phpunit --configuration phpunit.php81.xml
PHP81_DOCKER_PORT=8031 sh scripts/serve-php81-docker
APP_URL=http://127.0.0.1:8031 ADMIN_USERNAME=admin-shell-tester ADMIN_PASSWORD=secret123 ./scripts/smoke-admin-shell
ADMIN_USERNAME=admin-shell-tester ADMIN_PASSWORD=secret123 ./scripts/smoke-admin-shell
```

2026-06-01 Laravel 8 实验记录：

- 当前分支：`codex/php81-laravel8-upgrade`。
- Laravel 版本：`8.83.29`。
- 运行时目标：PHP 8.1+。
- Composer 已升级 `laravel/framework`、`nunomaduro/collision`、`ramsey/uuid`、`vlucas/phpdotenv` 和 `dragonmantank/cron-expression`。
- Dcat 兼容层继续保留后台登录、认证 guard / provider、中间件、权限白名单和旧入口 query-preserving 跳转。
- 该实验分支是升级优先主线，不直接混入 Laravel 10+ 或 Dcat 退场。

失败回滚条件：

- Composer 无法在当前锁文件下稳定安装。
- Laravel 启动期因 Dcat、Yansongda 或旧 helper 直接崩溃。
- migration / 测试库准备失败。
- 支付关键路径或后台 smoke 失败。
- 必须删除 Dcat 兼容关键入口才能继续时，停止实验并单独规划认证桥接。
