# 依赖阻塞矩阵

## 目标

本文档用于把当前升级前最关键的依赖阻塞整理成一张可执行矩阵。

输出重点：

- 当前包做什么
- 阻塞级别
- 建议动作是保留、替换还是移除
- 应优先在哪一阶段处理

---

## 当前矩阵

| 依赖 | 当前用途 | 阻塞级别 | 当前状态 | 建议动作 | 备注 |
| --- | --- | --- | --- | --- | --- |
| `dcat/laravel-admin 2.*` | 后台控制台核心框架 | P0 | 保留过渡 | 继续降耦合、最终替换 | 当前只承担兼容、认证和旧入口跳转，不新增业务承载 |
| `dcat/easy-excel` | 后台导入导出辅助 | P2 | 保留过渡 | 后续替换 | 随后台壳导入导出能力成熟后统一处理 |
| `germey/geetest ^3.1` | 行为验证 | P0 | 已从主锁文件移除 | 已处理 | 已退出前台下单主路径、路由、中间件与后台设置入口 |
| `simplesoftwareio/simple-qrcode 2.0.0` | 二维码生成 | P0 | 已从主锁文件移除 | 已处理 | 已改为前端本地 JS 生成二维码 |
| `bacon/bacon-qr-code 1.0.3` | QRCode 底层依赖 | P0 | 已随上层退出 | 已处理 | 已随 `simple-qrcode` 一起移除 |
| `paypal/rest-api-sdk-php ^1.14` | PayPal 支付 | P1 | 需替换 | 制定保留 / 替换 / 退场决策 | 已抽出 `PaypalSdkService` 与 `PaypalGatewayClientInterface` |
| `stripe/stripe-php ^20.0` | Stripe 支付 | P1 | 保留并继续收口 | 稳定 20.x 接入边界 | 已抽出 `StripeSdkService` 与 `StripeGatewayClientInterface` |
| `xhat/payjs-laravel ^1.6` | PayJS 支付 | P2 | 已从主锁文件移除 | 已处理 | 已随 PayJS 通道退役退出新版本 |
| `yansongda/pay ^2.10` | 支付宝 / 微信支付 | P2 | 保留过渡 | 观察后升级 | 当前不是第一阻塞点，先以回调安全测试护栏约束 |
| `phpspec/prophecy 1.13.0` | 测试依赖链 | P0 | 已从主锁文件移除 | 已处理 | 已通过升级 `phpunit/phpunit` 到 9.6.34 退出主依赖链 |

## beta.2 阻塞状态口径

| 依赖 | beta.2 状态 | 第一动作 | 退出条件 |
| --- | --- | --- | --- |
| `dcat/laravel-admin` | 保留过渡 | 继续保持后台壳主承载，Dcat 只做登录、认证、中间件、权限白名单和旧入口兼容 | 后台认证、中间件、权限白名单有替代实现后，再评估删除 |
| `dcat/easy-excel` | 保留过渡 | 跟随后台导入导出能力统一替换，不单独抢跑 | 导入导出服务边界完全脱离 Dcat 后再替换 |
| `paypal/rest-api-sdk-php` | 需替换或退役 | 维持 `PaypalGatewayClientInterface` / `PaypalSdkService` 单一 SDK 边界，先形成替换决策 | `composer why paypal/rest-api-sdk-php` 不再返回当前项目依赖链 |
| `stripe/stripe-php` | 保留并观察 | 保持 `StripePaymentService`、`StripeCheckoutService`、`StripeSdkService`、`StripeGatewayClientInterface` 边界 | 控制器不直连 SDK，关键路径测试通过 |
| `yansongda/pay` | 保留过渡 | 以 Alipay / Wepay 通知测试约束回调安全，不在 beta.2 直接替换 | 支付宝 / 微信回调异常路径测试覆盖后再决定升级或替换 |

---

## 按优先级拆解

### 第一优先级：必须先处理

#### `germey/geetest`

原因：

- 已知直接阻塞现代 PHP 兼容
- 当前前台下单页、配置、校验链仍有接入
- 但业务本身并非订单域核心能力

建议：

- 已按“退主路径、保留兼容数据字段”的方式移除
- 当前不再阻塞前台下单与依赖安装
- 后续如需重新引入行为验证，应以更轻量、可替换的方案接入

#### `simplesoftwareio/simple-qrcode` / `bacon/bacon-qr-code`

原因：

- 这条链是明确的 PHP 版本阻塞点
- 上层用途明确，替换边界相对清楚

建议：

- 已通过前端本地 JS 生成二维码的方式完成替换
- 当前二维码支付页不再依赖后端 PNG 生成器
- 这条 PHP 兼容阻塞链已经退出主锁文件

#### `phpspec/prophecy`

原因：

- 会阻塞现代开发依赖安装
- 不属于业务功能依赖

建议：

- 已通过测试栈升级完成主路径移除
- 后续只需继续关注其他包的 `require-dev` 元数据，不再是当前项目锁文件中的直接安装阻塞

---

### 第二优先级：支付生态阻塞

#### `paypal/rest-api-sdk-php`

原因：

- 已是典型历史 SDK
- 即使业务层逐步服务化，SDK 本身仍是升级阻塞源

建议：

- 不直接在控制器层处理
- 当前业务层已进一步摆脱旧 SDK 类型泄漏，可按 [paypal-stripe-transition-plan.md](/Users/apple/Documents/dujiaoshuka/docs/paypal-stripe-transition-plan.md) 继续推进退场
- 当前 webhook 路径已服务化为占位入口，后续替换时应明确是补全异步通知，还是继续只保留同步确认模型
- 当前控制器层已不再直接依赖 PayPal SDK 异常类型，后续替换实现时应保持这一边界
- 当前 PayPal 币种假设也已配置化，后续替换实现时不应重新写死 `CNY -> USD`
- 当前 PayPal 返回 / 取消回跳 URL 已收敛到独立服务，后续替换实现时不应重新内嵌到 SDK 封装

#### `stripe/stripe-php`

原因：

- 已从 `7.84.0` 进一步升到 `20.0.0`
- Stripe 主流程虽然已明显服务化，但剩余升级面仍较大
- 现已开始收敛 URL 与币种假设，为独立升级创造稳定边界
- 当前异常边界也已开始收敛到应用层，便于后续独立升级 SDK
- 当前 `source` 状态处理也开始脱离支付服务本体，便于后续升级时缩小改动面
- 当前金额换算也开始脱离控制器，便于后续升级时减少入口层改动
- 当前入口参数也开始脱离原始 request 数组读取，便于后续调整页面协议
- 当前 Stripe 支付页已开始脱离前端 CDN 壳依赖，便于后续本地化前端治理

建议：

- 先在 `20.x` 基线上继续保证 `StripePaymentService` / `StripeCheckoutService` 成为稳定边界
- 再继续清理前端协议和页面壳细节，而不是在接口仍波动时继续做大改

---

### 第三优先级：后台生态阻塞

#### `dcat/laravel-admin`

原因：

- 是最大阻塞，但不适合最先直接拆

建议：

- 当前阶段动作不是“替换包”，而是“降低后台业务承载”
- 后续等后台壳替换条件成熟，再整体退出

#### `dcat/easy-excel`

原因：

- 跟随 Dcat 生态

建议：

- 暂不单独处理
- 后续随后台替换统一评估

---

## 推荐执行顺序

1. 清理非核心业务阻塞：
   - `phpspec/prophecy`
   - `germey/geetest`
   - `simplesoftwareio/simple-qrcode` / `bacon/bacon-qr-code`
2. 继续压缩支付 SDK 绑定面：
   - `paypal/rest-api-sdk-php`
   - `stripe/stripe-php`
3. 继续降低后台耦合：
   - `dcat/laravel-admin`
   - `dcat/easy-excel`

---

## 当前结论

当前最适合先动的不是最大块的 Dcat，而是：

- 最明确的现代 PHP 阻塞链
- 最容易和核心业务主链解耦的依赖

因此下一步默认优先顺序建议是：

1. `paypal/rest-api-sdk-php` 与 `stripe/stripe-php`
2. `dcat/laravel-admin` 的进一步降耦合
3. 其余遗留包的兼容性复盘

在剩余高优先级链路被处理前，不建议直接发起正式 Laravel / PHP 跨版本升级。

## beta.1 升级实验分支验证清单

- 安装依赖：使用当前锁文件和 PHP 7.4 基线确认可重复安装。
- 数据库：执行 migration / bootstrap seed / 测试库准备脚本，不恢复 `install.sql`。
- 回归：执行全量 PHPUnit 和后台壳 smoke。
- 支付：执行 Stripe、PayPal、统一通知型网关的关键路径与异常路径测试。
- 兼容层：确认 `/admin`、后台登录、账号设置和旧 `/admin/*` 跳转仍可达。

## beta.2 升级实验分支最小命令集

实验分支只验证升级可行性，不把升级结果直接混入 beta.1 / RC：

```bash
./scripts/composer74 install
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
- Composer install 当前仍提示 `paypal/rest-api-sdk-php`、`swiftmailer/swiftmailer`、`symfony/debug` 为 abandoned 包，继续作为 beta.2 / RC 后续替换或退场观察项。

失败回滚条件：

- Composer 无法在当前锁文件下稳定安装。
- Laravel 启动期因 Dcat、支付 SDK 或旧 helper 直接崩溃。
- migration / 测试库准备失败。
- 支付关键路径或后台 smoke 失败。
- 需要跨越 Laravel / PHP 大版本才能继续时，停止实验并单独规划桥接版本。
