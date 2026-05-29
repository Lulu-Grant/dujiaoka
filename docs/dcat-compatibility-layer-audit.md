# Dcat 最小兼容层审计

审计日期：2026-05-30

## 当前结论

当前后台主承载已经切到后台壳，旧 Dcat Admin 不再承载业务 CRUD、Grid、Form、Show 或批量动作实现。

现阶段仍保留 Dcat 兼容层，原因不是继续使用旧页面，而是后台登录、认证中间件、权限白名单、历史入口跳转和包启动流程仍需要一层过渡引导。

因此当前口径统一为：

- 后台壳是主承载。
- Dcat 是过渡兼容层。
- `config/admin.php` 和 `routes/admin/routes.php` 暂不删除。
- 新业务动作不得回写到旧 Dcat 页面或 `app/Admin` 目录。

## 保留文件与职责

### `config/admin.php`

仍保留的职责：

- 提供 Dcat 包启动所需配置。
- 定义后台路由前缀、域名和中间件。
- 绑定后台认证控制器到 `App\Http\Controllers\AdminShell\AuthShellController`。
- 继续使用 `Dcat\Admin\Models\Administrator` 作为过渡期后台管理员模型。
- 通过 `admin.permission.except` 放行后台壳 dashboard、认证页和注册表派生出的 `/admin/v2/*` 权限白名单。
- 保留菜单、上传、表格等 Dcat 配置，作为包启动与历史后台数据兼容项。

不再承担的职责：

- 不再作为业务页面的字段合同来源。
- 不再新增 Dcat Grid / Form / Show 页面。
- 不再作为新增后台批量动作的入口。

### `routes/admin/routes.php`

仍保留的职责：

- 调用 `Admin::routes()` 注册 Dcat 认证相关路由。
- 在 `admin` 前缀和后台中间件下挂载 `/admin/v2/dashboard`。
- 通过 `AdminShellRouteRegistrar` 注册后台壳资源页与动作页。
- 为历史 `/admin/*` 链接提供 302 跳转，保留 query string。

不再承担的职责：

- 不再加载 `app/Admin/routes.php`。
- 不再依赖旧 `App\Admin\Controllers` 业务控制器。
- 不再承载业务写入逻辑。

### `LegacyAdminShellRedirectService`

仍保留的职责：

- 将旧 `/admin/*` 入口跳转到对应 `/admin/v2/*`。
- 保留原始 query string，确保旧书签、筛选链接和外部引用不丢上下文。

不再承担的职责：

- 不做权限决策。
- 不做业务查询。
- 不做业务写入。

## 当前旧入口清单

资源入口：

- `/admin/goods` -> `/admin/v2/goods`
- `/admin/goods-group` -> `/admin/v2/goods-group`
- `/admin/carmis` -> `/admin/v2/carmis`
- `/admin/coupon` -> `/admin/v2/coupon`
- `/admin/emailtpl` -> `/admin/v2/emailtpl`
- `/admin/pay` -> `/admin/v2/pay`
- `/admin/order` -> `/admin/v2/order`

资源动作：

- `index`
- `create`，仅 `goods / goods-group / carmis / coupon / emailtpl / pay`
- `show`
- `edit`

特殊入口：

- `/admin` -> `/admin/v2/dashboard`
- `/admin/import-carmis` -> `/admin/v2/carmis/import`
- `/admin/system-setting` -> `/admin/v2/system-setting`
- `/admin/email-test` -> `/admin/v2/email-test`

## 测试护栏

当前测试要求：

- `/admin` 跳转到 `/admin/v2/dashboard`。
- 旧资源入口跳转到对应 `/admin/v2/*`。
- 所有保留旧入口必须保留 query string。
- `app/Admin` 目录不得重新成为兼容层前提。
- `config('admin.directory')` 指向 `routes/admin`，而不是旧 `app/Admin`。

对应测试：

- [LegacyAdminShellRedirectControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Feature/LegacyAdminShellRedirectControllerTest.php)

## 删除或瘦身规则

暂不删除以下文件：

- [config/admin.php](/Users/apple/Documents/dujiaoshuka/config/admin.php)
- [routes/admin/routes.php](/Users/apple/Documents/dujiaoshuka/routes/admin/routes.php)

后续只有同时满足以下条件，才评估删除或替换：

- 后台登录、退出、账号设置、认证 guard 和中间件已有非 Dcat 替代实现。
- `/admin/v2/*` 权限白名单不再依赖 Dcat 配置。
- 所有旧 `/admin/*` 外链已经完成迁移，或有单独的废弃策略与测试。
- 全量 PHPUnit、后台 smoke 和旧入口跳转测试通过。

禁止项：

- 不为了减少文件数量而删除兼容关键文件。
- 不在兼容层新增业务写入。
- 不恢复 `app/Admin` 生产目录。
- 不新增 Dcat 绑定型后台能力。

## beta.1 验收口径

v3.0.0-beta.1 只要求 Dcat 兼容层职责清楚、旧入口可测、文档口径一致。

beta.1 不要求：

- 完全删除 Dcat 包。
- 删除 `config/admin.php`。
- 删除 `routes/admin/routes.php`。
- 删除所有旧 `/admin/*` 别名。
