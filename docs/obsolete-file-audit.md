# 废弃文件审计

日期：2026-04-12

这份审计用于记录仓库中已经明显退场、可以删除，或者暂时保留的旧文件。目标是先删掉引用已经断干净的低风险残留，再逐步收缩其余旧层。

## 可立即删除

这批文件已经没有运行时入口，也没有代码引用。

### 旧 Dcat 表单壳

- `app/Admin/Forms/EmailTest.php`
- `app/Admin/Forms/ImportCarmis.php`
- `app/Admin/Forms/SystemSetting.php`

依据：

- `rg -F "App\\Admin\\Forms\\..." app config routes tests resources` 无引用
- 邮件测试、系统设置、卡密导入都已经由 `AdminShell` 控制器与普通服务承接

### 旧 Dcat Repository 壳

- `app/Admin/Repositories/Carmis.php`
- `app/Admin/Repositories/Coupon.php`
- `app/Admin/Repositories/Emailtpl.php`
- `app/Admin/Repositories/Goods.php`
- `app/Admin/Repositories/GoodsGroup.php`
- `app/Admin/Repositories/Order.php`
- `app/Admin/Repositories/Pay.php`

依据：

- `rg -F "App\\Admin\\Repositories\\..." app config routes tests resources` 无引用
- 旧资源控制器已经退化为兼容跳转层，不再构建 Dcat Grid / Form / Show

### 零引用静态资源

- `public/assets/avatar/images/favicon.ico`
- `public/assets/avatar/images/green.webp`
- `public/assets/avatar/images/usdt.webp`
- `public/assets/avatar/images/yellow.webp`
- `public/assets/avatar/fonts/Nunito-Bold.eot`
- `public/assets/avatar/fonts/Nunito-Bold.ttf`
- `public/assets/avatar/fonts/Nunito-Bold.woff`
- `public/assets/avatar/fonts/Nunito-Light.ttf`
- `public/assets/avatar/fonts/Nunito-Light.woff`
- `public/assets/avatar/fonts/Nunito-Regular.eot`
- `public/assets/avatar/fonts/Nunito-Regular.ttf`
- `public/assets/avatar/fonts/Nunito-Regular.woff`
- `public/assets/avatar/fonts/Nunito-SemiBold.eot`
- `public/assets/avatar/fonts/Nunito-SemiBold.ttf`
- `public/assets/avatar/fonts/Nunito-SemiBold.woff`
- `public/assets/avatar/fonts/summernote.eot`
- `public/assets/avatar/fonts/summernote.ttf`
- `public/assets/avatar/fonts/summernote.woff`

依据：

- `rg` 检查 `resources/`、`app/`、`docs/`、`README.md` 后均无引用
- 当前前台主题与后台壳都没有使用这批字体和图片

## 暂时保留

### 旧后台兼容入口

- `app/Admin/Controllers/*.php`
- `app/Admin/routes.php`

原因：

- 旧 `/admin/*` 路径仍需要兼容跳转到 `/admin/v2/*`
- 当前仍依赖 Dcat 的路由组与后台中间件体系

### 旧安装历史文件

- `database/sql/install.sql`

原因：

- 已退出安装主路径，但 CI 仍在使用它恢复测试库
- 在移除前，需要先把 CI 测试库准备过程切到 migration/seed

### 旧 dashboard 图表与布局壳

- `app/Admin/Charts/*.php`
- `app/Service/AdminDashboardLayoutService.php`
- `resources/views/admin/dashboard/title.blade.php`

原因：

- `build()` 主体已经基本退场，但 `HomeController::title()` 仍复用 `titleView()`
- 这批可以在下一轮“旧后台首页彻底退场”时一起收口

### 主题内部说明文件

- `resources/views/avatar/readme.md`

原因：

- 运行时未引用，但仍带有主题来源与维护说明
- 是否保留取决于仓库是否继续需要主题署名/来源说明

### 历史审计文档

- `docs/project-audit-notes.md`

原因：

- 内容已经明显被 `current-baseline-audit.md`、`legacy-runtime-baseline.md`、`runtime-compatibility-blockers.md` 分流覆盖
- README 已改为以当前基线审计为主入口，旧文档不再承担主导航职责

## 下一批建议目标

1. 把 `SystemSettingController`、`EmailTestController` 这类兼容跳转控制器进一步收口
2. 让 CI 测试库准备脱离 `install.sql` 旧路径
3. 评估旧 dashboard 图表壳和 `avatar/readme.md` 是否一并移除
4. 评估 `app/Admin/Actions/Post/*`、`app/Service/AdminGridRestoreActionService.php`、`app/Admin/bootstrap.php` 这批只剩测试或样板意义的旧文件

## 2026-04-12 基线回归补充

- GitHub Actions 的测试库准备已切到 `scripts/prepare-test-db`
- 当前 CI 不再通过 `install.sql` 导入测试库
- `install.sql` 已从仓库主路径移除，只剩文档层历史说明

## 2026-04-12 第四批已执行清理

### 已删除

- `app/Admin/routes.php`
- 空目录 `app/Admin`

### 依据

- 旧后台目录中的生产文件已经全部退场，`app/Admin/routes.php` 是最后一个残留入口壳
- Dcat 仍需要一个 `admin_path('routes.php')` 兼容入口，因此后台路由引导已迁到 `routes/admin/routes.php`
- 新路由引导文件已去掉对 `config('admin.route.namespace')` 的实际运行时依赖，旧 `App\\Admin\\Controllers` 命名空间只剩 Dcat 工具链兼容意义

### 当前结论

- 仓库已经不再保留 `app/Admin` 目录
- 旧后台主承载层现在只剩 `config/admin.php` 和 `routes/admin/routes.php` 这组兼容配置
- 后续如果继续压缩 Dcat 依赖，优先目标会是后台兼容配置本身，而不再是 `app/Admin` 文件清理

## 2026-04-12 第二批已执行清理

### 已删除

- `app/Admin/Controllers/CarmisController.php`
- `app/Admin/Controllers/CouponController.php`
- `app/Admin/Controllers/EmailTestController.php`
- `app/Admin/Controllers/EmailtplController.php`
- `app/Admin/Controllers/GoodsController.php`
- `app/Admin/Controllers/GoodsGroupController.php`
- `app/Admin/Controllers/HomeController.php`
- `app/Admin/Controllers/OrderController.php`
- `app/Admin/Controllers/PayController.php`
- `app/Admin/Controllers/SystemSettingController.php`
- `app/Admin/Actions/Post/Restore.php`
- `app/Admin/Actions/Post/BatchRestore.php`
- `app/Admin/bootstrap.php`
- `app/Admin/Charts/DashBoard.php`
- `app/Admin/Charts/PayoutRateCard.php`
- `app/Admin/Charts/SalesCard.php`
- `app/Admin/Charts/SuccessOrderCard.php`
- `app/Service/AdminDashboardLayoutService.php`
- `app/Service/AdminGridRestoreActionService.php`
- `app/Service/AdminPageCardService.php`
- `app/Service/SoftDeleteRestoreService.php`
- `resources/views/admin/dashboard/title.blade.php`
- `tests/Unit/AdminDashboardLayoutServiceTest.php`
- `tests/Unit/AdminGridRestoreActionServiceTest.php`
- `tests/Unit/AdminPageCardServiceTest.php`
- `tests/Unit/SoftDeleteRestoreServiceTest.php`
- `resources/views/avatar/readme.md`
- `docs/project-audit-notes.md`

### 依据

- 旧 `/admin/*` 兼容跳转已直接收进 `app/Admin/routes.php`
- 这些控制器删除前已不包含任何业务逻辑，只负责把旧入口转向 `/admin/v2/*`
- 上述文件在删除前只剩测试引用或互相弱引用，没有生产控制器、路由、页面服务继续调用
- `/admin` 已经直接重定向到 `/admin/v2/dashboard`，旧 Dcat dashboard 不再承担主入口职责
- 恢复动作链已不再被任何生产 Grid 控制器挂载
- 主题内部说明文件和旧项目审计文档都不再作为仓库主导航入口
