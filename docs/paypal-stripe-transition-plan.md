# PayPal / Stripe 退场记录

更新时间：2026-05-31

本文档保留为历史迁移入口。当前结论已经从“继续替换 PayPal / Stripe”调整为“按维护范围退役”。

## 当前决策

- PayPal 不再作为新版本维护通道。
- Stripe 不再作为新版本维护通道。
- `paypal/rest-api-sdk-php` 与 `stripe/stripe-php` 已从 Composer 依赖中移除。
- PayPal / Stripe 控制器、服务、SDK 边界、收银页资源和对应测试基线已移除。
- 后续不再为 PayPal / Stripe 增加新功能、迁移 SDK 或补公开支付入口。

## 当前保留支付范围

- 官方支付宝：`/pay/alipay`
- 官方微信：`/pay/wepay`
- 易支付：`/pay/yipay`
- Epusdt：`/pay/epusdt`

## 退场验收口径

- `composer why paypal/rest-api-sdk-php` 不应返回当前项目依赖链。
- `composer why stripe/stripe-php` 不应返回当前项目依赖链。
- `routes/common/pay.php` 不再注册 PayPal / Stripe 路由。
- `PaySampleSeeder` 不再写入 PayPal / Stripe 样例。
- 当前 release 文档不再把 PayPal / Stripe 写成维护目标。

## 后续边界

如果未来需要重新接入 PayPal 或 Stripe，应作为新支付通道专项重新设计，单独建立配置模型、回调测试、安全审计和发布验收，不从本次退役代码中直接回滚恢复。
