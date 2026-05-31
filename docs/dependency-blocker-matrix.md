# 依赖阻塞矩阵

更新时间：2026-05-31

本文档用于把当前升级前最关键的依赖阻塞整理成一张可执行矩阵。

## 当前矩阵

| 依赖 | 当前用途 | 阻塞级别 | 当前状态 | 建议动作 | 备注 |
| --- | --- | --- | --- | --- | --- |
| `laravel/framework 6.20.*` | 应用主框架 | P0 | 保留运行时基线 | 先做 PHP 小步兼容实验，再设计 Laravel 桥接版本 | 不在 beta.2 / RC 直接跳大版本 |
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
| `laravel/framework` | 保留运行时基线 | 不在 beta.2 直接升级，先验证 PHP 7.4 工具链和迁移 / 测试 / smoke 可重复执行 | PHP 小步实验完成并形成 Laravel 桥接版本方案 |
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
- 当前剩余重点转为官方支付宝、官方微信、易支付和 Epusdt 的回调异常路径与配置边界。

建议：

- 继续补签名失败、金额不一致、重复通知、已完成订单重复推进测试。
- 后台批量动作不得触碰支付密钥、商户号和支付路由。

## 推荐执行顺序

1. 继续降低后台耦合：
   - `dcat/laravel-admin`
   - `dcat/easy-excel`
2. 继续约束保留支付通道：
   - 官方支付宝
   - 官方微信
   - 易支付
   - Epusdt
3. 设计 PHP / Laravel 分步升级路线。

## beta.2 升级实验分支最小命令集

实验分支只验证升级可行性，不把升级结果直接混入 beta.1 / RC：

```bash
./scripts/composer74 install --no-interaction --no-progress
./scripts/php74 artisan migrate:status
./scripts/php74 vendor/bin/phpunit
ADMIN_USERNAME=admin-shell-tester ADMIN_PASSWORD=secret123 ./scripts/smoke-admin-shell
```

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
