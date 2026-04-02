# PayPal / Stripe 迁移方案

本文档用于承接升级前清障阶段里两条仍保留支付通道的后续动作：`PayPal` 与 `Stripe`。

## 当前结论

- `PayPal` 优先级高于 `Stripe`
- 原因不是业务占比，而是 `paypal/rest-api-sdk-php` 的历史包袱更重
- `Stripe` 当前虽然版本偏旧，但边界已经比 `PayPal` 更清晰

## 当前状态

### PayPal

- 当前依赖：`paypal/rest-api-sdk-php ^1.14`
- 当前运行模式：由 `DUJIAOKA_PAYPAL_MODE` 控制，默认 `live`
- 当前异步通知状态：仅保留占位型 webhook 入口，实际完成支付仍以同步 return 主链为准
- 当前异常边界：业务层与控制器层已改为只接触应用层 `PaymentGatewayException`
- 业务入口已收敛到：
  - [PaypalCheckoutService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaypalCheckoutService.php)
  - [PaypalReturnService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaypalReturnService.php)
- webhook 占位入口已收敛到：
  - [PaypalWebhookService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaypalWebhookService.php)
- SDK 访问已收敛到：
  - [PaypalSdkService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaypalSdkService.php)
- 业务层当前只依赖：
  - [PaypalGatewayClientInterface.php](/Users/apple/Documents/dujiaoshuka/app/Service/Contracts/PaypalGatewayClientInterface.php)

### Stripe

- 当前依赖：`stripe/stripe-php ^7.84`
- 业务入口已收敛到：
  - [StripeCheckoutService.php](/Users/apple/Documents/dujiaoshuka/app/Service/StripeCheckoutService.php)
  - [StripePaymentService.php](/Users/apple/Documents/dujiaoshuka/app/Service/StripePaymentService.php)
- SDK 访问已收敛到：
  - [StripeSdkService.php](/Users/apple/Documents/dujiaoshuka/app/Service/StripeSdkService.php)
- 业务层当前只依赖：
  - [StripeGatewayClientInterface.php](/Users/apple/Documents/dujiaoshuka/app/Service/Contracts/StripeGatewayClientInterface.php)

## 默认执行顺序

1. 先处理 `PayPal`
2. 再处理 `Stripe`

## PayPal 退场路径

1. 继续消除业务层对旧 SDK 类型的泄漏
2. 保持运行模式等接入假设配置化，不再散落在旧 SDK 封装内部
3. 保持异常语义停留在应用层，避免新实现再次把 SDK 异常直接泄漏到控制器
4. 明确新接入方式的能力边界：
   - 创建支付链接
   - 同步返回确认
   - 异步通知如何参与或退出
   - 支付完成状态落单
5. 在不改动业务服务调用面的前提下引入新实现
6. 最后移除 `paypal/rest-api-sdk-php`

## Stripe 升级路径

1. 保持 `StripeCheckoutService` 与 `StripePaymentService` 为唯一主入口
2. 继续清掉旧的页面/回调内联耦合
3. 独立升级 `stripe/stripe-php`
4. 验证 `charge / return / check` 三条路径后再收口旧兼容逻辑

## 当前退出标准

### PayPal

- 业务服务不再依赖旧 SDK 暴露的类型
- 旧 SDK 被新实现替代
- `composer why paypal/rest-api-sdk-php` 不再返回当前项目依赖链

### Stripe

- SDK 升级后主链测试通过
- 控制器不再承担残余支付状态流转逻辑
- 文档与后台生命周期状态同步更新
