# 后台迁移优先级清单

## 背景

截至当前阶段，`app/Admin` 已完成多轮薄壳化处理：

- 业务逻辑持续下沉到 `app/Service`
- 高重复筛选、恢复、字段挂载、页面壳装配已集中收口
- 后台控制器中不再保留明显依赖 Dcat 行上下文的业务展示闭包

这意味着后台替换已经可以从“概念评估”进入“迁移优先级排序”。

---

## 评估维度

每个后台页面按下面几个维度判断迁移优先级：

- 业务风险：迁移后是否容易引起订单、库存、支付等主链回归
- Dcat 绑定度：是否高度依赖 Grid / Form / Widget 特性
- 服务化完成度：是否已经具备清晰的普通服务边界
- 验证成本：是否已有测试护栏或容易补验证

---

## 第一优先级

这些页面已经最接近“新壳直接接服务”的状态，适合作为第一批迁移对象。

第一批迁移合同见：

- [admin-first-batch-migration-contracts.md](/Users/apple/Documents/dujiaoshuka/docs/admin-first-batch-migration-contracts.md)

### 1. 商品分类管理

对应文件：

- [GoodsGroupController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/GoodsGroupController.php)
- [GoodsGroupShellController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/AdminShell/GoodsGroupShellController.php)

原因：

- 标准 CRUD
- 状态、筛选、恢复、字段挂载都已服务化
- 风险低，适合做新后台壳样板
- 当前已落地第一版只读样板页：`/admin/v2/goods-group`

### 2. 邮件模板管理

对应文件：

- [EmailtplController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/EmailtplController.php)

原因：

- 结构清晰
- token 字段策略、表单行为、恢复动作都已下沉
- 可作为“表单页 + 列表页”迁移样板

### 3. 支付通道管理

对应文件：

- [PayController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/PayController.php)
- [PayShellController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/AdminShell/PayShellController.php)

原因：

- 展示格式化、生命周期文案、恢复动作、筛选都已收口
- 当前剩余主要是普通字段编辑
- 适合作为“状态文案 + 配置表单”迁移样板

当前样板状态：

- 第一优先级三张只读样板页已全部落地：
  - `/admin/v2/goods-group`
  - `/admin/v2/emailtpl`
  - `/admin/v2/pay`

---

## 第二优先级

这些页面已经有较好边界，但仍带有一些中等复杂度业务或数据装配。

### 4. 优惠码管理

对应文件：

- [CouponController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/CouponController.php)

原因：

- 主体已经服务化
- 仍带有商品多选与格式化行为
- 迁移难度可控，但比第一优先级稍高

当前样板状态：

- 第二优先级第一张只读样板页已落地：
  - `/admin/v2/coupon`

### 5. 卡密管理

对应文件：

- [CarmisController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/CarmisController.php)
- [ImportCarmis.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Forms/ImportCarmis.php)

原因：

- 列表与详情展示已经较薄
- 导入仍然是这组页面的核心复杂点
- 适合作为第二批“带批量导入能力”的迁移对象

当前样板状态：

- 第二优先级第二张只读样板页已落地：
  - `/admin/v2/carmis`

### 6. 系统设置 / 邮件测试

对应文件：

- [SystemSettingController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/SystemSettingController.php)
- [EmailTestController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/EmailTestController.php)

原因：

- 页面壳和配置读写已抽离
- 但表单字段较多，验证项也多
- 迁移时更像“配置中心”而不是简单 CRUD

---

## 第三优先级

这些页面虽然已经有明显进展，但仍然和业务链路、统计口径或复杂表单紧密相关。

### 7. 商品管理

对应文件：

- [GoodsController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/GoodsController.php)

原因：

- 目前已很薄，库存展示也已切到模型 accessor + 查询预载
- 但字段多、配置多、输入输出复杂
- 更适合作为第一优先级页面迁移完成后的第二阶段目标

### 8. 订单管理

对应文件：

- [OrderController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/OrderController.php)

原因：

- 已有较多服务化工作
- 但和订单状态、支付结果、履约链天然更近
- 验证成本高于普通 CRUD

### 9. 后台首页看板

对应文件：

- [HomeController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/HomeController.php)
- [AdminDashboardLayoutService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminDashboardLayoutService.php)
- [AdminDashboardMetricsService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminDashboardMetricsService.php)

原因：

- 布局已经抽离
- 但图表壳仍强依赖 Dcat Metrics
- 更适合在 CRUD 迁移稳定后再处理

---

## 推荐迁移顺序

建议下一阶段默认按这个顺序推进：

1. 商品分类管理
2. 邮件模板管理
3. 支付通道管理
4. 优惠码管理
5. 卡密管理
6. 系统设置 / 邮件测试
7. 商品管理
8. 订单管理
9. 后台首页看板

---

## 当前结论

后台替换这件事已经不再是“是否能做”的问题，而是“按什么顺序做更稳”。

当前最合理的策略是：

- 先迁低风险、高服务化页面
- 再迁中复杂度的批量与配置页面
- 最后处理订单与仪表盘这类高验证成本页面
