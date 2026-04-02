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
| `dcat/laravel-admin 2.*` | 后台控制台核心框架 | P0 | 深度耦合 | 保留过渡、最终替换 | 当前不能先硬拔，应先降耦合 |
| `dcat/easy-excel` | 后台导入导出辅助 | P2 | 依赖 Dcat 生态 | 观察、后续替换 | 随后台壳替换一起处理更合理 |
| `germey/geetest ^3.1` | 行为验证 | P0 | 已从主锁文件移除 | 已处理 | 已退出前台下单主路径、路由、中间件与后台设置入口 |
| `simplesoftwareio/simple-qrcode 2.0.0` | 二维码生成 | P0 | 已从主锁文件移除 | 已处理 | 已改为前端本地 JS 生成二维码 |
| `bacon/bacon-qr-code 1.0.3` | QRCode 底层依赖 | P0 | 已随上层退出 | 已处理 | 已随 `simple-qrcode` 一起移除 |
| `paypal/rest-api-sdk-php ^1.14` | PayPal 支付 | P1 | 历史 SDK，已收敛到接口绑定的单点服务 | 替换 | 已抽出 `PaypalSdkService` 与 `PaypalGatewayClientInterface` |
| `stripe/stripe-php ^7.84` | Stripe 支付 | P1 | 偏旧，已收敛到接口绑定的单点服务 | 升级或替换接入方式 | 已抽出 `StripeSdkService` 与 `StripeGatewayClientInterface` |
| `xhat/payjs-laravel ^1.6` | PayJS 支付 | P2 | 已从主锁文件移除 | 已处理 | 已随 PayJS 通道退役退出新版本 |
| `yansongda/pay ^2.10` | 支付宝 / 微信支付 | P2 | 可运行 | 观察后升级 | 当前不是第一阻塞点 |
| `phpspec/prophecy 1.13.0` | 测试依赖链 | P0 | 已从主锁文件移除 | 已处理 | 已通过升级 `phpunit/phpunit` 到 9.6.34 退出主依赖链 |

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

#### `stripe/stripe-php`

原因：

- 当前版本较老
- Stripe 主流程虽然已部分服务化，但剩余升级面仍较大

建议：

- 先继续保证 `StripePaymentService` / `StripeCheckoutService` 成为稳定边界
- 再按 [paypal-stripe-transition-plan.md](/Users/apple/Documents/dujiaoshuka/docs/paypal-stripe-transition-plan.md) 独立升级 SDK，而不是边拆边升

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
