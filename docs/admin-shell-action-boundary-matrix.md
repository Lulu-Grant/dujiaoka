# 后台壳动作边界矩阵

更新时间：2026-05-31

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

## 文本类动作明细

下表只列 RC 前继续允许维护的低风险文本动作。已存在的启停、折扣、次数、状态、支付场景、支付方式等运营动作进入“保留型运营动作”，不作为继续扩容目标。

| 资源 | 动作 | 实际更新字段 | 明确不更新字段 / 行为 | 回滚说明 |
| --- | --- | --- | --- | --- |
| 商品 `goods` | `batch-buy-prompt` | `buy_prompt` | 价格、库存、销量、状态、分组、排序、支付、履约 | 用同一 ID 列表重新写回原购买提示 |
| 商品 `goods` | `batch-buy-prompt-trim` | `buy_prompt` | 同上 | 从导出或备份文本按 ID 重新写回 |
| 商品 `goods` | `batch-description` | `gd_description` | 价格、库存、销量、状态、分组、排序、支付、履约 | 用同一 ID 列表重新写回原简介 |
| 商品 `goods` | `batch-description-trim` | `gd_description` | 同上 | 从导出或备份文本按 ID 重新写回 |
| 商品 `goods` | `batch-keywords` | `gd_keywords` | 价格、库存、销量、状态、分组、排序、支付、履约 | 用同一 ID 列表重新写回原关键字 |
| 商品 `goods` | `batch-keywords-suffix` | `gd_keywords` | 同上 | 反向批量替换或重新写回原关键字 |
| 商品 `goods` | `batch-keywords-trim` | `gd_keywords` | 同上 | 从导出或备份文本按 ID 重新写回 |
| 商品 `goods` | `batch-keywords-collapse-spaces` | `gd_keywords` | 同上 | 从导出或备份文本按 ID 重新写回 |
| 订单 `order` | `batch-info` | `info` | `status`、`trade_no`、支付完成、履约、通知、卡密交付 | 用同一订单 ID 列表重新写回原附加信息 |
| 订单 `order` | `batch-title` | `title` | 状态机、支付、履约、通知、查询密码 | 用同一订单 ID 列表重新写回原标题 |
| 订单 `order` | `batch-title-prefix` | `title` | 同上 | 反向批量替换或重新写回原标题 |
| 订单 `order` | `batch-title-suffix` | `title` | 同上 | 反向批量替换或重新写回原标题 |
| 订单 `order` | `batch-title-trim` | `title` | 同上 | 从导出或备份文本按 ID 重新写回 |
| 订单 `order` | `batch-title-collapse-spaces` | `title` | 同上 | 从导出或备份文本按 ID 重新写回 |
| 支付 `pay` | `batch-name` | `pay_name` | `pay_check`、`merchant_id`、`merchant_key`、`merchant_pem`、`pay_handleroute`、支付 SDK 配置 | 用同一支付 ID 列表重新写回原名称 |
| 支付 `pay` | `batch-name-prefix` | `pay_name` | 同上 | 反向批量替换或重新写回原名称 |
| 支付 `pay` | `batch-name-suffix` | `pay_name` | 同上 | 反向批量替换或重新写回原名称 |
| 支付 `pay` | `batch-name-replace` | `pay_name` | 同上 | 用同一支付 ID 列表重新写回原名称 |
| 支付 `pay` | `batch-name-trim` | `pay_name` | 同上 | 从导出或备份文本按 ID 重新写回 |
| 支付 `pay` | `batch-name-collapse-spaces` | `pay_name` | 同上 | 从导出或备份文本按 ID 重新写回 |
| 优惠码 `coupon` | `batch-code` | `coupon` | `discount`、`ret`、`is_open`、`is_use`、商品关联 | 用同一优惠码 ID 列表重新写回原内容，需保持唯一性 |
| 优惠码 `coupon` | `batch-code-prefix` | `coupon` | 同上 | 反向批量替换或重新写回原内容，需保持唯一性 |
| 优惠码 `coupon` | `batch-code-suffix` | `coupon` | 同上 | 反向批量替换或重新写回原内容，需保持唯一性 |
| 优惠码 `coupon` | `batch-code-replace` | `coupon` | 同上 | 用同一优惠码 ID 列表重新写回原内容，需保持唯一性 |
| 优惠码 `coupon` | `batch-code-trim` | `coupon` | 同上 | 从导出或备份文本按 ID 重新写回，需保持唯一性 |
| 优惠码 `coupon` | `batch-code-collapse-spaces` | `coupon` | 同上 | 从导出或备份文本按 ID 重新写回，需保持唯一性 |
| 卡密 `carmis` | `batch-trim` | `carmi` | `status`、`is_loop`、`goods_id`、订单关联、库存扣减、履约 | 用同一卡密 ID 列表重新写回原卡密内容 |
| 卡密 `carmis` | `batch-collapse-spaces` | `carmi` | 同上 | 从导出或备份文本按 ID 重新写回 |
| 卡密 `carmis` | `batch-replace` | `carmi` | 同上 | 用同一卡密 ID 列表重新写回原卡密内容 |
| 卡密 `carmis` | `batch-suffix` | `carmi` | 同上 | 反向批量替换或重新写回原卡密内容 |

## 现存动作分级

以下分级用于 beta.1 收口，不表示鼓励继续扩展高风险动作。

| 分级 | 当前动作 | beta.1 处理 |
| --- | --- | --- |
| 低风险文本整理 | 商品说明/简介/购买提示/关键字整理，订单标题/附加信息整理，支付名称整理，优惠码内容整理，卡密内容整理 | 允许继续补齐缺口，但必须遵守准入清单 |
| 保留型运营动作 | 商品启停、商品分类、销量、排序、限购，订单类型、查询密码重置，支付启停/场景/方式，优惠码启停/使用状态/折扣/次数 | 已存在的入口保留并继续测试，不在 beta.1 扩展同类新动作 |
| 禁止新增动作 | 价格、库存、支付密钥、支付回调路由、订单支付完成、订单履约、通知副作用、卡密销售状态和订单关联 | 不进入后台壳批量动作；需要单独规划和验收 |

## smoke 覆盖护栏

- 所有注册到 `AdminShellResourceRegistry` 的静态 `batch` GET 路由，都必须在 [tests/Browser/admin-shell-smoke.sh](/Users/apple/Documents/dujiaoshuka/tests/Browser/admin-shell-smoke.sh) 中有 `assert_page` 覆盖。
- [AdminShellRouteRegistrarTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AdminShellRouteRegistrarTest.php) 会自动检查这条规则。
- 通过 `mode=` 挂在创建页上的批量动作不在注册表静态路由检查范围内，仍需手工维护 smoke 覆盖。

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
