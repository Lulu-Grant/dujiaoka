# 依赖阻塞矩阵

更新时间：2026-06-01

本文档用于把当前升级前最关键的依赖阻塞整理成一张可执行矩阵。

## 当前矩阵

| 依赖 | 当前用途 | 阻塞级别 | 当前状态 | 建议动作 | 备注 |
| --- | --- | --- | --- | --- | --- |
| `laravel/framework 7.30.7` | 应用主框架 | P0 | Laravel 7 桥接实验通过，仍未标记 stable-ready | 继续做兼容层与支付链验收，再评估 Laravel 8 | 不在 beta.2 / RC 直接跳 Laravel 8/10 |
| `facade/ignition ^2.17` | 开发异常页 | P1 | Laravel 7 实验分支已处理 | 保持当前约束 | 只作为桥接实验组合固定，不是最终升级目标 |
| `nunomaduro/collision ^4.3` | 控制台异常输出 | P1 | Laravel 7 实验分支已处理 | 保持当前约束 | 与 PHP 7.4 / 8.1 双运行时验收绑定 |
| `dcat/laravel-admin 2.*` | 后台控制台兼容层 | P0 | 保留过渡 | 继续降耦合、最终替换 | 当前只承担登录、认证、中间件、权限白名单和旧入口兼容 |
| `dcat/easy-excel` | 后台导入导出辅助 | P2 | 保留过渡 | 后续替换 | 随后台壳导入导出能力成熟后统一处理 |
| `yansongda/pay ^2.10` | 官方支付宝 / 官方微信支付 | P2 | 保留过渡 | 观察后升级 | 当前不是第一阻塞点，先以回调安全测试护栏约束 |
| `germey/geetest ^3.1` | 行为验证 | P0 | 已从主锁文件移除 | 已处理 | 已退出前台下单主路径、路由、中间件与后台设置入口 |
| `simplesoftwareio/simple-qrcode 2.0.0` | 二维码生成 | P0 | 已从主锁文件移除 | 已处理 | 已改为前端本地 JS 生成二维码 |
| `bacon/bacon-qr-code 1.0.3` | QRCode 底层依赖 | P0 | 已随上层退出 | 已处理 | 已随 `simple-qrcode` 一起移除 |
| `paypal/rest-api-sdk-php ^1.14` | PayPal 支付 | P1 | 已从主锁文件移除 | 已处理 | PayPal 已退役 |
| `stripe/stripe-php` | Stripe 支付 | P1 | 已从主锁文件移除 | 已处理 | Stripe 已退役 |
| `xhat/payjs-laravel ^1.6` | PayJS 支付 | P2 | 已从主锁文件移除 | 已处理 | 已随 PayJS 通道退役退出新版本 |
| `phpspec/prophecy 1.13.0` | 测试依赖链 | P0 | 已从主锁文件移除 | 已处理 | 已通过升级 `phpunit/phpunit` 到 9.6.34 退出主依赖链 |

## beta.2 阻塞状态口径

| 依赖 | beta.2 状态 | 第一动作 | 退出条件 |
| --- | --- | --- | --- |
| `laravel/framework` | Laravel 7 桥接实验已通过 | 保留 PHP 7.4 兼容验证，同时用 PHP 8.1 Docker 工具链持续验证 | Dcat 兼容层、支付保留链、CI 与文档验收后，作为可评审合并候选 |
| `dcat/laravel-admin` | 保留过渡 | 继续保持后台壳主承载，Dcat 只做登录、认证、中间件、权限白名单和旧入口兼容 | 后台认证、中间件、权限白名单有替代实现后，再评估删除 |
| `dcat/easy-excel` | 保留过渡 | 跟随后台导入导出能力统一替换，不单独抢跑 | 导入导出服务边界完全脱离 Dcat 后再替换 |
| `yansongda/pay` | 保留过渡 | 以官方支付宝 / 官方微信通知测试约束回调安全，不在 beta.2 直接替换 | 支付宝 / 微信回调异常路径测试覆盖后再决定升级或替换 |
| `paypal/rest-api-sdk-php` | 已处理 | 保持 PayPal 退役状态 | `composer why paypal/rest-api-sdk-php` 不再返回当前项目依赖链 |
| `stripe/stripe-php` | 已处理 | 保持 Stripe 退役状态 | `composer why stripe/stripe-php` 不再返回当前项目依赖链 |

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
- 仍是后续 Laravel 升级中的最大框架级阻塞。

建议：

- 当前阶段继续降耦合，不直接删除。
- 后续等后台认证、中间件和权限白名单替代方案成熟后，再整体退出。

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

- 支付回调改动时继续维护签名失败、金额不一致、重复通知、已完成订单重复推进和异常不触发履约测试。
- 后台批量动作不得触碰支付密钥、商户号和支付路由。

## 推荐执行顺序

1. 继续降低后台耦合：
   - `dcat/laravel-admin`
   - `dcat/easy-excel`
2. 继续维护保留支付通道：
   - 官方支付宝
   - 官方微信
   - 易支付
   - Epusdt
3. 基于 PHP 8.1 已通过的结果，设计 Laravel 桥接版本路线。

## Laravel 桥接路线

详见 [laravel-bridge-upgrade-plan.md](/Users/apple/Documents/dujiaoshuka/docs/laravel-bridge-upgrade-plan.md)。

当前 Laravel 7 实验结论：

- Laravel 7 第一阻塞链已经在 `codex/laravel7-bridge-experiment` 处理：`laravel/framework ^7.30`、`facade/ignition ^2.17`、`nunomaduro/collision ^4.3`、Symfony 5.4 组件链、`vlucas/phpdotenv 4.x`、`ramsey/uuid ^3.9`、`psr/log ^1.1` 和 Composer `config.platform.php=7.4.33`。
- Laravel 8 额外阻塞链：Symfony 5.4、`dragonmantank/cron-expression 3.x`、`ramsey/uuid 4.x`、`vlucas/phpdotenv 5.4+`。
- 当前不从 Laravel 6 直接跳 Laravel 10；先开实验分支验证 Laravel 7，再评估 Laravel 8。

## beta.2 升级实验分支最小命令集

实验分支只验证升级可行性，不把升级结果直接混入 beta.1 / RC：

```bash
./scripts/composer74 install --no-interaction --no-progress
./scripts/php74 artisan migrate:status
./scripts/php74 vendor/bin/phpunit
sh scripts/composer81-docker check-platform-reqs
sh scripts/php81-docker artisan migrate:status --no-ansi
sh scripts/php81-docker vendor/bin/phpunit --configuration phpunit.php81.xml
PHP81_DOCKER_PORT=8031 sh scripts/serve-php81-docker
APP_URL=http://127.0.0.1:8031 ADMIN_USERNAME=admin-shell-tester ADMIN_PASSWORD=secret123 ./scripts/smoke-admin-shell
ADMIN_USERNAME=admin-shell-tester ADMIN_PASSWORD=secret123 ./scripts/smoke-admin-shell
```

2026-06-01 PHP 8.1 小步实验记录：

- 新增 `docker/php81-cli.Dockerfile`，固定 `php:8.1-cli` 并安装 `bcmath / gd / pdo_mysql / zip`。
- 新增 `scripts/php81-docker` 与 `scripts/composer81-docker`，用于不污染本机 PHP 7.4 基线的 PHP 8.1 验证。
- 新增 `phpunit.php81.xml`，通过 `host.docker.internal` 连接本地 MySQL，避免容器误用宿主机 socket。
- 新增 `App\Foundation\Bootstrap\HandleExceptions`，在 PHP 8.1+ 下屏蔽 Laravel 6 / 旧依赖的弃用告警进入异常路径。
- CI 已新增 `PHPUnit (PHP 8.1 experimental)` job，并通过 `TEST_PHP_BIN=php` 复用测试库准备脚本；PHP 7.4 job 仍是主基线。
- `sh scripts/composer81-docker check-platform-reqs` 通过。
- `sh scripts/php81-docker artisan migrate:status --no-ansi` 通过。
- `sh scripts/php81-docker vendor/bin/phpunit --configuration phpunit.php81.xml` 通过，结果为 `OK (417 tests, 4281 assertions)`。
- `PHP81_DOCKER_PORT=8031 sh scripts/serve-php81-docker` 可启动 PHP 8.1 内置 Web 服务，供后台 smoke 使用。

2026-06-01 Laravel 7 桥接实验记录：

- 当前分支：`codex/laravel7-bridge-experiment`。
- Laravel 版本：`7.30.7`。
- PHP 7.4 PHPUnit：`OK (417 tests, 4281 assertions)`。
- PHP 8.1 PHPUnit：`OK (417 tests, 4281 assertions)`。
- PHP 7.4 / PHP 8.1 后台 smoke 均已通过。
- Dcat 兼容层继续保留后台登录、认证 guard / provider、中间件、权限白名单和旧入口 query-preserving 跳转。
- 该实验分支是可评审候选，不直接宣称 stable-ready。

2026-05-31 本地试跑记录：

- `./scripts/php74 artisan migrate:status` 通过，当前 17 个 migration 均已执行。
- 已固定项目内 Composer 2.2 phar：`tools/composer-2.2.phar`。
- `scripts/composer74` 当前选择顺序为：`COMPOSER74_BIN`、项目内 `tools/composer-2.2.phar`、系统 Composer。
- `./scripts/composer74 --version` 通过，输出 `Composer version 2.2.25`。
- `./scripts/composer74 install --no-interaction --no-progress` 通过，锁文件可在 PHP 7.4 工具链下重复安装。
- Composer install 当前仍提示 `swiftmailer/swiftmailer`、`symfony/debug` 为 abandoned 包；PayPal / Stripe SDK 已不再出现。

失败回滚条件：

- Composer 无法在当前锁文件下稳定安装。
- Laravel 启动期因 Dcat、Yansongda 或旧 helper 直接崩溃。
- migration / 测试库准备失败。
- 支付关键路径或后台 smoke 失败。
- 需要跨越 Laravel / PHP 大版本才能继续时，停止实验并单独规划桥接版本。
