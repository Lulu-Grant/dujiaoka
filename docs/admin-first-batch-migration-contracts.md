# 后台第一批迁移合同

## 目标

这份文档用于固定第一批后台迁移页面的最小合同，避免后续新后台壳开发时再从 Dcat 控制器反推字段、筛选、列表列和页面行为。

当前第一批页面：

1. 商品分类管理
2. 邮件模板管理
3. 支付通道管理

---

## 1. 商品分类管理

对应现状：

- [GoodsGroupController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/GoodsGroupController.php)

### 列表页合同

列：

- `id`
- `gp_name`
- `is_open`
- `ord`
- `created_at`
- `updated_at`

筛选：

- `id`
- `trashed`

动作：

- 行级恢复
- 批量恢复

### 详情页合同

字段：

- `id`
- `gp_name`
- `is_open`
- `ord`
- `created_at`
- `updated_at`

### 编辑页合同

字段：

- 只读：`id`、`created_at`、`updated_at`
- 可编辑：`gp_name`、`is_open`、`ord`

行为：

- 默认 `is_open = STATUS_OPEN`
- 默认 `ord = 1`
- 禁用 view button

---

## 2. 邮件模板管理

对应现状：

- [EmailtplController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/EmailtplController.php)
- [AdminFormBehaviorService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminFormBehaviorService.php)

### 列表页合同

列：

- `id`
- `tpl_name`
- `tpl_token`
- `created_at`
- `updated_at`

筛选：

- `id`
- `tpl_name`
- `tpl_token`

动作：

- 行级恢复
- 批量恢复

页面约束：

- 禁用 view button
- 禁用 delete button

### 详情页合同

字段：

- `id`
- `tpl_name`
- `tpl_content`
- `tpl_token`
- `created_at`
- `updated_at`

### 编辑页合同

字段：

- 只读：`id`、`created_at`、`updated_at`
- 可编辑：`tpl_name`、`tpl_content`、`tpl_token`

行为：

- 创建时 `tpl_token` 必填
- 编辑时 `tpl_token` 禁用
- 禁用 view button
- 禁用 delete button

---

## 3. 支付通道管理

对应现状：

- [PayController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/PayController.php)
- [PayAdminPresenterService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PayAdminPresenterService.php)

### 列表页合同

列：

- `id`
- `pay_name`
- `pay_check`
- `lifecycle`
- `pay_method`
- `merchant_id`
- `merchant_key`
- `merchant_pem`
- `pay_client`
- `pay_handleroute`
- `is_open`
- `created_at`
- `updated_at`

筛选：

- `id`
- `pay_check`
- `pay_name`
- `trashed`

动作：

- 行级恢复
- 批量恢复

页面约束：

- 禁用 delete button

展示规则：

- `lifecycle` 走 [PayAdminPresenterService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PayAdminPresenterService.php)
- `pay_method` 使用 `Pay::getMethodMap()`
- `pay_client` 使用 `Pay::getClientMap()`

### 详情页合同

字段：

- `id`
- `pay_name`
- `merchant_id`
- `merchant_key`
- `merchant_pem`
- `pay_check`
- `lifecycle`
- `pay_client`
- `pay_handleroute`
- `pay_method`
- `is_open`
- `created_at`
- `updated_at`

### 编辑页合同

字段：

- 只读：`id`、`created_at`、`updated_at`
- 可编辑：
  - `pay_name`
  - `merchant_id`
  - `merchant_key`
  - `merchant_pem`
  - `pay_check`
  - `pay_client`
  - `pay_method`
  - `pay_handleroute`
  - `is_open`

行为：

- `merchant_id` 必填
- `merchant_pem` 必填
- `pay_check` 必填
- `pay_handleroute` 必填
- 默认 `pay_client = PAY_CLIENT_PC`
- 默认 `pay_method = METHOD_JUMP`
- 默认 `is_open = STATUS_OPEN`
- 禁用 delete button

---

## 当前结论

第一批后台迁移页已经具备明确的页面合同。

这意味着下一阶段如果要启动新后台壳，不需要再从 Dcat 实现反推：

- 需要哪些列表列
- 需要哪些筛选项
- 需要哪些详情字段
- 需要哪些表单字段和默认行为
