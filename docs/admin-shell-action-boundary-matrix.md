# 后台壳动作边界矩阵

更新时间：2026-05-24

## 目标

本文档用于约束 `v3.0.0-beta.1` 前后台壳批量动作的字段边界、禁止事项、测试要求和回滚口径。

默认规则：

- 只新增低风险、可预览、可显式提交、可测试的动作。
- 批量动作必须只更新声明字段，并只统计实际变化数量。
- 不新增价格、库存、支付密钥、订单状态推进、履约、通知副作用相关批量动作。

## 资源边界

| 资源 | 允许字段 | 禁止字段 / 行为 | 回滚口径 | 必测项 |
| --- | --- | --- | --- | --- |
| 商品 `goods` | `gd_description`、`buy_prompt`、`description`、`gd_keywords`、`picture` 等展示/文本字段 | `actual_price`、`retail_price`、`in_stock`、`type`、`is_open`、支付或履约链 | 通过相同筛选 ID 重新批量设置原文本；高风险字段不提供批量回滚 | 页面预览、只更新目标字段、空 ID 错误、route registrar、smoke |
| 订单 `order` | `title`、`info`、人工展示辅助字段 | `status` 推进、`trade_no`、支付确认、发货、通知、查询密码无保护批量变更 | 用订单 ID 列表恢复文本字段；状态机相关不纳入后台壳批量动作 | 无事件写入、只改文本、重复提交幂等、异常不触发履约 |
| 支付 `pay` | `pay_name` 及其前缀、后缀、替换、空格整理 | `pay_check`、`merchant_id`、`merchant_key`、`merchant_pem`、`pay_handleroute`、支付方式密钥 | 用支付 ID 列表恢复展示名称；密钥和路由必须单条显式编辑 | 不改密钥、路由、商户字段；smoke 覆盖入口 |
| 优惠码 `coupon` | `coupon` 文本及前缀、后缀、替换、空格整理 | `discount`、`ret`、`is_open`、`is_use`、商品关联 | 用优惠码 ID 列表恢复文本；折扣和次数不提供批量文本动作 | 唯一性保护、只改内容文本、不改状态和商品关联 |
| 卡密 `carmis` | `carmi` 文本及 trim、collapse、replace、suffix 等导入后维护动作 | `status`、`is_loop`、`goods_id`、订单关联、库存扣减、履约链 | 用卡密 ID 列表恢复原文本；已售状态和订单关联不批量改 | 只改 `carmis.carmi`、缺失 ID 预览、smoke 覆盖入口 |
| 系统设置 | 分组表单中的显式配置项 | 无保护批量更新、密钥批量替换、运行时敏感项静默写入 | 通过单项配置表单恢复；敏感项必须显式保存 | 表单校验、权限中间件、敏感示例值不进文档 |

## 新动作准入清单

新增任何后台壳动作前必须确认：

- 路由使用 `/admin/v2/{resource}/{action}`。
- 页面有匹配预览、缺失 ID 提示和风险边界说明。
- 控制器只做请求校验和服务调用，写入逻辑放在服务层。
- Feature 测试覆盖页面渲染与提交。
- Unit 测试覆盖默认值和只更新允许字段。
- `AdminShellRouteRegistrarTest`、`AdminShellPageStructureTest` 和 `tests/Browser/admin-shell-smoke.sh` 同步更新。
- README、当前审计、进度总汇和升级日志同步动作清单。

## beta.1 冻结规则

- beta.1 冻结后不再新增批量动作。
- 冻结后只接受阻断 bug、测试补强、文档口径修正。
- 任何触及禁止字段的需求必须进入单独规划，不并入后台壳收口批次。
