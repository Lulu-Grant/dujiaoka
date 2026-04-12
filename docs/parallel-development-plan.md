# 后台壳十路并行开发计划

制定日期：2026-04-12

目标：

- 用十个并行子任务同时推进后台壳扩容与旧 Dcat 降耦合
- 将共享冲突压到最低，让主线程只负责集成、回归和主线提交
- 将“资源页增强”“共享 UI 打磨”“高频入口退壳”拆成可独立交付的工作面

## 并行原则

- 每个子任务拥有明确写入范围，避免多人同时修改同一批共享文件
- 主线程负责：
  - 共享注册表与路由总线
  - 全量 PHPUnit 回归
  - 升级日志同步
  - GitHub `master` 集成
- 子任务优先修改：
  - 资源自己的控制器
  - 资源自己的服务
  - 资源自己的 Blade 视图
  - 资源自己的 Feature / Unit 测试

## 技能映射

- `frontend-design`：优先给后台壳共享 UI 线、页面壳层线和 Dashboard 线使用，提升视觉一致性和页面完成度。
- `webapp-testing`：优先给后台壳各资源页、动作页和 Dashboard 做浏览器烟雾测试，补足 PHPUnit 之外的页面验证。
- `upgrade-stripe`：优先给支付通道线使用，处理 Stripe 相关 SDK、API 和升级切口。
- `stripe-best-practices`：优先给支付通道线使用，约束支付配置、安全边界和 Stripe 集成方式。

## 子代理运行协议

- 默认一次拉起 10 个子代理，围绕 10 个互不重叠的工作面并行推进
- 每个子代理只允许改自己工作面的控制器、服务、视图和测试
- 共享文件只允许主线程改动：
  - `app/Admin/routes.php`
  - `app/Service/AdminShellResourceRegistry.php`
  - `resources/views/admin-shell/layout.blade.php`
  - `public/assets/avatar/css/admin-shell.css`
  - `tests/Unit/AdminShellPageStructureTest.php`
  - `docs/refactor-upgrade-log.md`
- 子代理完成后先给出最小可验证结论，再由主线程统一集成和回归
- 若某个工作面需要碰共享文件，先冻结该工作面，转由主线程单独收口

## 十个并行工作面

### 1. 系统设置线

目标：

- 继续拆出独立的真实配置动作页
- 将“系统设置概览”逐步演化成分组配置中心

所有权：

- `app/Http/Controllers/AdminShell/SystemSettingActionController.php`
- `app/Service/AdminShellSystemSettingPageService.php`
- `resources/views/admin-shell/system-setting/*`
- `tests/Feature/AdminShellSystemSettingControllerTest.php`

### 2. 邮件模板线

目标：

- 继续增强邮件模板后台壳能力
- 优先补模板预览、使用说明或低风险辅助动作

所有权：

- `app/Http/Controllers/AdminShell/EmailTemplate*.php`
- `app/Service/AdminShellEmailTemplatePageService.php`
- `app/Service/EmailTemplateActionService.php`
- `resources/views/admin-shell/emailtpl/*`
- `tests/Feature/AdminShellEmailTemplateControllerTest.php`

### 3. 优惠码线

目标：

- 增强优惠码壳页的业务可操作性
- 优先补低风险批量/复制/辅助生成能力

所有权：

- `app/Http/Controllers/AdminShell/Coupon*.php`
- `app/Service/AdminShellCouponPageService.php`
- `app/Service/CouponActionService.php`
- `resources/views/admin-shell/coupon/*`
- `tests/Feature/AdminShellCouponControllerTest.php`

### 4. 商品分类线

目标：

- 把商品分类页做成更完整的轻量 CRUD 标杆
- 优先补更好的详情表达、排序/状态维护体验

所有权：

- `app/Http/Controllers/AdminShell/GoodsGroup*.php`
- `app/Service/AdminShellGoodsGroupPageService.php`
- `app/Service/GoodsGroupActionService.php`
- `resources/views/admin-shell/goods-group/*`
- `tests/Feature/AdminShellGoodsGroupControllerTest.php`

### 5. 支付通道线

目标：

- 继续降低支付配置页的风险感和混乱度
- 优先做密钥展示/编辑体验、安全提示和字段分组

所有权：

- `app/Http/Controllers/AdminShell/Pay*.php`
- `app/Service/AdminShellPayPageService.php`
- `app/Service/PayActionService.php`
- `resources/views/admin-shell/pay/*`
- `tests/Feature/AdminShellPayControllerTest.php`

### 6. 商品管理线

目标：

- 持续增强商品资源的后台壳承载能力
- 优先补商品编辑体验、复杂字段分组和表单可读性

所有权：

- `app/Http/Controllers/AdminShell/Goods*.php`
- `app/Service/AdminShellGoodsPageService.php`
- `app/Service/GoodsActionService.php`
- `resources/views/admin-shell/goods/*`
- `tests/Feature/AdminShellGoodsControllerTest.php`

### 7. 订单管理线

目标：

- 继续把订单页从“只可看、可低风险编辑”推进到更好维护
- 优先补低风险辅助视图和人工维护体验

所有权：

- `app/Http/Controllers/AdminShell/Order*.php`
- `app/Service/AdminShellOrderPageService.php`
- `app/Service/OrderActionService.php`
- `resources/views/admin-shell/order/*`
- `tests/Feature/AdminShellOrderControllerTest.php`

### 8. 卡密管理线

目标：

- 继续增强卡密资源的壳页能力
- 优先补导入后的维护体验、批量入口和辅助动作

所有权：

- `app/Http/Controllers/AdminShell/Carmi*.php`
- `app/Service/AdminShellCarmisPageService.php`
- `app/Service/CarmiActionService.php`
- `resources/views/admin-shell/carmis/*`
- `tests/Feature/AdminShellCarmisControllerTest.php`

### 9. 后台总览线

目标：

- 把后台壳 Dashboard 做成真正的后台首页
- 优先补健康状态、快捷入口和运营视图

所有权：

- `app/Http/Controllers/AdminShell/DashboardShellController.php`
- `app/Service/AdminShellDashboardPageService.php`
- `resources/views/admin-shell/dashboard/*`
- `tests/Feature/AdminShellDashboardControllerTest.php`
- `tests/Unit/AdminShellDashboardPageServiceTest.php`

### 10. 共享 UI 壳层线

目标：

- 打磨后台壳的共享视觉底座
- 保持所有新页的视觉统一和移动端可用

所有权：

- `resources/views/admin-shell/layout.blade.php`
- `resources/views/admin-shell/partials/*`
- `public/assets/avatar/css/admin-shell.css`

## 首批十子代理建议

以下是当前最适合同时开跑的一组工作面，原则是互不抢共享文件，先各自把页面壳层和动作页做厚，再由主线程统一接线：

1. 系统设置动作页子代理，继续拆基础、品牌、邮件、订单、通知、体验等配置页。
2. 邮件模板子代理，补模板预览、说明、辅助动作和更完整表单体验。
3. 优惠码子代理，补批量、复制、辅助生成和更清晰的详情表达。
4. 商品分类子代理，补排序、状态、详情和轻量 CRUD 体验。
5. 支付通道子代理，补密钥遮罩、字段分组、风险提示和安全表达。
6. 商品管理子代理，补复杂字段分组、编辑体验和维护友好度。
7. 订单管理子代理，补低风险编辑、人工维护体验和详情可读性。
8. 卡密管理子代理，补导入后的维护体验、辅助动作和批量入口。
9. 后台总览子代理，补健康状态、快捷入口和运营视图。
10. 共享 UI 壳层子代理，继续打磨后台壳布局、卡片、表格、表单和移动端适配。

## 集成节拍

1. 子代理各自完成页面或服务增强后，先保留本地变更并给出简短结果。
2. 主线程统一读取各路变更，优先合并不冲突的资源页。
3. 若存在共享文件冲突，先收口共享文件，再回头集成各资源页。
4. 每个批次合并后都跑一次全量 PHPUnit。
5. 回归稳定后补升级日志，再推送 `master`。

## 集成顺序

主线程默认按下面顺序收口：

1. 资源级功能线先落
2. 共享 UI 壳层再收
3. 主线程接入共享注册表 / 路由 / 权限白名单
4. 跑全量 PHPUnit
5. 更新升级日志
6. 推送 `master`

## 当前说明

- 本计划用于当前阶段的大规模并行推进
- 若某条线需要改共享注册表或共享路由，由主线程统一处理
- 若两条线存在潜在冲突，优先保持资源级写入隔离，再由主线程二次集成
