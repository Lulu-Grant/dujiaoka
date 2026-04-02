# 支付网关整改清单

本文档用于追踪当前仓库内支付网关的整改状态，作为阶段 A“支付层整治收口”的执行面板。

状态说明：

- `已接入统一通知骨架`：通知型网关已复用公共回调服务
- `已服务化`：第三方 SDK / webhook 型网关已抽到专用服务
- `仍为旧式实现`：控制器仍直接承担较重逻辑
- `高风险`：依赖老 SDK、内联页面、或实现复杂度明显偏高

## 当前盘点

| 网关 | 路由 | 当前状态 | 备注 |
| --- | --- | --- | --- |
| Alipay | `/pay/alipay` | 已服务化 | 已拆出 `AlipayNotificationService` |
| Wepay | `/pay/wepay` | 已服务化 | 已拆出 `WepayNotificationService` |
| Mapay | `/pay/mapay` | 已接入统一通知骨架 | 控制器只保留签名规则 |
| Paysapi | `/pay/paysapi` | 已接入统一通知骨架 | 已复用 `PaymentCallbackService` |
| Yipay | `/pay/yipay` | 已接入统一通知骨架 | 已复用 `PaymentCallbackService` |
| Vpay | `/pay/vpay` | 已接入统一通知骨架 | 已修复 `pay_handleroute` 历史问题 |
| Epusdt | `/pay/epusdt` | 已接入统一通知骨架 | 已修复 `pay_handleroute` 历史问题 |
| TokenPay | `/pay/tokenpay` | 已接入统一通知骨架 | 已修复 `pay_handleroute` 历史问题 |
| Payjs | `/pay/payjs` | 已服务化 | 已拆出 `PayjsNotificationService` |
| Coinbase | `/pay/coinbase` | 已服务化 | 已拆出 `CoinbaseWebhookService` |
| Paypal | `/pay/paypal` | 部分服务化 | `returnUrl` 已抽服务，主控制器仍较重 |
| Stripe | `/pay/stripe` | 部分服务化 | 已拆出 `StripePaymentService`，但页面渲染与汇率/创建逻辑仍较重 |

## 整改优先级

### P0

- Stripe
  - 文件体积大
  - 支付路径多
  - 逻辑分叉多
  - 内联 HTML 和回调逻辑耦合重

### P1

- Paypal
  - 已有第一步服务化基础
  - 仍需继续拆同步返回、异步通知、SDK 调用和状态流转

### P2

- 统一抽象层
  - 为“通知型网关”定义更明确的适配接口
  - 为“SDK 型网关”定义统一的验证/完成支付编排模式
  - 减少控制器直接触碰第三方 facade

## 当前结论

截至当前节点：

- 通知型支付控制器的大部分重复逻辑已经被收敛
- SDK 型支付回调已经有可复用服务化样板
- 支付层剩余最高风险集中在 `StripeController` 和部分 `PaypalPayController`

## 下一步默认动作

1. 继续处理 Paypal 主流程
2. 开始拆 Stripe 的支付创建、回调完成和页面渲染逻辑
3. 视情况引入更显式的支付网关适配接口
