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
- 当前币种假设：源币种与目标结算币种已配置化，默认 `CNY -> USD`
- 当前异步通知状态：仅保留占位型 webhook 入口，实际完成支付仍以同步 return 主链为准
- 当前异常边界：业务层与控制器层已改为只接触应用层 `PaymentGatewayException`
- 当前回跳假设：PayPal 同步返回与取消回跳 URL 已收敛到独立服务，不再散落在 SDK 封装中，并对缺失命名路由的环境提供安全回退
- 业务入口已收敛到：
  - [PaypalCheckoutService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaypalCheckoutService.php)
  - [PaypalReturnService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaypalReturnService.php)
- webhook 占位入口已收敛到：
  - [PaypalWebhookService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaypalWebhookService.php)
- 回跳 URL 边界已收敛到：
  - [PaypalCallbackUrlService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaypalCallbackUrlService.php)
- SDK 访问已收敛到：
  - [PaypalSdkService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaypalSdkService.php)
- 业务层当前只依赖：
  - [PaypalGatewayClientInterface.php](/Users/apple/Documents/dujiaoshuka/app/Service/Contracts/PaypalGatewayClientInterface.php)

### Stripe

- 当前依赖：`stripe/stripe-php ^7.84`
- 当前币种假设：源币种与目标结算币种已配置化，默认 `CNY -> USD`
- 当前 URL 假设：结账页 return / check / charge / detail URL 已收敛到独立服务
- 当前异常边界：控制器层已不再直接处理 SDK 异常，Stripe 支付服务改为抛出应用层 `PaymentGatewayException`
- 当前状态处理边界：`source` 检索、扣款、归属校验、完成订单已开始从支付服务中拆出
- 当前金额换算边界：结账页与卡片扣款所需的分单位金额已开始收敛到独立金额服务
- 当前入口参数边界：`orderid / source / stripeToken` 已开始收敛到 Stripe 专用输入对象
- 当前页面壳边界：Stripe 收银页已改为项目内本地 CSS/JS 资源，不再依赖外部前端 CDN 提供页面壳
- 业务入口已收敛到：
  - [StripeCheckoutService.php](/Users/apple/Documents/dujiaoshuka/app/Service/StripeCheckoutService.php)
  - [StripePaymentService.php](/Users/apple/Documents/dujiaoshuka/app/Service/StripePaymentService.php)
- URL 边界已收敛到：
  - [StripeRouteService.php](/Users/apple/Documents/dujiaoshuka/app/Service/StripeRouteService.php)
- 金额边界已收敛到：
  - [StripeAmountService.php](/Users/apple/Documents/dujiaoshuka/app/Service/StripeAmountService.php)
- SDK 访问已收敛到：
  - [StripeSdkService.php](/Users/apple/Documents/dujiaoshuka/app/Service/StripeSdkService.php)
- 业务层当前只依赖：
  - [StripeGatewayClientInterface.php](/Users/apple/Documents/dujiaoshuka/app/Service/Contracts/StripeGatewayClientInterface.php)

## 默认执行顺序

1. 先处理 `PayPal`
2. 再处理 `Stripe`

## PayPal 退场路径

1. 继续消除业务层对旧 SDK 类型的泄漏
2. 保持运行模式、源币种、目标币种等接入假设配置化，不再散落在旧 SDK 封装内部
3. 保持异常语义停留在应用层，避免新实现再次把 SDK 异常直接泄漏到控制器
4. 保持同步返回、取消回跳等 URL 假设停留在独立服务层，而不是嵌在 SDK 实现内部
5. 明确新接入方式的能力边界：
   - 创建支付链接
   - 同步返回确认
   - 异步通知如何参与或退出
   - 支付完成状态落单
6. 在不改动业务服务调用面的前提下引入新实现
7. 最后移除 `paypal/rest-api-sdk-php`

## Stripe 升级路径

1. 保持 `StripeCheckoutService` 与 `StripePaymentService` 为唯一主入口
2. 保持币种与 URL 假设继续停留在配置 / 独立服务层，而不是散回控制器
3. 保持异常语义停留在应用层，而不是继续让 SDK 错误直接泄漏到控制器
4. 继续把 `source` 状态处理从支付服务中收拢成独立边界
5. 继续把金额换算与分单位计算留在独立服务层，不再散回控制器
6. 继续把入口参数解析从控制器原始数组访问收成明确输入对象
7. 继续清掉旧的页面 / 回调内联耦合与外部 CDN 依赖
8. 独立升级 `stripe/stripe-php`
9. 验证 `charge / return / check` 三条路径后再收口旧兼容逻辑

## 当前退出标准

### PayPal

- 业务服务不再依赖旧 SDK 暴露的类型
- 运行模式与结算币种不再写死在旧 SDK 封装内部
- 返回 / 取消回跳 URL 不再写死在旧 SDK 封装内部
- 旧 SDK 被新实现替代
- `composer why paypal/rest-api-sdk-php` 不再返回当前项目依赖链

### Stripe

- SDK 升级后主链测试通过
- 结账页 URL 与币种假设不再写死在控制器或 SDK 封装中
- 控制器层不再直接依赖 Stripe SDK 异常语义
- 控制器不再承担残余支付状态流转逻辑
- 文档与后台生命周期状态同步更新
