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
| `germey/geetest ^3.1` | 行为验证 | P0 | 老旧且 PHP 兼容性差 | 替换或移除 | 已知阻塞现代 PHP；若业务非强依赖，优先考虑移除 |
| `simplesoftwareio/simple-qrcode 2.0.0` | 二维码生成 | P0 | 被旧 `bacon` 链锁死 | 替换 | 当前 QRCode 链直接卡住现代 PHP 兼容性 |
| `bacon/bacon-qr-code 1.0.3` | QRCode 底层依赖 | P0 | 旧版本 | 随上层替换退出 | 不建议单独保留 |
| `paypal/rest-api-sdk-php ^1.14` | PayPal 支付 | P1 | 历史 SDK | 替换 | 支付链已部分服务化，但 SDK 仍旧 |
| `stripe/stripe-php ^7.84` | Stripe 支付 | P1 | 偏旧 | 升级或替换接入方式 | 先稳定服务层，再升级 SDK |
| `xhat/payjs-laravel ^1.6` | PayJS 支付 | P2 | 仍在用 | 观察 | 已服务化，但后续仍需看维护性 |
| `yansongda/pay ^2.10` | 支付宝 / 微信支付 | P2 | 可运行 | 观察后升级 | 当前不是第一阻塞点 |
| `phpspec/prophecy 1.13.0` | 测试依赖链 | P0 | 现代 PHP 不兼容 | 移除或升级测试链 | 当前会阻塞开发依赖安装 |

---

## 按优先级拆解

### 第一优先级：必须先处理

#### `germey/geetest`

原因：

- 已知直接阻塞现代 PHP 兼容
- 当前前台下单页、配置、校验链仍有接入
- 但业务本身并非订单域核心能力

建议：

- 优先评估移除成本
- 如果移除风险低，直接从默认主路径中退场
- 如果必须保留，再寻找替代方案

#### `simplesoftwareio/simple-qrcode` / `bacon/bacon-qr-code`

原因：

- 这条链是明确的 PHP 版本阻塞点
- 上层用途明确，替换边界相对清楚

建议：

- 作为第一条可执行替换链处理
- 目标是把 QRCode 功能从旧 `bacon 1.0.*` 链上摘下来

#### `phpspec/prophecy`

原因：

- 会阻塞现代开发依赖安装
- 不属于业务功能依赖

建议：

- 优先看能否通过测试栈升级或替换间接移除
- 它应是“最适合先清掉的非业务阻塞”

---

### 第二优先级：支付生态阻塞

#### `paypal/rest-api-sdk-php`

原因：

- 已是典型历史 SDK
- 即使业务层逐步服务化，SDK 本身仍是升级阻塞源

建议：

- 不直接在控制器层处理
- 等现有 Paypal 服务化路径稳定后，再替换 SDK 接入

#### `stripe/stripe-php`

原因：

- 当前版本较老
- Stripe 主流程虽然已部分服务化，但剩余升级面仍较大

建议：

- 先继续保证 `StripePaymentService` / `StripeCheckoutService` 成为稳定边界
- 再独立升级 SDK，而不是边拆边升

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

1. `phpspec/prophecy`
2. `germey/geetest`
3. `simple-qrcode` / `bacon`

在这三条链被处理前，不建议直接发起正式 Laravel / PHP 跨版本升级。
