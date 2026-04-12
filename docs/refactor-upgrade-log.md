# 重构升级日志

## 记录规则

- 本文档用于记录本仓库现代化改造过程中的重要节点。
- 重要节点包括：评估结论、运行时基线恢复、测试体系建立、架构拆分、部署模型变更、安全治理、升级阻塞点确认、重要回归修复。
- 每次进入新的重要节点时，都应在本文档追加一条记录。
- 每条记录尽量包含：日期、阶段、变更摘要、影响范围、验证结果、下一步。

---

## 2026-04-12 阶段日志

### 148. 第二批旧 Dcat 壳文件清理

摘要：

- 删除了只剩测试或历史兼容意义的旧 Dcat 恢复动作链：
  - [app/Admin/Actions/Post/Restore.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Actions/Post/Restore.php)
  - [app/Admin/Actions/Post/BatchRestore.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Actions/Post/BatchRestore.php)
  - [app/Service/AdminGridRestoreActionService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminGridRestoreActionService.php)
  - [app/Service/SoftDeleteRestoreService.php](/Users/apple/Documents/dujiaoshuka/app/Service/SoftDeleteRestoreService.php)
- 删除了旧 Dcat dashboard 兼容壳：
  - [app/Admin/Charts/DashBoard.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Charts/DashBoard.php)
  - [app/Admin/Charts/PayoutRateCard.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Charts/PayoutRateCard.php)
  - [app/Admin/Charts/SalesCard.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Charts/SalesCard.php)
  - [app/Admin/Charts/SuccessOrderCard.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Charts/SuccessOrderCard.php)
  - [app/Service/AdminDashboardLayoutService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminDashboardLayoutService.php)
  - [resources/views/admin/dashboard/title.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin/dashboard/title.blade.php)
- 删除了已无运行时意义的 Dcat 样板文件：
  - [app/Admin/bootstrap.php](/Users/apple/Documents/dujiaoshuka/app/Admin/bootstrap.php)
  - [app/Service/AdminPageCardService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminPageCardService.php)
- 同步删除了与上述旧壳绑定的单元测试，并把 [HomeController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/HomeController.php) 中不再需要的 `title()` 兼容方法去掉，仅保留 `/admin -> /admin/v2/dashboard` 的跳转职责。

影响范围：

- 旧 Dcat dashboard 与恢复动作残留文件继续减少
- `app/Admin` 下的样板和空壳进一步收缩
- 后台壳已经足够承接首页和高频入口，不再依赖旧 dashboard 兼容视图

验证：

- 删除后继续执行全量 PHPUnit 与后台壳烟雾检查

下一步：

- 继续审计剩余旧兼容控制器与历史文档，清理第三批废弃文件

### 147. 第一批废弃文件审计与清理

摘要：

- 新增了 [docs/obsolete-file-audit.md](/Users/apple/Documents/dujiaoshuka/docs/obsolete-file-audit.md)，把当前仓库里的废弃候选文件分成“可立即删除 / 暂时保留 / 下一批目标”三类，先把旧后台、旧安装、静态资源的边界理清。
- 删除了 3 个已经没有任何运行时入口的旧 Dcat 表单壳：
  - [app/Admin/Forms/EmailTest.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Forms/EmailTest.php)
  - [app/Admin/Forms/ImportCarmis.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Forms/ImportCarmis.php)
  - [app/Admin/Forms/SystemSetting.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Forms/SystemSetting.php)
- 删除了 7 个已经没有任何代码引用的旧 Dcat Repository 壳：
  - [app/Admin/Repositories/Carmis.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Repositories/Carmis.php)
  - [app/Admin/Repositories/Coupon.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Repositories/Coupon.php)
  - [app/Admin/Repositories/Emailtpl.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Repositories/Emailtpl.php)
  - [app/Admin/Repositories/Goods.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Repositories/Goods.php)
  - [app/Admin/Repositories/GoodsGroup.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Repositories/GoodsGroup.php)
  - [app/Admin/Repositories/Order.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Repositories/Order.php)
  - [app/Admin/Repositories/Pay.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Repositories/Pay.php)
- 删除了 18 个零引用的 `avatar` 主题静态资源，包括未使用的 `favicon.ico`、4 张旧图片和一批未被任何前台或后台页面使用的字体文件。

影响范围：

- 旧 Dcat 后台遗留文件体积继续缩小
- 仓库中的死文件和零引用静态资源减少
- 后续清理 `app/Admin/Actions/Post/*`、`app/Admin/bootstrap.php`、旧 dashboard 兼容壳时有了更明确的审计边界

验证：

- 删除后再次检查 `App\\Admin\\Forms\\*`、`App\\Admin\\Repositories\\*` 的引用，结果为空
- 全量 PHPUnit 回归待本轮执行

下一步：

- 继续清理只剩测试或样板意义的旧 Dcat 文件
- 逐步让 CI 测试库准备过程脱离 `install.sql`

### 146. CouponController 退壳瘦身

摘要：

- [app/Admin/Controllers/CouponController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/CouponController.php) 进一步瘦身为仅保留 `index`、`create`、`show`、`edit` 四个兼容跳转入口，移除了已不再被使用的 Dcat `Grid`、`Show`、`Form` 旧实现和无效 import。
- [tests/Feature/LegacyAdminShellRedirectControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Feature/LegacyAdminShellRedirectControllerTest.php) 补齐了 coupon 旧入口的跳转断言，覆盖列表、创建、详情和编辑四个路径，并保留了列表页的查询串转发，确保兼容层仍只负责转发，不改变现有 URL 和跳转行为。
- 这次收口没有改动任何 coupon 相关 URL，也没有改变新后台壳的落点，只是把旧 Dcat 控制器里的冗余实现清掉，继续缩小旧层体积。

影响范围：

- `app/Admin/Controllers/CouponController.php` 的旧 Dcat 承载面
- coupon 旧入口兼容跳转行为
- 后台壳继续作为优惠码管理的主承载层

验证：

- `./scripts/php74 vendor/bin/phpunit tests/Feature/LegacyAdminShellRedirectControllerTest.php` 通过，覆盖 coupon 旧入口跳转断言
- `./scripts/php74 vendor/bin/phpunit` 后续再做一次完整回归确认

下一步：

- 继续沿着后台壳扩容和旧 Dcat 降耦合主线推进，优先处理剩余高频后台页和更复杂的操作型页面。

### 144. 卡密旧控制器退壳瘦身

摘要：

- [CarmisController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/CarmisController.php) 现在只保留 `index / create / show / edit / importCarmis` 的兼容跳转层，旧的 `Grid / Show / Form` 构建和无效 import 代码已删除。
- [tests/Feature/LegacyAdminShellRedirectControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Feature/LegacyAdminShellRedirectControllerTest.php) 新增对 `/admin/carmis`、`/admin/carmis/789` 和 `/admin/carmis/789/edit` 的跳转断言，确保卡密旧入口继续稳定转到新后台壳。

影响范围：

- 卡密旧 Dcat 资源控制器进一步瘦身
- 旧后台的卡密浏览、编辑和详情入口全部退到新后台壳
- 跳转行为保持不变，只是移除了不再使用的旧实现

验证：

- `./scripts/php74 vendor/bin/phpunit tests/Feature/LegacyAdminShellRedirectControllerTest.php` 预期通过
- `./scripts/php74 vendor/bin/phpunit` 预期通过

下一步：

- 继续沿着后台壳扩容和旧 Dcat 降耦合主线推进，优先处理剩余高频后台页和更复杂的操作型页面。

### 13. 继续压缩旧后台订单控制器

摘要：

- 将 [app/Admin/Controllers/OrderController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/OrderController.php) 进一步瘦身为兼容跳转层，仅保留 `index`、`show`、`edit` 三个入口。
- 删除了旧 `OrderController` 中已不再使用的 Dcat `Grid`、`Show`、`Form` 旧实现以及对应的多余 import，避免旧资源控制器继续承载重复渲染逻辑。
- 在 [tests/Feature/LegacyAdminShellRedirectControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Feature/LegacyAdminShellRedirectControllerTest.php) 新增订单 show 旧路由的跳转断言，确保 `/admin/order/{id}` 仍稳定进入新后台壳，不改变既有 URL 与跳转行为。

影响范围：

- 旧后台订单资源控制器的承载面继续收缩
- 旧 `admin/order` 路由仍保留，但只承担兼容跳转
- 订单后台逻辑进一步向新壳集中

验证：

- 订单旧路由的跳转断言已补充，后续可通过全量 PHPUnit 再次确认。

下一步：

- 继续沿着后台壳扩容和旧 Dcat 降耦合主线推进，优先处理剩余高频后台页和更复杂的操作型页面。

### 145. EmailtplController 退壳瘦身

摘要：

- [EmailtplController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/EmailtplController.php) 继续收口为兼容跳转层，仅保留 `index`、`create`、`show`、`edit` 四个入口，删除了旧 Dcat 的 `Grid`、`Show`、`Form` 旧实现以及相关无效 import。
- [LegacyAdminShellRedirectControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Feature/LegacyAdminShellRedirectControllerTest.php) 补充了邮件模板的创建、详情和编辑旧入口跳转断言，确保 `/admin/emailtpl/*` 仍稳定进入新后台壳，不改变既有 URL 和跳转行为。

影响范围：

- 旧后台邮件模板资源控制器的承载面继续收缩
- 旧 `admin/emailtpl` 路由仍保留，但只承担兼容跳转
- 邮件模板后台逻辑进一步向新壳集中

验证：

- 邮件模板旧路由跳转断言已补充，后续可通过全量 PHPUnit 再次确认。

下一步：

- 继续沿着后台壳扩容和旧 Dcat 降耦合主线推进，优先处理剩余高频后台页和更复杂的操作型页面。

### 144. GoodsController 退壳瘦身

摘要：

- [GoodsController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/GoodsController.php) 现在只保留 `index/create/show/edit` 四个兼容跳转入口，彻底移除了旧 Dcat 的 `Grid`、`Show`、`Form` 构建实现和已不再使用的 import。
- [tests/Feature/LegacyAdminShellRedirectControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Feature/LegacyAdminShellRedirectControllerTest.php) 补充了商品列表、创建页和编辑页的旧入口跳转护栏，确保兼容层仍然只负责转发，不改变 URL 和跳转行为。
- 这次瘦身没有调整任何商品相关 URL，也没有改变新后台壳的路由落点，只把旧 Dcat 控制器里的冗余实现清掉了，进一步降低旧层体积。

影响范围：

- `app/Admin/Controllers/GoodsController.php` 的旧 Dcat 承载面
- 商品旧入口兼容跳转行为
- 后台壳继续作为商品管理的主承载层

验证：

- `./scripts/php74 vendor/bin/phpunit tests/Feature/LegacyAdminShellRedirectControllerTest.php` 通过
- `./scripts/php74 vendor/bin/phpunit` 通过，结果待本轮完整回归确认

下一步：

- 继续沿着后台壳扩容和旧 Dcat 降耦合主线推进，优先处理剩余高频后台页和更复杂的操作型页面。

### 145. GoodsGroupController 退壳瘦身

摘要：

- 将 [app/Admin/Controllers/GoodsGroupController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/GoodsGroupController.php) 进一步瘦身为兼容跳转层，仅保留 `index`、`create`、`show`、`edit` 四个入口。
- 删除了旧 `GoodsGroupController` 中已不再使用的 Dcat `Grid`、`Show`、`Form` 旧实现以及对应的多余 import，避免旧资源控制器继续承载重复渲染逻辑。
- 旧路由保持不变，仍通过 [tests/Feature/LegacyAdminShellRedirectControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Feature/LegacyAdminShellRedirectControllerTest.php) 约束 `goods-group` 的兼容跳转行为。

影响范围：

- 旧后台商品分类资源控制器的承载面继续收缩
- 旧 `admin/goods-group` 路由仍保留，但只承担兼容跳转
- 商品分类后台逻辑进一步向新壳集中

验证：

- 仅删除已不使用的旧 Dcat 构建实现，不改变既有 URL 和跳转行为。

下一步：

- 继续沿着后台壳扩容和旧 Dcat 降耦合主线推进，优先处理剩余高频后台页和更复杂的操作型页面。

### 11. 强化后台总览的快捷入口与运营提示

摘要：

- 将 [AdminShellDashboardPageService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellDashboardPageService.php) 扩展为更偏“指挥台”的页面数据提供者，优先补入 `账号设置`、`系统设置分组`、`高频管理页` 三类快捷入口。
- 更新 [resources/views/admin-shell/dashboard/index.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin-shell/dashboard/index.blade.php)，新增快捷分组区块与本日操作建议区块，让首页从“统计+概览”进一步变成“巡检+跳转+处置”的入口面板。
- 同步扩展 [AdminShellDashboardControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Feature/AdminShellDashboardControllerTest.php) 与 [AdminShellDashboardPageServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AdminShellDashboardPageServiceTest.php)，锁定新快捷入口与提示文案。

影响范围：

- 后台壳首页
- 首页快捷入口组织方式
- 首页运营提示表达

验证：

- 页面服务与 Feature 测试均已更新，后续再跑全量 PHPUnit 与浏览器烟雾检查确认展示效果。

下一步：

- 持续沿“后台壳扩容 + 旧 Dcat 降耦合”主线推进，优先把更多高频页压到新后台壳里。

### 10. 建立十路并行开发总纲

摘要：

- 制定 [parallel-development-plan.md](/Users/apple/Documents/dujiaoshuka/docs/parallel-development-plan.md)，把后台壳扩容、旧 Dcat 降耦合、支付层收口和共享 UI 打磨拆成十条可并行推进的工作面。
- 明确并行开发的冲突规约、共享文件边界与主线程集成节拍，后续优先让子代理处理资源级页面与服务，主线程负责共享接线、全量测试和主线提交。

影响范围：

- 协作方式
- 任务拆分
- 后台壳整体推进节奏

验证：

- 并行开发总纲已落库并可作为后续默认执行方式。

下一步：

- 按十路并行计划继续推进后台壳扩容与旧 Dcat 降耦合。

### 12. 扩面后台壳烟雾检查

摘要：

- 将 [tests/Browser/admin-shell-smoke.sh](/Users/apple/Documents/dujiaoshuka/tests/Browser/admin-shell-smoke.sh) 扩展为登录后关键路径巡检，新增 `auth/setting`、`goods/create`、`emailtpl/create` 等页面检查。
- 同步更新 [README.md](/Users/apple/Documents/dujiaoshuka/README.md) 与 [docs/local-dev-quickstart.md](/Users/apple/Documents/dujiaoshuka/docs/local-dev-quickstart.md)，让本地快速拉站说明与 smoke 覆盖范围保持一致。
- 保持脚本仍然是轻量 `curl` 路线，没有引入额外依赖，方便在当前本地流程中直接使用。

影响范围：

- 本地快速拉站
- 后台登录后路径回归
- 烟雾检查覆盖面

验证：

- 更新后 smoke 脚本将继续兼容现有后台登录流程。

下一步：

- 继续把后台壳的其他稳定动作页纳入烟雾检查，优先保持低依赖、高可运行性。

## 2026-04-02 阶段日志

### 0. 建立大整改执行方案并切换为按阶段连续推进

摘要：

- 新增 [rectification-execution-plan.md](/Users/apple/Documents/dujiaoshuka/docs/rectification-execution-plan.md)，把整改目标、阶段、退出标准、同步规则和默认执行顺序固定下来。
- 明确后续默认按阶段连续执行，不再在每个小节点停下来等待确认。
- 继续保留“重要节点必须记日志、稳定节点应同步 GitHub `master`”的节奏，避免改造失去可追踪性。

影响范围：

- 整体整改节奏
- 任务执行方式
- 文档化治理规则

验证：

- 方案文档已落库，并已接入 [modernization-roadmap.md](/Users/apple/Documents/dujiaoshuka/docs/modernization-roadmap.md)。

下一步：

- 按方案继续执行支付层整治收口，优先清理剩余的支付控制器。

### 1. 完成第一轮重大评估

摘要：

- 确认项目属于高业务价值但高技术债的遗留 Laravel 单体。
- 确认核心问题集中在旧运行时、旧依赖、支付耦合、安装方式、测试缺失和部署模式。
- 明确不适合直接跳升到现代 Laravel，应先做基线恢复、测试护栏和核心服务拆分。

影响范围：

- 架构路线
- 风险分级
- 后续重构顺序

产出：

- [modernization-roadmap.md](/Users/apple/Documents/dujiaoshuka/docs/modernization-roadmap.md)
- [project-audit-notes.md](/Users/apple/Documents/dujiaoshuka/docs/project-audit-notes.md)
- [runtime-compatibility-blockers.md](/Users/apple/Documents/dujiaoshuka/docs/runtime-compatibility-blockers.md)

验证：

- 基于仓库结构、依赖清单、安装流程、服务实现完成静态审计。

下一步：

- 恢复可运行的遗留运行时基线。

### 2. 恢复遗留运行时基线

摘要：

- 安装 Composer 与 PHP 7.4，避免直接用本机 PHP 8.5 硬跑 Laravel 6。
- 建立 `scripts/php74` 和 `scripts/composer74`，固定遗留运行时入口。
- 启动 MariaDB 与 Redis，导入 `dujiaoka` / `dujiaoka_test` 数据库。
- 建立 `install.lock`，让项目进入已安装状态。

影响范围：

- 本地开发基线
- 测试执行基线
- 后续升级验证路径

产出：

- [legacy-runtime-baseline.md](/Users/apple/Documents/dujiaoshuka/docs/legacy-runtime-baseline.md)
- [scripts/php74](/Users/apple/Documents/dujiaoshuka/scripts/php74)
- [scripts/composer74](/Users/apple/Documents/dujiaoshuka/scripts/composer74)

验证：

- PHP 7.4 下可执行 Artisan 与 PHPUnit。

下一步：

- 建立真实业务测试护栏。

### 3. 完成仓库与敏感配置基础治理

摘要：

- 将已跟踪的 [`.env`](/Users/apple/Documents/dujiaoshuka/.env) 从 git 跟踪中移除，但保留本地文件。
- 明确当前仓库不应继续把真实环境文件当作版本内容管理。
- 确认安装 SQL 内含默认后台数据，后续需继续推进密钥和默认凭据治理。

影响范围：

- 仓库卫生
- 安全治理起点

验证：

- `git status` 中 `.env` 已从版本跟踪中移除。

下一步：

- 持续推进安装与默认数据的安全治理。

### 4. 建立第一批核心业务测试护栏

摘要：

- 从只有示例测试，推进到覆盖订单主链的真实单元测试。
- 覆盖场景包括：下单创建、自动发货成功、自动发货异常、优惠码使用、优惠码回退、订单查询、订单状态查询、支付前校验、订单过期。
- 后续继续补入批发价计算与订单状态邮件监听器测试。

影响范围：

- 订单主链
- 支付前校验
- 优惠码生命周期
- 订单过期与查询

主要测试文件：

- [OrderProcessServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/OrderProcessServiceTest.php)
- [OrderServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/OrderServiceTest.php)
- [OrderStatusControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/OrderStatusControllerTest.php)
- [PayControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/PayControllerTest.php)
- [OrderExpiredJobTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/OrderExpiredJobTest.php)
- [ExpireOrdersCommandTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/ExpireOrdersCommandTest.php)
- [OrderCreationServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/OrderCreationServiceTest.php)
- [OrderUpdatedListenerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/OrderUpdatedListenerTest.php)

验证：

- 当前全量回归结果为：
  `./scripts/php74 vendor/bin/phpunit`
- 最新结果：`OK (19 tests, 67 assertions)`

下一步：

- 继续把大服务拆成可演进的领域服务。

### 5. 完成“去守护进程”第一阶段改造

摘要：

- 移除“订单超时依赖延迟队列”的设计，改为命令扫描过期订单。
- 新增 [ExpireOrdersCommand.php](/Users/apple/Documents/dujiaoshuka/app/Console/Commands/ExpireOrdersCommand.php)，并接入 Laravel scheduler。
- 新增同步优先的副作用分发机制，默认不需要常驻 `queue:work`。
- 更新 Dockerfile、Debian 手册和 docker-compose，使部署不再依赖项目自带的 `supervisord` / 后台队列 worker。

影响范围：

- 订单过期机制
- 通知与回调执行模型
- Docker 部署方式
- Linux 部署文档

产出：

- [no-daemon-migration-checklist.md](/Users/apple/Documents/dujiaoshuka/docs/no-daemon-migration-checklist.md)
- [SideEffectDispatcherService.php](/Users/apple/Documents/dujiaoshuka/app/Service/SideEffectDispatcherService.php)
- [ExpireOrdersCommand.php](/Users/apple/Documents/dujiaoshuka/app/Console/Commands/ExpireOrdersCommand.php)

验证：

- 订单过期与通知相关测试通过。
- 当前部署说明已改为 `schedule:run` / `orders:expire` 模式。

下一步：

- 继续消减代码与文档里的队列中心化假设。

### 6. 完成订单创建与通知层的第一轮服务拆分

摘要：

- 将订单创建中的价格计算和待支付订单组装提取到 [OrderCreationService.php](/Users/apple/Documents/dujiaoshuka/app/Service/OrderCreationService.php)。
- 将订单完成后的通知、状态邮件和模板发送提取到 [OrderNotificationService.php](/Users/apple/Documents/dujiaoshuka/app/Service/OrderNotificationService.php)。
- [OrderProcessService.php](/Users/apple/Documents/dujiaoshuka/app/Service/OrderProcessService.php) 进一步收敛为订单流程编排器。
- [OrderUpdated.php](/Users/apple/Documents/dujiaoshuka/app/Listeners/OrderUpdated.php) 不再直接拼模板和派邮件，统一走通知服务。

影响范围：

- 订单创建逻辑
- 订单通知逻辑
- 订单状态更新监听器

验证：

- 新增独立服务测试与监听器测试。
- 当前全量回归结果：`OK (19 tests, 67 assertions)`

下一步：

- 继续拆出履约层，把 `processAuto()` / `processManual()` 提升为独立 fulfillment service。

### 7. 完成订单履约层第一轮服务拆分

摘要：

- 将手动履约与自动发货逻辑提取到 [OrderFulfillmentService.php](/Users/apple/Documents/dujiaoshuka/app/Service/OrderFulfillmentService.php)。
- [OrderProcessService.php](/Users/apple/Documents/dujiaoshuka/app/Service/OrderProcessService.php) 不再直接操作卡密与库存履约细节，而是统一委托履约服务。
- 为履约服务新增独立测试，直接守住手动履约与自动履约行为。

影响范围：

- 订单履约逻辑
- 自动发货逻辑
- 手动处理逻辑

验证：

- 新增 [OrderFulfillmentServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/OrderFulfillmentServiceTest.php)
- 当前全量回归结果：`OK (21 tests, 78 assertions)`

下一步：

- 继续压缩 `OrderProcessService`，考虑把支付完成编排也抽成更明确的支付确认 / 状态流转层。

### 8. 完成支付完成编排第一轮服务拆分

摘要：

- 将支付成功后的核心状态流转提取到 [OrderPaymentService.php](/Users/apple/Documents/dujiaoshuka/app/Service/OrderPaymentService.php)。
- 新服务负责：校验订单是否可完成支付、应用支付结果、调用履约服务、增加销量。
- [OrderProcessService.php](/Users/apple/Documents/dujiaoshuka/app/Service/OrderProcessService.php) 进一步退化为更薄的门面，只保留事务边界和支付完成后的统一通知派发。

影响范围：

- 支付完成主链
- 订单状态流转
- 支付完成后的履约入口

验证：

- 新增 [OrderPaymentServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/OrderPaymentServiceTest.php)
- 当前全量回归结果：`OK (22 tests, 82 assertions)`

下一步：

- 继续收敛 `OrderProcessService` 的可变输入状态，评估是否引入明确的 order command / DTO 来替代多组 setter。

### 9. 完成创建订单输入对象化改造

摘要：

- 新增 [CreateOrderData.php](/Users/apple/Documents/dujiaoshuka/app/Service/DataTransferObjects/CreateOrderData.php)，作为创建订单的明确输入对象。
- [OrderProcessService.php](/Users/apple/Documents/dujiaoshuka/app/Service/OrderProcessService.php) 新增 `createOrderFromData()`，将 DTO 作为主路径。
- [OrderController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Home/OrderController.php) 已切换到 DTO 调用链，不再依赖多组 setter 组装下单上下文。
- 旧 setter 入口仍暂时保留，仅用于兼容过渡。

影响范围：

- 订单创建入口
- 控制器到服务层的数据传递方式
- `OrderProcessService` 的可变状态模型

验证：

- 新增 [CreateOrderDataFlowTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/CreateOrderDataFlowTest.php)
- 当前全量回归结果：`OK (23 tests, 90 assertions)`

下一步：

- 评估是否可以彻底删除旧 setter 接口，并继续把控制器验证输出也收敛为更明确的应用层命令对象。

### 10. 完成旧 setter 兼容层下线

摘要：

- 已从 [OrderProcessService.php](/Users/apple/Documents/dujiaoshuka/app/Service/OrderProcessService.php) 删除旧的 setter 接口与无参 `createOrder()` 兼容路径。
- 订单创建已完全切换为 `CreateOrderData` 输入对象模型。
- `OrderProcessService` 不再持有创建订单所需的可变内部状态，职责边界进一步清晰。

影响范围：

- 订单创建服务接口
- 过渡兼容层
- 旧可变状态 API

验证：

- 搜索确认仓库内已无生产代码调用旧 setter 下单接口
- 当前全量回归结果：`OK (23 tests, 90 assertions)`

下一步：

- 继续评估控制器验证与应用层命令建模，逐步把 legacy request-to-service 粘连再往外拆。

### 11. 完成订单创建控制器到应用服务的第一轮收口

摘要：

- 新增 [OrderCheckoutService.php](/Users/apple/Documents/dujiaoshuka/app/Service/OrderCheckoutService.php)，承接“请求校验 -> 组装 CreateOrderData -> 创建订单”的应用层流程。
- [OrderController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Home/OrderController.php) 的创建订单动作已改为直接调用结账应用服务。
- 控制器厚度进一步降低，订单创建的请求到领域服务入口之间有了更明确的应用服务边界。

影响范围：

- 创建订单控制器逻辑
- 请求校验与 DTO 组装职责划分
- 控制器到服务层的调用路径

验证：

- 新增 [OrderCheckoutServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/OrderCheckoutServiceTest.php)
- 当前全量回归结果：`OK (24 tests, 95 assertions)`

下一步：

- 继续梳理控制器层的重复查询与展示逻辑，评估是否需要为订单查询侧也引入更明确的应用服务。

### 12. 完成订单查询控制器到应用服务的第一轮收口

摘要：

- 新增 [OrderQueryService.php](/Users/apple/Documents/dujiaoshuka/app/Service/OrderQueryService.php)，承接结账页订单获取、订单详情查询、状态轮询响应、邮箱查询和浏览器缓存查询。
- [OrderController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Home/OrderController.php) 的查询侧逻辑已大幅收缩，控制器主要保留响应渲染和错误输出。
- 查询侧的异常消息与状态码逻辑开始从控制器分离，控制器职责进一步清晰。

影响范围：

- 订单详情展示
- 订单状态轮询
- 按邮箱查询
- 按浏览器缓存查询

验证：

- 新增 [OrderQueryServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/OrderQueryServiceTest.php)
- 当前全量回归结果：`OK (27 tests, 100 assertions)`

下一步：

- 继续盘点首页下单控制器之外的旧式控制器，优先评估支付入口和安装流程哪些适合先抽应用服务层。

### 13. 完成支付入口控制器到应用服务的第一轮收口

摘要：

- 新增 [PayEntryService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PayEntryService.php)，承接支付前订单校验、支付网关加载和零元订单直达完成逻辑。
- [PayController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/PayController.php) 已改为主要委托支付入口应用服务。
- 为支付入口补充了零元订单直达完成的测试覆盖。

影响范围：

- 支付前订单校验
- 支付网关加载
- 0 元订单直接完成路径

验证：

- 扩展 [PayControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/PayControllerTest.php)
- 当前全量回归结果：`OK (28 tests, 102 assertions)`

下一步：

- 继续评估各支付网关控制器，优先寻找可抽出的公共回调校验 / 完成支付入口，逐步为支付层建立更明确的网关抽象。

### 14. 完成 PayPal 回调样板服务拆分并修复取消支付跳转问题

摘要：

- 新增 [PaypalReturnService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaypalReturnService.php)，承接 PayPal 同步回调中的订单/网关校验与 API context 构建。
- [PaypalPayController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Pay/PaypalPayController.php) 已开始把回调校验逻辑委托给应用服务。
- 修复了 PayPal 取消支付时跳转详情页使用错误参数的问题，原先错误使用 `PayerID`，现已改为正确使用 `orderSN`。

影响范围：

- PayPal 同步回调入口
- PayPal 回调上下文校验
- PayPal 取消支付后的回跳行为

验证：

- 新增 [PaypalPayControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/PaypalPayControllerTest.php)
- 当前全量回归结果：`OK (30 tests, 104 assertions)`

下一步：

- 继续挑选下一个典型支付控制器，优先抽出更通用的“回调上下文校验 + 完成支付入口”模式，逐步逼近统一网关抽象。

### 15. 完成支付回调公共上下文服务抽取，并以 Paysapi 作为第二个网关样板

摘要：

- 新增 [PaymentCallbackService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaymentCallbackService.php)，统一承接“按订单号解析订单与支付网关，并校验 handleroute”的回调上下文逻辑。
- [PaypalReturnService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaypalReturnService.php) 已改为复用回调公共服务。
- [PaysapiController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Pay/PaysapiController.php) 已开始把回调上下文解析和完成支付入口委托给公共服务，自身只保留 Paysapi 特有的签名校验。

影响范围：

- 支付回调公共上下文校验
- Paysapi 异步通知处理
- PayPal 回调服务的公共化依赖

验证：

- 新增 [PaysapiControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/PaysapiControllerTest.php)
- 当前全量回归结果：`OK (32 tests, 108 assertions)`

下一步：

- 继续选择下一个异步通知型网关，把更多“签名校验之外的通用流程”抽到支付层公共服务中，逐步逼近统一网关适配模型。

### 16. 完成 Yipay 第三个异步通知型网关样板接入

摘要：

- [YipayController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Pay/YipayController.php) 已接入 [PaymentCallbackService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaymentCallbackService.php)。
- 现在 Yipay 回调也开始复用统一的订单/网关上下文解析与完成支付入口。
- 控制器自身继续保留易支付特有的签名规则，公共流程不再重复手写。

影响范围：

- 易支付异步通知回调
- 支付回调公共服务的复用范围
- 支付控制器的重复逻辑收敛

验证：

- 新增 [YipayControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/YipayControllerTest.php)
- 当前全量回归结果：`OK (34 tests, 112 assertions)`

下一步：

- 继续选择下一个通知型网关，或开始抽取更上层的“签名计算策略 / 网关适配接口”，让支付层从公共流程抽取逐步进入统一抽象阶段。

### 17. 完成异步通知型网关的统一通知骨架抽取

摘要：

- [PaymentCallbackService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaymentCallbackService.php) 新增 `handleSignedNotification()`，统一承接“回调上下文解析 -> 验签回调 -> 完成订单 -> 返回结果”的公共流程。
- [PaysapiController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Pay/PaysapiController.php) 与 [YipayController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Pay/YipayController.php) 已改为复用这一统一入口。
- 新增 [PaymentCallbackServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/PaymentCallbackServiceTest.php)，给支付回调公共服务本身补上了独立护栏。

影响范围：

- 支付回调公共服务抽象层
- Paysapi 异步通知
- Yipay 异步通知
- 支付完成入口的统一复用

验证：

- 新增 [PaymentCallbackServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/PaymentCallbackServiceTest.php)
- 当前全量回归结果：`OK (36 tests, 116 assertions)`

下一步：

- 继续评估是否引入更明确的网关适配接口，或者继续将更多通知型控制器接入统一通知骨架。

### 18. 完成 Vpay 第四个通知型网关样板接入并修正 handleroute 校验

摘要：

- [VpayController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Pay/VpayController.php) 已接入 [PaymentCallbackService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaymentCallbackService.php) 的统一通知骨架。
- 修复了 Vpay 回调里 `pay_handleroute` 校验缺少前导 `/` 的问题，避免因路由字符串不一致导致合法通知被错误拒绝。
- 支付回调统一骨架的复用范围进一步扩大到第四个典型网关。

影响范围：

- Vpay 异步通知回调
- 支付回调统一骨架的覆盖范围
- Vpay handleroute 校验一致性

验证：

- 新增 [VpayControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/VpayControllerTest.php)
- 当前全量回归结果：`OK (38 tests, 120 assertions)`

下一步：

- 继续决定是扩更多网关到统一骨架，还是开始抽象更明确的网关适配协议层。

### 19. 完成 TokenPay 与 Epusdt 通知型网关接入统一回调骨架

摘要：

- [TokenPayController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Pay/TokenPayController.php) 与 [EpusdtController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Pay/EpusdtController.php) 已改为复用 [PaymentCallbackService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaymentCallbackService.php) 的 `handleSignedNotification()`。
- 两个控制器现在都只保留各自网关特有的签名算法，回调上下文解析、handleroute 校验与支付完成入口不再重复实现。
- 顺手修正了两个控制器里缺少前导 `/` 的 `pay_handleroute` 校验历史问题，继续收敛旧支付代码中的路由字符串不一致风险。

影响范围：

- TokenPay 异步通知回调
- Epusdt 异步通知回调
- 支付回调统一骨架的覆盖范围
- handleroute 校验一致性

验证：

- 新增 [TokenPayControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/TokenPayControllerTest.php)
- 新增 [EpusdtControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/EpusdtControllerTest.php)
- 当前全量回归结果：`OK (42 tests, 128 assertions)`

下一步：

- 继续评估其余支付控制器，决定是继续扩大统一骨架的覆盖范围，还是开始提取更显式的支付网关适配接口。

### 20. 完成 Mapay 通知型网关接入统一回调骨架

摘要：

- [MapayController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Pay/MapayController.php) 已改为复用 [PaymentCallbackService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaymentCallbackService.php) 的 `handleSignedNotification()`。
- Mapay 控制器现在只保留自身的签名串计算规则，订单解析、网关校验和支付完成入口都统一收敛到公共服务层。
- 这样支付层统一回调骨架已经覆盖到第七个典型通知型网关，控制器重复逻辑继续下降。

影响范围：

- Mapay 异步通知回调
- 支付回调统一骨架的覆盖范围
- 支付控制器重复逻辑收敛

验证：

- 新增 [MapayControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/MapayControllerTest.php)
- 当前全量回归结果：`OK (44 tests, 132 assertions)`

下一步：

- 继续挑选剩余支付控制器，评估是否进一步抽出更明确的支付网关适配接口。

### 21. 完成 Coinbase Webhook 服务化改造

摘要：

- 新增 [CoinbaseWebhookService.php](/Users/apple/Documents/dujiaoshuka/app/Service/CoinbaseWebhookService.php)，把 Coinbase 回调中的 payload 解析、签名校验、币种校验、金额校验和完成支付逻辑从控制器中抽出。
- [CoinbaseController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Pay/CoinbaseController.php) 不再直接读取 `php://input` 和 `$_SERVER`，改为使用 Request 内容与 header，便于测试和后续统一接入。
- 顺手修正了 Coinbase 回调里 `pay_handleroute` 历史缺少前导 `/` 的问题，继续减少路由字符串不一致带来的误判。

影响范围：

- Coinbase webhook 回调
- 支付层服务化边界
- Request 驱动的可测试性

验证：

- 新增 [CoinbaseWebhookServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/CoinbaseWebhookServiceTest.php)
- 当前全量回归结果：`OK (46 tests, 136 assertions)`

下一步：

- 继续评估 Payjs / Alipay 这类第三方 SDK 型回调，决定是继续抽 webhook 服务，还是进一步提炼统一网关适配层。

### 22. 完成 Payjs 回调服务化改造

摘要：

- 新增 [PayjsNotificationService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PayjsNotificationService.php)，将 Payjs 回调里的上下文解析、配置注入和完成支付编排从控制器中抽离。
- [PayjsController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Pay/PayjsController.php) 现在只保留入口职责，第三方 SDK 回调开始进入“控制器薄壳 + 服务编排”的统一模式。
- 这一步同时打通了第三方 facade 型支付回调的可测试路径，给后续 `Alipay` 等网关改造提供样板。

影响范围：

- Payjs 回调
- 第三方 SDK 型支付回调的服务化模式
- 支付层可测试性

验证：

- 新增 [PayjsNotificationServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/PayjsNotificationServiceTest.php)
- 新增 [PayjsControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/PayjsControllerTest.php)
- 当前全量回归结果：`OK (49 tests, 141 assertions)`

下一步：

- 沿用这套模式继续处理 Alipay / Wepay 这类 SDK 型回调，逐步统一支付层编排。

### 23. 完成 Alipay 回调服务化改造

摘要：

- 新增 [AlipayNotificationService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AlipayNotificationService.php)，把 Alipay 回调中的上下文解析、SDK 客户端创建、签名验证和支付完成编排从控制器中抽离。
- [AlipayController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Pay/AlipayController.php) 现在只保留回调入口职责，继续向“控制器薄壳 + 服务编排”的统一支付模式收敛。
- 这一步为后续处理 `Wepay` 提供了几乎同构的样板。

影响范围：

- Alipay 回调
- SDK 型支付通知服务化模式
- 支付层可测试性

验证：

- 新增 [AlipayNotificationServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AlipayNotificationServiceTest.php)
- 新增 [AlipayControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AlipayControllerTest.php)
- 当前全量回归结果：`OK (52 tests, 146 assertions)`

下一步：

- 继续用同样模式处理 Wepay，并开始整理支付网关整改清单。

### 24. 完成 Wepay 回调服务化改造

摘要：

- 新增 [WepayNotificationService.php](/Users/apple/Documents/dujiaoshuka/app/Service/WepayNotificationService.php)，把 Wepay 回调中的上下文解析、SDK 客户端创建、签名验证和支付完成编排从控制器中抽离。
- [WepayController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Pay/WepayController.php) 进一步瘦身，现在只负责从 XML 中提取订单号并委托通知服务。
- 到这一步，Yansongda 这类 SDK 型支付回调已经建立起可复用的统一服务化模式。

影响范围：

- Wepay 回调
- 微信支付 SDK 型通知服务化模式
- 支付层服务边界清晰度

验证：

- 新增 [WepayNotificationServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/WepayNotificationServiceTest.php)
- 新增 [WepayControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/WepayControllerTest.php)
- 当前全量回归结果：待本轮测试更新

下一步：

- 开始整理支付网关整改清单，并评估 Stripe / Paypal 等更重网关的下一步抽象路线。

### 25. 建立支付网关整改清单

摘要：

- 新增 [payment-gateway-remediation-inventory.md](/Users/apple/Documents/dujiaoshuka/docs/payment-gateway-remediation-inventory.md)，把当前支付网关按“已接入统一通知骨架 / 已服务化 / 仍为旧式实现 / 高风险”分类整理。
- 明确 `Stripe` 和 `Paypal` 是当前支付层剩余的最高风险区域，后续应作为阶段 A 的重点清障对象。
- 阶段 A 从“持续重构中”进一步升级为“有执行面板、有状态盘点、有默认下一步”的可追踪状态。

影响范围：

- 支付层整改追踪方式
- 阶段 A 的优先级判断
- 后续 Stripe / Paypal 整改路线

验证：

- 新增 [payment-gateway-remediation-inventory.md](/Users/apple/Documents/dujiaoshuka/docs/payment-gateway-remediation-inventory.md)
- 当前全量回归结果：`OK (55 tests, 151 assertions)`

下一步：

- 继续处理 Paypal 主流程，并准备对 Stripe 进行更大颗粒度的拆解。

### 26. 完成 Paypal 同步返回完成支付服务化

摘要：

- [PaypalReturnService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaypalReturnService.php) 已从“只提供上下文和 ApiContext”升级为真正承接 Paypal 同步返回完成支付的应用服务。
- [PaypalPayController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Pay/PaypalPayController.php) 的 `returnUrl()` 不再自己持有支付执行细节，而是只负责取消分支、结果跳转和日志记录。
- 这样 Paypal 主流程已进一步向“控制器薄壳 + 服务编排”的模式收敛，为后续继续拆支付创建与异步通知打下基础。

影响范围：

- Paypal 同步返回支付完成路径
- Paypal 控制器职责边界
- 支付层服务化一致性

验证：

- 新增 [PaypalReturnServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/PaypalReturnServiceTest.php)
- 当前全量回归结果：`OK (58 tests, 158 assertions)`

下一步：

- 继续评估 Paypal 支付创建路径，并开始着手 Stripe 的分块拆解。

### 27. 完成 Paypal 支付创建服务化

摘要：

- 新增 [PaypalCheckoutService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaypalCheckoutService.php)，把 Paypal 支付创建中的汇率转换、交易对象构建和 approval link 生成从控制器中抽离。
- [PaypalPayController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Pay/PaypalPayController.php) 的 `gateway()` 已进一步瘦身，现在主要只负责入口校验、委托服务和错误响应。
- 到这一步，Paypal 的“支付创建 + 同步返回完成支付”主流程都已进入服务化轨道。

影响范围：

- Paypal 支付创建路径
- Paypal 控制器职责边界
- 支付层服务化一致性

验证：

- 新增 [PaypalCheckoutServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/PaypalCheckoutServiceTest.php)
- 当前全量回归结果：`OK (58 tests, 158 assertions)`

下一步：

- 开始进入 Stripe 的第一轮拆分，并继续收敛 Paypal 剩余通知路径。

### 28. 完成 Stripe 支付完成路径第一轮服务化

摘要：

- 新增 [StripePaymentService.php](/Users/apple/Documents/dujiaoshuka/app/Service/StripePaymentService.php)，将 Stripe 的 `returnUrl`、`check`、`charge` 三条完成支付路径中的上下文解析、Source/Charge 调用和完成订单编排从控制器中抽出。
- [StripeController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Pay/StripeController.php) 已不再直接承担核心状态流转责任，开始向“超长页面渲染 + 轻入口委托”的中间状态收敛。
- 这一步优先把最危险的支付完成逻辑从 540 行旧控制器中剥离出来，为后续继续拆 Stripe 的页面渲染与汇率/创建逻辑建立基础。

影响范围：

- Stripe 返回支付路径
- Stripe 轮询检查路径
- Stripe 卡支付路径
- Stripe 控制器职责边界

验证：

- 新增 [StripePaymentServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/StripePaymentServiceTest.php)
- 新增 [StripeControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/StripeControllerTest.php)
- 当前全量回归结果：`OK (63 tests, 167 assertions)`

下一步：

- 继续拆 Stripe 的页面渲染与汇率/支付创建逻辑，并评估是否将内联 HTML 提取为视图模板。

### 29. 完成 Stripe 汇率与收银页视图第一轮拆分

摘要：

- 新增 [StripeCurrencyService.php](/Users/apple/Documents/dujiaoshuka/app/Service/StripeCurrencyService.php)，把美元汇率获取逻辑从控制器中抽离。
- 新增 [StripeCheckoutService.php](/Users/apple/Documents/dujiaoshuka/app/Service/StripeCheckoutService.php)，把 Stripe 收银页所需的数据组装从控制器中抽离。
- 新增 [stripe/checkout.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/stripe/checkout.blade.php)，将原先嵌在控制器里的超长内联 HTML 收银页提取为独立视图。
- [StripeController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Pay/StripeController.php) 已从“大字符串生成器”进一步退化为入口控制器，代码体积和职责复杂度明显下降。

影响范围：

- Stripe 汇率获取逻辑
- Stripe 收银页渲染方式
- Stripe 支付创建页面数据组装
- Stripe 控制器体积与可维护性

验证：

- 新增 [StripeCurrencyServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/StripeCurrencyServiceTest.php)
- 新增 [StripeCheckoutServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/StripeCheckoutServiceTest.php)
- 当前全量回归结果：`OK (65 tests, 172 assertions)`

下一步：

- 继续清理 Stripe 剩余耦合点，并开始评估阶段 A 是否可以准备收口转入下一阶段。

### 30. 启动数据库现代化并落下第一批核心表迁移骨架

摘要：

- 新增 [database-modernization-plan.md](/Users/apple/Documents/dujiaoshuka/docs/database-modernization-plan.md)，把 `install.sql -> migrations + seeders` 的拆解顺序和原则固定下来。
- 新建 `database/migrations`，并把核心商业主链表的第一批迁移骨架落地：
  - [2026_04_02_000001_create_goods_group_table.php](/Users/apple/Documents/dujiaoshuka/database/migrations/2026_04_02_000001_create_goods_group_table.php)
  - [2026_04_02_000002_create_goods_table.php](/Users/apple/Documents/dujiaoshuka/database/migrations/2026_04_02_000002_create_goods_table.php)
  - [2026_04_02_000003_create_carmis_table.php](/Users/apple/Documents/dujiaoshuka/database/migrations/2026_04_02_000003_create_carmis_table.php)
  - [2026_04_02_000004_create_coupons_tables.php](/Users/apple/Documents/dujiaoshuka/database/migrations/2026_04_02_000004_create_coupons_tables.php)
  - [2026_04_02_000005_create_pays_table.php](/Users/apple/Documents/dujiaoshuka/database/migrations/2026_04_02_000005_create_pays_table.php)
  - [2026_04_02_000006_create_orders_table.php](/Users/apple/Documents/dujiaoshuka/database/migrations/2026_04_02_000006_create_orders_table.php)
- 这一步标志着项目正式从“只有整包 SQL 初始化”迈向“可增量演进的 schema 管理”。

影响范围：

- 数据库演进模型
- 安装层现代化起步
- 核心商业主链表结构管理方式

验证：

- 第一批迁移骨架已落库，并与当前模型/测试覆盖的核心业务表域对齐。
- 当前全量回归结果：`OK (65 tests, 172 assertions)`

下一步：

- 继续补第二批业务支撑表迁移，并开始评估 seeders 与 install.sql 默认数据的拆分方案。

### 31. 接入 Avatar 主题并切换为新版默认模板

摘要：

- 将用户提供的主题资源包接入为新主题 `avatar`，资源已落到 [public/assets/avatar](/Users/apple/Documents/dujiaoshuka/public/assets/avatar)。
- 基于现有前台模板骨架创建了 [resources/views/avatar](/Users/apple/Documents/dujiaoshuka/resources/views/avatar)，并将视图命名空间与静态资源路径切换到 `avatar`。
- 在 [config/dujiaoka.php](/Users/apple/Documents/dujiaoshuka/config/dujiaoka.php) 中注册 `avatar` 模板选项，并将前台基础渲染 fallback 从旧默认主题切换到 `avatar`。
- 这一步采用兼容接入方式：以 avatar 视觉资源为主，同时补入现有页面运行所需的兼容资源，先保证新版默认主题可启用。

影响范围：

- 前台默认主题
- 模板注册列表
- 前台错误页与基础渲染 fallback
- 主题资源目录结构

验证：

- `avatar` 主题目录与资源目录已落库，并已接入模板选择配置。
- 当前全量回归结果：`OK (65 tests, 172 assertions)`

下一步：

- 验证 avatar 主题页面可正常渲染，并继续微调其兼容层样式与资源引用。

### 32. 完成 Avatar 主题首页与布局第一轮细化

摘要：

- 重新设计了 [avatar/layouts/_nav.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/avatar/layouts/_nav.blade.php)、[avatar/layouts/_footer.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/avatar/layouts/_footer.blade.php) 和 [avatar/static_pages/home.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/avatar/static_pages/home.blade.php) 的主题结构。
- 扩展了 [public/assets/avatar/css/avatar.css](/Users/apple/Documents/dujiaoshuka/public/assets/avatar/css/avatar.css)，为 avatar 主题补上导航、英雄区、分类标签、商品卡片和页脚的完整视觉层。
- 这一步让 avatar 从“兼容接入可运行”进一步提升到“具备明确前台默认主题观感”的状态。

影响范围：

- Avatar 首页
- Avatar 导航与页脚
- Avatar 主题视觉表达

验证：

- 当前全量回归结果：`OK (65 tests, 172 assertions)`

下一步：

- 继续细化 avatar 的购买页、订单详情页和搜索页，让新主题在主流程页面上形成更一致的视觉语言。

### 33. 切换为单主题模式并移除旧主题包袱

摘要：

- 将仓库前台主题策略收敛为只保留 `avatar`，不再继续维护 `unicorn`、`luna`、`hyper` 三套旧主题。
- [config/dujiaoka.php](/Users/apple/Documents/dujiaoshuka/config/dujiaoka.php) 的模板列表已缩减为仅保留 `avatar`。
- 这样后续前台重构、样式维护、页面细化和兼容修复都只需要围绕一套主题进行，明显减轻了本轮现代化改造负担。

影响范围：

- 前台模板选择策略
- 旧主题目录维护成本
- 后续主题相关重构范围

验证：

- 旧主题目录已计划从仓库中移除，仅保留 avatar 主线。
- 当前全量回归结果：`OK (65 tests, 172 assertions)`

下一步：

- 继续围绕 avatar 细化主流程页面，并逐步清理旧主题遗留的文档和资源引用。

### 34. 拆出第二批业务支撑表，并启动结构迁移与默认种子分离

摘要：

- 新增了 [create_emailtpls_table.php](/Users/apple/Documents/dujiaoshuka/database/migrations/2026_04_02_000007_create_emailtpls_table.php) 和 [create_failed_jobs_table.php](/Users/apple/Documents/dujiaoshuka/database/migrations/2026_04_02_000008_create_failed_jobs_table.php)，开始把第二批业务支撑表从 `install.sql` 里迁出。
- 引入了 [BootstrapSeeder.php](/Users/apple/Documents/dujiaoshuka/database/seeds/BootstrapSeeder.php) 和 [SampleDataSeeder.php](/Users/apple/Documents/dujiaoshuka/database/seeds/SampleDataSeeder.php)，把“安装必需默认数据”和“本地开发示例数据”分成两条 seed 路径。
- 新增 [EmailTemplateSeeder.php](/Users/apple/Documents/dujiaoshuka/database/seeds/EmailTemplateSeeder.php)，由它负责默认邮件模板 bootstrap 数据，不再让 `DatabaseSeeder` 默认灌入示例订单。
- 补了 [EmailTemplateSeederTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/EmailTemplateSeederTest.php) 作为护栏，确保默认模板 token 集不会在后续拆分中被破坏。

影响范围：

- 第二批业务支撑表迁移
- 数据库默认初始化职责划分
- `DatabaseSeeder` 入口语义
- 默认邮件模板来源

验证：

- 默认邮件模板已可通过专用 seeder 完成 bootstrap 写入。
- 当前全量回归结果：`OK (66 tests, 178 assertions)`

下一步：

- 继续把 `install.sql` 中剩余的默认数据拆成更明确的 bootstrap seed 和 sample seed。

### 35. 将支付方式样例从安装默认值拆入 sample seed

摘要：

- 新增了 [PaySampleSeeder.php](/Users/apple/Documents/dujiaoshuka/database/seeds/PaySampleSeeder.php)，把 `install.sql` 里的支付方式样例配置迁移为开发样例 seed。
- [SampleDataSeeder.php](/Users/apple/Documents/dujiaoshuka/database/seeds/SampleDataSeeder.php) 现在会显式加载支付方式样例和示例订单，而不是让安装 bootstrap 默认带上一组伪商户配置。
- 这样新安装环境的 bootstrap 数据会更干净，支付网关示例只在本地演示、测试和开发初始化时按需提供。

影响范围：

- 默认支付方式样例来源
- sample seed 语义
- `install.sql` 后续职责收缩方向

验证：

- 已补充 [PaySampleSeederTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/PaySampleSeederTest.php) 作为样例支付配置护栏。
- 当前全量回归结果：`OK (67 tests, 184 assertions)`

下一步：

- 继续识别 `install.sql` 里剩余的后台/系统默认数据，进一步划分为 bootstrap、sample 和高风险禁入三类。

### 36. 固化安装数据分类边界，并为 bootstrap seed 加边界护栏

摘要：

- 新增了 [install-data-classification.md](/Users/apple/Documents/dujiaoshuka/docs/install-data-classification.md)，把 `install.sql` 里的后台/系统默认数据分成了 `bootstrap`、`sample`、`forbidden` 三类。
- 这一步明确将 `admin_users` 和 `admin_role_users` 归为高风险禁入项，不再把“默认管理员账号 + 角色绑定”视为可接受的安装默认值。
- 同时补了 [BootstrapSeederBoundaryTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/BootstrapSeederBoundaryTest.php)，确保 bootstrap seed 只恢复邮件模板这类安全默认值，不会把支付样例重新带回默认安装路径。

影响范围：

- 安装默认数据治理边界
- 后台骨架数据后续迁移策略
- bootstrap / sample seed 责任边界

验证：

- 当前全量回归结果：`OK (68 tests, 186 assertions)`

下一步：

- 继续把后台骨架数据从 `install.sql` 拆成独立迁移/seed，并在安装流程层面开始摆脱对整包 SQL 导入的依赖。

### 37. 启动第三批后台骨架结构迁移，并继续隔离高风险默认账号

摘要：

- 新增了后台骨架相关 migration：
  - [create_admin_menu_table.php](/Users/apple/Documents/dujiaoshuka/database/migrations/2026_04_02_000009_create_admin_menu_table.php)
  - [create_admin_permissions_table.php](/Users/apple/Documents/dujiaoshuka/database/migrations/2026_04_02_000010_create_admin_permissions_table.php)
  - [create_admin_roles_table.php](/Users/apple/Documents/dujiaoshuka/database/migrations/2026_04_02_000011_create_admin_roles_table.php)
  - [create_admin_permission_menu_table.php](/Users/apple/Documents/dujiaoshuka/database/migrations/2026_04_02_000012_create_admin_permission_menu_table.php)
  - [create_admin_role_menu_table.php](/Users/apple/Documents/dujiaoshuka/database/migrations/2026_04_02_000013_create_admin_role_menu_table.php)
  - [create_admin_role_permissions_table.php](/Users/apple/Documents/dujiaoshuka/database/migrations/2026_04_02_000014_create_admin_role_permissions_table.php)
  - [create_admin_settings_table.php](/Users/apple/Documents/dujiaoshuka/database/migrations/2026_04_02_000015_create_admin_settings_table.php)
- 这一轮只迁后台骨架结构，不迁 `admin_users` 和 `admin_role_users`，继续把默认管理员账号及其角色绑定隔离在高风险禁入区。
- 这样第三批已经从“只做策略盘点”进入“结构先行、账号后拆”的实作阶段。

影响范围：

- 第三批后台与系统表迁移进度
- 后台骨架与默认凭据分离策略
- `install.sql` 后续退场路径

验证：

- 当前全量回归结果：`OK (68 tests, 186 assertions)`

下一步：

- 继续补后台骨架的安全 seed 方案，并开始从安装流程中移除默认管理员账号导入。

### 38. 为后台骨架补上安全 bootstrap seed，继续排除默认管理员账号

摘要：

- 新增了 [AdminBootstrapSeeder.php](/Users/apple/Documents/dujiaoshuka/database/seeds/AdminBootstrapSeeder.php)，将 `admin_menu`、`admin_permissions`、`admin_roles` 的非敏感骨架数据从 `install.sql` 迁入 bootstrap seed。
- [BootstrapSeeder.php](/Users/apple/Documents/dujiaoshuka/database/seeds/BootstrapSeeder.php) 现在除了默认邮件模板，还会恢复后台菜单、权限和角色骨架。
- 整个过程仍然明确排除了 `admin_users` 和 `admin_role_users`，避免默认管理员账号和角色绑定再次回流到安装默认路径。
- 同时补了 [AdminBootstrapSeederTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AdminBootstrapSeederTest.php) 和对 [BootstrapSeederBoundaryTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/BootstrapSeederBoundaryTest.php) 的增强护栏。

影响范围：

- 后台骨架默认数据来源
- bootstrap seed 的可用性
- 默认管理员账号禁入策略

验证：

- 当前全量回归结果：`OK (69 tests, 195 assertions)`

下一步：

- 开始改安装流程，从整包 `install.sql` 导入切换到“迁移 + bootstrap seed”的新入口。

### 39. 将安装主路径切换到 migrate + bootstrap seed，并改为显式创建首个管理员

摘要：

- 新增了 [InstallationService.php](/Users/apple/Documents/dujiaoshuka/app/Service/InstallationService.php)，安装流程现在会执行运行时连接配置、迁移、bootstrap seed、安装锁写入，以及首个管理员账号创建。
- [HomeController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Home/HomeController.php) 的 `doInstall()` 已经不再直接执行 `DB::unprepared(file_get_contents($installSql))`，而是切到新的安装服务入口。
- 补上了后台用户结构 migration：
  - [create_admin_users_table.php](/Users/apple/Documents/dujiaoshuka/database/migrations/2026_04_02_000016_create_admin_users_table.php)
  - [create_admin_role_users_table.php](/Users/apple/Documents/dujiaoshuka/database/migrations/2026_04_02_000017_create_admin_role_users_table.php)
- 安装页 [install.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/common/install.blade.php) 新增了管理员账号、密码、确认密码字段，安装完成后也不再提示固定默认账号。
- 同时补了 [InstallationServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/InstallationServiceTest.php)，守住“安装时显式创建首个管理员并绑定管理员角色”这条行为。

影响范围：

- 安装入口主流程
- 后台首个管理员创建方式
- `install.sql` 在安装路径中的职责

验证：

- 当前全量回归结果：`OK (70 tests, 198 assertions)`

下一步：

- 继续收缩安装流程中对 `install.sql` 的剩余依赖，并准备最终移除整包 SQL 安装主路径。

### 40. 将 install.sql 正式降级为历史参考文件

摘要：

- 给 [install.sql](/Users/apple/Documents/dujiaoshuka/database/sql/install.sql) 增加了明确的废弃说明，声明它仅作为历史结构/数据快照保留，不再作为新安装主入口。
- 新增了 [installer-modernization-status.md](/Users/apple/Documents/dujiaoshuka/docs/installer-modernization-status.md)，把当前安装主路径、新旧职责对比、已完成项和后续退场条件集中整理出来。
- 这一步的目标不是立刻删除 `install.sql`，而是先从代码和文档层面彻底去掉“它仍然是主安装方式”的暗示。

影响范围：

- 安装文档认知边界
- `install.sql` 的仓库定位
- 后续彻底移除旧 SQL 安装路径的准备工作

验证：

- 代码层安装主路径仍保持 `migrate + bootstrap seed + 显式创建首个管理员`。
- 当前全量回归结果：`OK (70 tests, 198 assertions)`

下一步：

- 继续把 `install.sql` 中剩余的后台关系数据和历史默认值拆解干净，为最终移除它做准备。

### 41. 同步 README 与 Debian 手册到新的安装现实

摘要：

- 更新了 [README.md](/Users/apple/Documents/dujiaoshuka/README.md)，补入当前安装流程现代化状态、最新测试基线以及相关文档索引。
- 更新了 [debian_manual.md](/Users/apple/Documents/dujiaoshuka/debian_manual.md)，将克隆地址切换到当前维护分支仓库，并明确安装时需要显式设置首个管理员账号密码。
- 这一轮的目标是把仓库表层文档和我们已经完成的代码现实对齐，避免后续用户仍按“默认 admin/admin + install.sql 安装”的旧认知操作。

影响范围：

- README 现状说明
- Debian 安装手册
- 新旧安装模式认知一致性

验证：

- 文档已与当前主线安装模式保持一致。
- 当前全量回归结果：`OK (70 tests, 198 assertions)`

下一步：

- 继续识别 `install.sql` 中剩余未迁出的历史数据，并评估最终移除条件。

### 42. 建立 install.sql 覆盖矩阵，并修正早期审计文档的历史时态

摘要：

- 新增了 [install-sql-coverage-matrix.md](/Users/apple/Documents/dujiaoshuka/docs/install-sql-coverage-matrix.md)，按表列出了 `install.sql` 中每一项结构和默认数据目前是否已被 migration / bootstrap / sample / forbidden 策略接管。
- 这份矩阵确认了一个关键结论：`install.sql` 中的表结构已经全部有对应迁移覆盖，剩余价值主要是历史快照和审计对照，而不再是安装功能依赖。
- 同时修正了 [project-audit-notes.md](/Users/apple/Documents/dujiaoshuka/docs/project-audit-notes.md) 和 [modernization-roadmap.md](/Users/apple/Documents/dujiaoshuka/docs/modernization-roadmap.md) 中部分已经过时的“现在时”描述，改成更准确的“原始项目曾如此，当前维护分支已部分修复”。

影响范围：

- `install.sql` 退场评估依据
- 审计文档准确性
- 现代化路线文档与当前事实的一致性

验证：

- 当前文档已能清晰区分“历史问题”和“当前主线状态”。
- 当前全量回归结果：`OK (70 tests, 198 assertions)`

下一步：

- 继续识别是否还需要为空关系表补最小 bootstrap 关系数据，或者直接将其维持为空结构。

### 43. 完成安装现代化阶段收尾，并确认空关系表维持为空结构

摘要：

- 新增了 [installation-modernization-closure.md](/Users/apple/Documents/dujiaoshuka/docs/installation-modernization-closure.md)，作为本阶段的正式结项说明。
- 这一轮明确做出最终判断：`admin_permission_menu`、`admin_role_menu`、`admin_role_permissions`、`admin_settings` 继续保持为空结构，不人为补伪造 bootstrap 关系数据。
- 这样安装现代化阶段的未决问题已经收口，`install.sql` 也从“仍待拆解”转为“短期保留的历史参考文件”。

影响范围：

- 安装现代化阶段边界
- `install.sql` 退场判断
- 后续阶段的工作焦点

验证：

- 当前全量回归结果：`OK (70 tests, 198 assertions)`

下一步：

- 后续重点转向更高层的框架升级、后台替换评估和剩余遗留兼容清障。

### 44. 统一品牌视觉与默认 Logo 为「独角数卡西瓜版」

摘要：

- 新增并接入了新的品牌主图与缩略图资源：
  [dujiaoka-xigua.png](/Users/apple/Documents/dujiaoshuka/public/assets/avatar/images/dujiaoka-xigua.png)、
  [favicon.png](/Users/apple/Documents/dujiaoshuka/public/assets/avatar/images/favicon.png)、
  [public/favicon.png](/Users/apple/Documents/dujiaoshuka/public/favicon.png)。
- 前台 `avatar` 主题导航、页脚、favicon 和品牌默认文案已统一切换为“独角数卡西瓜版”。
- 后台 `dcat-admin` 的名称、标题、Logo 和欢迎页标题已统一为“独角数卡西瓜版”。
- 安装页不再使用旧的超大内联 Logo，而是改为直接引用新的西瓜版品牌图，并同步更新安装程序标题和站点默认名称。
- 订单邮件模板数据与 Bark 推送的默认站点名，也统一切换为“独角数卡西瓜版”。

影响范围：

- 前台主题视觉识别
- 后台控制台品牌识别
- 安装流程品牌呈现
- 通知与推送默认品牌名

验证：

- 关键品牌文案和 favicon 引用已统一完成。
- 当前全量回归结果：`OK (70 tests, 198 assertions)`

下一步：

- 后续可继续细化 `avatar` 主题的下单、查询与订单详情页视觉一致性。

### 45. 同步仓库 README 与项目默认介绍口径

摘要：

- 更新了 [README.md](/Users/apple/Documents/dujiaoshuka/README.md)，将仓库首页说明统一为“独角数卡西瓜版”的当前定位、完成阶段与运行基线。
- 调整了 [composer.json](/Users/apple/Documents/dujiaoshuka/composer.json) 与 [package.json](/Users/apple/Documents/dujiaoshuka/package.json) 的项目描述，使仓库元信息不再停留在 Laravel 默认模板。
- 同步更新了 `avatar` 首页眉标、主题自述文件，以及中后台默认介绍文案，让仓库内外的项目介绍保持一致。

影响范围：

- GitHub 仓库首页展示
- 项目包元信息
- 默认主题首页介绍
- 中后台默认说明文案

验证：

- README、Composer、Package 与首页默认文案已统一到“独角数卡西瓜版”口径。
- 当前全量回归结果：`OK (70 tests, 198 assertions)`

下一步：

- 继续回到主线整改，推进升级前清障与后台替换评估。

### 46. 启动升级前清障与后台替换评估

摘要：

- 新增了 [upgrade-readiness-checklist.md](/Users/apple/Documents/dujiaoshuka/docs/upgrade-readiness-checklist.md)，把下一阶段的主要阻塞分成运行时依赖、后台框架、支付 SDK 和 Laravel 6 时代写法四类。
- 新增了 [admin-replacement-assessment.md](/Users/apple/Documents/dujiaoshuka/docs/admin-replacement-assessment.md)，对当前 `app/Admin` 与 Dcat 的耦合程度做了盘点，并给出“先降耦合、后替后台壳”的建议结论。
- 同步更新了 [modernization-roadmap.md](/Users/apple/Documents/dujiaoshuka/docs/modernization-roadmap.md) 与 [rectification-execution-plan.md](/Users/apple/Documents/dujiaoshuka/docs/rectification-execution-plan.md)，让主线计划进入升级前清障阶段时有明确依据。

影响范围：

- 下一阶段整改优先级
- 后台替换时机判断
- Laravel / PHP 升级前的准备路径

验证：

- 阻塞项、后台耦合面与建议执行顺序已形成书面依据。
- 当前全量回归结果：`OK (70 tests, 198 assertions)`

下一步：

- 开始为阻塞依赖建立“保留 / 替换 / 移除”矩阵，并优先处理 QRCode、Geetest 与测试依赖链。

### 47. 建立阻塞依赖执行矩阵

摘要：

- 新增了 [dependency-blocker-matrix.md](/Users/apple/Documents/dujiaoshuka/docs/dependency-blocker-matrix.md)，将当前关键阻塞依赖按用途、阻塞级别、建议动作和执行顺序整理成矩阵。
- 这份矩阵明确区分了三类问题：现代 PHP 直接阻塞链、支付 SDK 阻塞链、后台生态阻塞链。
- 当前建议的默认优先顺序已收敛为：`phpspec/prophecy`、`germey/geetest`、`simple-qrcode` / `bacon`。

影响范围：

- 依赖治理优先级
- 升级前清障执行顺序
- 后续包替换决策依据

验证：

- 阻塞包已有明确的“保留 / 替换 / 移除”判断依据。
- 当前全量回归结果：`OK (70 tests, 198 assertions)`

下一步：

- 开始处理第一条非业务阻塞链，优先确认 `phpspec/prophecy` 的移除路径。

### 48. 完成第一条依赖阻塞链：移除 Prophecy 主依赖

摘要：

- 通过升级 `phpunit/phpunit` 从 `9.5.4` 到 `9.6.34`，将 `phpspec/prophecy` 从当前项目主锁文件依赖链中移除。
- 同步更新了 [composer.lock](/Users/apple/Documents/dujiaoshuka/composer.lock)，并完成当前 PHPUnit 配置迁移，消除了旧 schema 警告。
- 当前测试代码中并未直接使用 `Prophecy`，因此这次清障没有触碰业务测试语义，只是清掉了历史测试依赖阻塞。

影响范围：

- 开发依赖锁文件
- PHPUnit 运行时
- 升级前测试链稳定性

验证：

- `./scripts/composer74 why phpspec/prophecy` 已确认当前项目中找不到该包。
- 当前全量回归结果：`OK (70 tests, 198 assertions)`

下一步：

- 继续处理下一条 P0 阻塞链，优先评估 `germey/geetest` 的移除或替换路径。

### 49. 完成第二条依赖阻塞链：移除 Geetest 主路径依赖

摘要：

- 将 `Geetest` 从前台下单主路径中移除，不再参与订单创建校验、前台购买页脚本、初始化路由和中间件启动流程。
- 清理了 [composer.json](/Users/apple/Documents/dujiaoshuka/composer.json)、[composer.lock](/Users/apple/Documents/dujiaoshuka/composer.lock)、[config/app.php](/Users/apple/Documents/dujiaoshuka/config/app.php) 中的 Geetest 包级依赖与注册入口。
- 删除了旧的 [config/geetest.php](/Users/apple/Documents/dujiaoshuka/config/geetest.php) 和 [resources/views/vendor/geetest/geetest.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/vendor/geetest/geetest.blade.php)，并移除了后台系统设置里的 Geetest 配置页。
- 新增了 [tests/Unit/GeetestRemovalTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/GeetestRemovalTest.php)，确保旧缓存开关即使仍为开启状态，也不会再阻塞订单创建校验。

影响范围：

- 前台下单验证链
- 历史行为验证依赖
- Composer 主依赖
- 后台系统设置入口

验证：

- `./scripts/composer74 why germey/geetest` 已确认当前项目中找不到该包。
- 当前全量回归结果：`OK (71 tests, 199 assertions)`

下一步：

- 继续处理下一条 P0 阻塞链，开始替换 `simple-qrcode` / `bacon` 这一组二维码依赖。

### 50. 完成第三条依赖阻塞链：移除二维码生成旧依赖

摘要：

- 将二维码支付页从后端 `QrCode::format('png')` 生成方式切换为前端本地 `jquery-qrcode` 渲染，不再依赖 `simple-qrcode` 和 `bacon`。
- 清理了 [composer.json](/Users/apple/Documents/dujiaoshuka/composer.json) 与 [composer.lock](/Users/apple/Documents/dujiaoshuka/composer.lock) 中的二维码依赖。
- 新增了 [tests/Unit/QrPayViewTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/QrPayViewTest.php)，确认二维码支付页使用本地前端二维码容器与脚本，而不是后端 PNG 生成器。

影响范围：

- 二维码支付页渲染方式
- Composer 主依赖
- 现代 PHP 阻塞链

验证：

- `./scripts/composer74 why simplesoftwareio/simple-qrcode` 与 `./scripts/composer74 why bacon/bacon-qr-code` 已确认当前项目中找不到这两个包。
- 当前全量回归结果：`OK (72 tests, 203 assertions)`

下一步：

- 继续进入下一组高优先级阻塞，开始评估 `paypal/rest-api-sdk-php` 与 `stripe/stripe-php` 的替换顺序。

### 51. 退役 Paysapi / Vpay / PayJS，并收紧 PayPal SDK 边界

摘要：

- 将 `Paysapi`、`Vpay`、`PayJS` 三个已落后的支付通道正式定义为新版本不再维护，并从支付路由、控制器、服务与测试基线中移除。
- 同步从 [composer.json](/Users/apple/Documents/dujiaoshuka/composer.json) 与 [composer.lock](/Users/apple/Documents/dujiaoshuka/composer.lock) 中移除了 `xhat/payjs-laravel`。
- 在 [Pay.php](/Users/apple/Documents/dujiaoshuka/app/Models/Pay.php)、[PayService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PayService.php) 与 [PayEntryService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PayEntryService.php) 中加入退役通道规则，使数据库中残留的旧网关配置也不会再进入前台主路径。
- 新增了 [PaypalSdkService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaypalSdkService.php)，将旧 PayPal REST SDK 访问收敛为单点服务，`PaypalCheckoutService` 与 `PaypalReturnService` 现在只通过它触达旧 SDK。
- 新增了 [RetiredGatewayTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/RetiredGatewayTest.php)，确保退役通道不会再出现在支付列表里，并会向用户返回明确的“已停止维护”提示。

影响范围：

- 新版本支付通道维护范围
- PayJS 依赖链
- 支付入口过滤规则
- PayPal 历史 SDK 隔离边界

验证：

- `./scripts/composer74 why xhat/payjs-laravel` 已确认当前项目中找不到该包。
- 当前全量回归结果：`OK (67 tests, 196 assertions)`

下一步：

- 继续聚焦仍保留的新版本支付通道，开始制定 `paypal/rest-api-sdk-php` 与 `stripe/stripe-php` 的替换顺序与单点清障方案。

### 52. 收紧 Stripe SDK 边界，进入保留支付通道单点替换阶段

摘要：

- 新增了 [StripeSdkService.php](/Users/apple/Documents/dujiaoshuka/app/Service/StripeSdkService.php)，将 `Stripe::setApiKey`、`Source::retrieve`、`Charge::create` 这组旧 SDK 访问收敛为单点服务。
- [StripePaymentService.php](/Users/apple/Documents/dujiaoshuka/app/Service/StripePaymentService.php) 现在不再直接静态调用 Stripe SDK，而是统一通过 `StripeSdkService` 访问。
- 同步更新了 [StripePaymentServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/StripePaymentServiceTest.php)，让测试围绕 SDK 边界 mock，而不是继续依赖内部受保护方法覆写。

影响范围：

- Stripe 支付完成路径
- Stripe SDK 调用边界
- 后续 Stripe SDK 升级切口

验证：

- 定向测试：`OK (2 tests, 10 assertions)`
- 当前全量回归结果：`OK (67 tests, 200 assertions)`

下一步：

- 开始形成 `PayPal` 与 `Stripe` 两条保留支付通道的替换顺序与实施方案，优先处理更老的 `paypal/rest-api-sdk-php`。

### 53. 为 PayPal 与 Stripe 建立可替换的接口绑定边界

摘要：

- 新增了 [PaypalGatewayClientInterface.php](/Users/apple/Documents/dujiaoshuka/app/Service/Contracts/PaypalGatewayClientInterface.php) 与 [StripeGatewayClientInterface.php](/Users/apple/Documents/dujiaoshuka/app/Service/Contracts/StripeGatewayClientInterface.php)。
- 现有 [PaypalSdkService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaypalSdkService.php) 与 [StripeSdkService.php](/Users/apple/Documents/dujiaoshuka/app/Service/StripeSdkService.php) 已分别实现这些接口。
- [PaypalCheckoutService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaypalCheckoutService.php)、[PaypalReturnService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaypalReturnService.php)、[StripePaymentService.php](/Users/apple/Documents/dujiaoshuka/app/Service/StripePaymentService.php) 现在都通过接口从容器取网关客户端，不再直接依赖具体 SDK 服务类。
- [AppServiceProvider.php](/Users/apple/Documents/dujiaoshuka/app/Providers/AppServiceProvider.php) 已加入接口到实现类的绑定，测试也同步切到基于接口的 mock。

影响范围：

- PayPal 与 Stripe 的 SDK 调用边界
- 后续 SDK 替换切口
- 支付服务的容器依赖关系

验证：

- 当前全量回归结果：`OK (67 tests, 200 assertions)`

下一步：

- 开始形成 `paypal/rest-api-sdk-php` 与 `stripe/stripe-php` 的替换顺序文档，并优先设计 PayPal 的退场方案。

### 54. 固化支付通道生命周期状态并接入后台管理面板

摘要：

- 在 [Pay.php](/Users/apple/Documents/dujiaoshuka/app/Models/Pay.php) 中正式定义支付通道生命周期，区分 `维护中`、`遗留待替换`、`已退役` 三种状态。
- 将 `PayPal` 与 `Stripe` 标记为遗留待替换通道，将 `Paysapi`、`Vpay`、`Payjs` 标记为已退役通道。
- 在后台 [PayController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/PayController.php) 的列表页、详情页和编辑页接入生命周期展示与提示，避免继续把退役通道当作普通可维护支付方式使用。
- 新增 [PayLifecycleTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/PayLifecycleTest.php)，直接守住生命周期分类规则。

影响范围：

- 支付通道后台管理
- 支付通道模型语义
- 后续支付替换与退场治理

验证：

- 当前全量回归结果：`OK (70 tests, 207 assertions)`

下一步：

- 开始整理 `PayPal` 退场方案，把旧 REST SDK 的替代接入方式和迁移策略固定下来。

### 55. 消除 PayPal 业务层的旧 SDK 类型泄漏并建立迁移顺序文档

摘要：

- 调整 [PaypalGatewayClientInterface.php](/Users/apple/Documents/dujiaoshuka/app/Service/Contracts/PaypalGatewayClientInterface.php)，让业务层不再直接暴露 `ApiContext`、`Payment` 这类旧 SDK 类型。
- [PaypalCheckoutService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaypalCheckoutService.php) 与 [PaypalReturnService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaypalReturnService.php) 现在只通过更稳定的业务参数与接口交互。
- [PaypalSdkService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaypalSdkService.php) 继续作为唯一旧 SDK 访问边界，旧对象构建与执行细节全部收拢在服务内部。
- 新增 [paypal-stripe-transition-plan.md](/Users/apple/Documents/dujiaoshuka/docs/paypal-stripe-transition-plan.md)，正式固定 `PayPal` 先退场、`Stripe` 后升级的默认顺序。

影响范围：

- PayPal 支付业务服务
- PayPal SDK 替换切口
- 支付依赖升级顺序文档

验证：

- 当前全量回归结果：`OK (70 tests, 207 assertions)`

下一步：

- 继续沿着迁移方案推进 `PayPal` 退场条件，优先整理旧 SDK 仍残留的接入假设与替换约束。

### 56. 清理退役支付样例并拔掉 PayPal 模式硬编码

摘要：

- 将 [PaySampleSeeder.php](/Users/apple/Documents/dujiaoshuka/database/seeds/PaySampleSeeder.php) 中的 `Paysapi`、`Vpay`、`Payjs` 示例通道移除，避免新环境继续被历史样例误导。
- [PaypalSdkService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaypalSdkService.php) 不再硬编码 `live` 模式，改由 [config/dujiaoka.php](/Users/apple/Documents/dujiaoshuka/config/dujiaoka.php) 中的 `paypal_mode` 配置控制。
- 更新 [PaySampleSeederTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/PaySampleSeederTest.php) 与迁移方案文档，保持退役策略、样例数据和替换计划一致。

影响范围：

- 支付样例种子
- PayPal 接入配置边界
- 新环境初始化体验

验证：

- 当前全量回归结果：`OK (70 tests, 204 assertions)`

下一步：

- 继续梳理 `PayPal` 旧 SDK 仍残留的同步返回/异步通知假设，准备进入真正的替换实施阶段。

### 57. 将 PayPal webhook 占位实现服务化，明确同步优先模型

摘要：

- 新增 [PaypalWebhookService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaypalWebhookService.php)，将原来控制器里直接读取 `php://input` 的临时实现收口为服务。
- [PaypalPayController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Pay/PaypalPayController.php) 的 `notifyUrl()` 现在只负责委托服务处理并返回明确的 `202 ignored` 响应。
- 新增 [PaypalWebhookServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/PaypalWebhookServiceTest.php)，并扩充 [PaypalPayControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/PaypalPayControllerTest.php)，守住 webhook 占位入口的当前行为。
- 在迁移文档中明确：当前 PayPal 主链依旧以同步 return 完成支付为准，webhook 仍是待决定的替换约束，而不再是隐式实现。

影响范围：

- PayPal 异步通知入口
- PayPal 替换约束梳理
- 控制器直接读取原始输入流的旧实现

验证：

- 当前全量回归结果：`OK (70 tests, 211 assertions)`

下一步：

- 继续整理 `PayPal` 替换实施所需的最终约束，准备进入旧 SDK 真正退场前的实现切换阶段。

### 58. 将 PayPal SDK 异常包装为应用层异常

摘要：

- 新增 [PaymentGatewayException.php](/Users/apple/Documents/dujiaoshuka/app/Exceptions/PaymentGatewayException.php)，作为支付网关接入层统一异常。
- [PaypalSdkService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaypalSdkService.php) 现在会把旧 SDK 抛出的异常包装成应用层异常，再向上抛出。
- [PaypalPayController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Pay/PaypalPayController.php) 不再直接依赖 `PayPalConnectionException`，只处理应用层异常与业务校验异常。
- 新增 [PaypalSdkServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/PaypalSdkServiceTest.php)，确保旧 SDK 错误不会重新泄漏到业务层。

影响范围：

- PayPal 控制器异常边界
- PayPal SDK 隔离层
- 后续替换实现的异常语义

验证：

- 当前全量回归结果：`OK (76 tests, 218 assertions)`

下一步：

- 继续压缩 `PayPal` 旧 SDK 的残余实现假设，为真正移除 `paypal/rest-api-sdk-php` 做最后准备。

### 59. 将 PayPal 币种假设与示例字段语义配置化

摘要：

- 在 [config/dujiaoka.php](/Users/apple/Documents/dujiaoshuka/config/dujiaoka.php) 中新增 `paypal_source_currency` 与 `paypal_target_currency`，默认保持 `CNY -> USD`，但不再写死在服务内部。
- [PaypalCheckoutService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaypalCheckoutService.php) 与 [PaypalSdkService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaypalSdkService.php) 现在统一从配置读取 PayPal 源币种与目标结算币种。
- [PaySampleSeeder.php](/Users/apple/Documents/dujiaoshuka/database/seeds/PaySampleSeeder.php) 中的 PayPal 示例字段说明已改为更明确的 `Client ID / Client Secret` 语义，减少新环境接入误解。
- 补充 [PaypalCheckoutServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/PaypalCheckoutServiceTest.php) 与 [PaypalSdkServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/PaypalSdkServiceTest.php)，守住配置读取边界。

影响范围：

- PayPal 币种转换假设
- PayPal 示例支付配置
- 后续替换实现的配置边界

验证：

- 当前全量回归结果：`OK (78 tests, 220 assertions)`

下一步：

- 继续梳理 `PayPal` 旧 SDK 还剩下哪些实现假设必须保留，哪些可以直接让位给替代实现。

### 60. 将 PayPal 回跳 URL 假设抽到独立服务

摘要：

- 新增 [PaypalCallbackUrlService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaypalCallbackUrlService.php)，统一负责 PayPal 同步成功与取消支付的回跳 URL 生成。
- [PaypalSdkService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaypalSdkService.php) 不再直接调用 `route('paypal-return')`，而是通过独立服务获取回跳地址；当命名路由缺失时，服务会安全回退到显式 URL 拼装。
- 新增 [PaypalCallbackUrlServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/PaypalCallbackUrlServiceTest.php)，并扩充 [PaypalSdkServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/PaypalSdkServiceTest.php)，守住回跳 URL 边界。
- 这一步让 PayPal 替换前的剩余实现假设进一步收缩到独立边界，避免后续新实现再次把路由约定粘回 SDK 封装。

影响范围：

- PayPal 同步返回 / 取消回跳 URL
- PayPal SDK 封装边界
- PayPal 替换准备度

验证：

- 当前全量回归结果：`OK (80 tests, 227 assertions)`

下一步：

- 继续梳理 `PayPal` 最终替换前仍保留的能力边界，准备把重心逐步切向 `Stripe` 升级链。

### 61. 为 Stripe 抽离 URL 与币种边界

摘要：

- 新增 [StripeRouteService.php](/Users/apple/Documents/dujiaoshuka/app/Service/StripeRouteService.php)，统一承接 Stripe 结账页使用的 `return / detail / check / charge` URL 生成。
- [StripeCheckoutService.php](/Users/apple/Documents/dujiaoshuka/app/Service/StripeCheckoutService.php) 不再自己拼接这些 URL，同时为后续升级预留了 `stripe_source_currency` 与 `stripe_target_currency` 配置边界。
- 在 [config/dujiaoka.php](/Users/apple/Documents/dujiaoshuka/config/dujiaoka.php) 中新增 Stripe 币种配置，默认保持 `CNY -> USD`，但不再把这组假设写死在服务内部。
- 新增 [StripeRouteServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/StripeRouteServiceTest.php)，并扩充 [StripeCheckoutServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/StripeCheckoutServiceTest.php)，守住 Stripe 结账页边界。

影响范围：

- Stripe 结账页 URL 生成
- Stripe 币种配置边界
- Stripe SDK 升级前清障

验证：

- 当前全量回归结果：`OK (82 tests, 233 assertions)`

下一步：

- 继续收敛 Stripe 的异常与状态处理边界，为后续 SDK 升级做好准备。

### 62. 将 Stripe 异常与结算币种语义收敛到应用层

摘要：

- [StripePaymentService.php](/Users/apple/Documents/dujiaoshuka/app/Service/StripePaymentService.php) 现在会把 SDK 层异常统一包装成应用层 `PaymentGatewayException`，不再把底层错误直接泄漏到更高层。
- [StripeController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Pay/StripeController.php) 已改为处理应用层异常，而不是隐式依赖 SDK 行为。
- `handleCardCharge()` 不再写死 `usd`，而是读取 `stripe_target_currency` 配置，保持与前面配置化边界一致。
- 扩充 [StripePaymentServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/StripePaymentServiceTest.php)，守住异常边界和结算币种读取行为。

影响范围：

- Stripe 支付状态处理
- Stripe 控制器异常边界
- Stripe 结算币种语义

验证：

- 当前全量回归结果：`OK (83 tests, 236 assertions)`

下一步：

- 继续收敛 Stripe 里残留的状态处理假设，为后续独立升级 `stripe/stripe-php` 准备更稳定的边界。

### 63. 将 Stripe source 状态处理抽成独立处理服务

摘要：

- 新增 [StripeSourceProcessorService.php](/Users/apple/Documents/dujiaoshuka/app/Service/StripeSourceProcessorService.php)，统一处理 `source` 的检索、按需扣款、订单归属校验与完成订单。
- [StripePaymentService.php](/Users/apple/Documents/dujiaoshuka/app/Service/StripePaymentService.php) 现在更接近编排层，`return` 与 `check` 两条路径不再各自保留一份相似的 source 状态处理逻辑。
- 新增 [StripeSourceProcessorServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/StripeSourceProcessorServiceTest.php)，给这段独立出来的状态处理逻辑补了直接护栏。

影响范围：

- Stripe source 状态处理
- Stripe 支付服务职责边界
- Stripe SDK 升级准备度

验证：

- 当前全量回归结果：`OK (84 tests, 239 assertions)`

下一步：

- 继续清理 Stripe 剩余的旧式输入 / 页面耦合点，为独立升级 SDK 和后续前端整治做准备。

### 64. 将 Stripe 金额换算与分单位计算抽出控制器

摘要：

- 新增 [StripeAmountService.php](/Users/apple/Documents/dujiaoshuka/app/Service/StripeAmountService.php)，统一承接 Stripe 结账页与卡片扣款所需的金额换算和分单位计算。
- [StripeCheckoutService.php](/Users/apple/Documents/dujiaoshuka/app/Service/StripeCheckoutService.php) 与 [StripeController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Pay/StripeController.php) 已改为通过金额服务取值，控制器不再自己做汇率换算。
- 新增 [StripeAmountServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/StripeAmountServiceTest.php)，并同步调整 [StripeCheckoutServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/StripeCheckoutServiceTest.php) 与 [StripeControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/StripeControllerTest.php)。

影响范围：

- Stripe 控制器职责边界
- Stripe 金额换算与分单位计算
- Stripe 结账页数据组装

验证：

- 当前全量回归结果：`OK (85 tests, 245 assertions)`

下一步：

- 继续清理 Stripe 里残留的旧式页面与前端耦合点，为后续 SDK 独立升级与前台重做做准备。

### 65. 将 Stripe 入口参数收成专用输入对象

摘要：

- 新增 [StripeRequestData.php](/Users/apple/Documents/dujiaoshuka/app/Service/DataTransferObjects/StripeRequestData.php)，统一承接 `orderid`、`source`、`stripeToken` 三类 Stripe 入口参数。
- [StripeController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Pay/StripeController.php) 不再直接依赖 `$request->all()` 的原始数组键名，而是改为通过 DTO 读取。
- 新增 [StripeRequestDataTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/StripeRequestDataTest.php)，给 Stripe 入口协议增加一层轻量护栏。

影响范围：

- Stripe 控制器入口参数解析
- Stripe 页面协议边界
- 后续前端整治与 SDK 升级准备度

验证：

- 当前全量回归结果：`OK (86 tests, 246 assertions)`

下一步：

- 继续清理 Stripe 里剩余的旧式页面和前端 CDN 耦合点，为更现代的支付页与 SDK 升级做准备。

### 66. 将 Stripe 收银页外部 CDN 壳替换为本地资源

摘要：

- 新增本地资源 [stripe-checkout.css](/Users/apple/Documents/dujiaoshuka/public/assets/avatar/css/stripe-checkout.css) 与 [stripe-checkout.js](/Users/apple/Documents/dujiaoshuka/public/assets/avatar/js/stripe-checkout.js)，接管 Stripe 收银页的样式与页面行为。
- 重写 [stripe/checkout.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/stripe/checkout.blade.php)，去掉 `cdn.jsdelivr` 上的 AmazeUI、jQuery、jQuery.qrcode 依赖，页面壳现在主要使用仓库内资源。
- [StripeCheckoutService.php](/Users/apple/Documents/dujiaoshuka/app/Service/StripeCheckoutService.php) 继续补充收银页配置数据，视图通过页面配置对象接收运行参数。
- 新增 [StripeCheckoutViewTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/StripeCheckoutViewTest.php)，直接守住“页面壳使用本地资源、不再依赖前端 CDN”这一约束。

影响范围：

- Stripe 收银页模板
- Stripe 前端资源依赖
- Stripe 页面协议边界

验证：

- 当前全量回归结果：`OK (87 tests, 251 assertions)`

下一步：

- 继续清理 Stripe 页面的旧式前端实现细节，为独立升级 SDK 和后续主题整合做准备。

### 67. 将 Stripe SDK 升级到 10.x 基线

摘要：

- 更新 [composer.json](/Users/apple/Documents/dujiaoshuka/composer.json) 中的 Stripe 依赖约束，从 `^7.84` 提升到 `^10.0`。
- 通过 Composer 更新后，当前锁定版本已提升到 `stripe/stripe-php v10.21.0`。
- 现有 Stripe 代码边界与回归测试在 `10.x` 基线上继续通过，说明前面完成的边界清理已经具备实际升级价值。

影响范围：

- Stripe SDK 版本基线
- 依赖锁文件
- 后续更高主版本升级准备

验证：

- `./scripts/composer74 show stripe/stripe-php`
- 当前版本：`v10.21.0`
- 当前全量回归结果：`OK (87 tests, 251 assertions)`

下一步：

- 继续清理 Stripe 剩余的旧式页面与交互耦合，并评估是否进一步升级到更高主版本。

### 68. 将 Stripe SDK 继续升级到 20.x 基线

摘要：

- 更新 [composer.json](/Users/apple/Documents/dujiaoshuka/composer.json) 中的 Stripe 依赖约束，从 `^10.0` 进一步提升到 `^20.0`。
- 通过 Composer 更新后，当前锁定版本已提升到 `stripe/stripe-php v20.0.0`。根据本地 `composer show` 结果，该版本发布时间为 `2026-03-26`。
- 现有 Stripe 代码边界和回归测试在 `20.x` 基线上继续通过，说明当前服务化切口已经足以承接更高主版本。

影响范围：

- Stripe SDK 版本基线
- 依赖锁文件
- 后续 Stripe 升级与维护策略

验证：

- `./scripts/composer74 show stripe/stripe-php`
- 当前版本：`v20.0.0`
- 当前全量回归结果：`OK (87 tests, 251 assertions)`

下一步：

- 继续清理 Stripe 支付页与前端协议中的遗留实现细节，在 `20.x` 基线上完成更彻底的稳定化收口。

### 69. 将系统设置与邮件配置从 Dcat 表单中抽到普通服务层

摘要：

- 新增 [SystemSettingService.php](/Users/apple/Documents/dujiaoshuka/app/Service/SystemSettingService.php)，统一负责系统设置的默认值、字段白名单、缓存读写。
- 新增 [MailConfigService.php](/Users/apple/Documents/dujiaoshuka/app/Service/MailConfigService.php)，统一负责从系统设置派生运行时邮件配置。
- [SystemSetting.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Forms/SystemSetting.php) 现在只保留表单结构，保存逻辑改为委托服务层。
- [EmailTest.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Forms/EmailTest.php)、[MailSend.php](/Users/apple/Documents/dujiaoshuka/app/Jobs/MailSend.php)、[functions.php](/Users/apple/Documents/dujiaoshuka/app/Helpers/functions.php) 已切到新的设置/邮件服务，不再各自直接操作 `system-setting` 缓存。
- 新增 [SystemSettingServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/SystemSettingServiceTest.php) 与 [MailConfigServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/MailConfigServiceTest.php) 守住设置白名单、默认值和邮件配置派生行为。

影响范围：

- 后台系统设置页
- 邮件测试与邮件发送运行时配置
- 全局系统配置读取边界

验证：

- 当前全量回归结果：`OK (90 tests, 264 assertions)`

下一步：

- 继续沿着后台降耦合路线，优先抽离后台批量动作和数据看板中的业务计算，让 `app/Admin` 进一步退化成薄展示层。

### 70. 将后台数据看板统计从 Dcat 图表组件抽到普通服务层

摘要：

- 新增 [AdminDashboardMetricsService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminDashboardMetricsService.php)，统一负责后台看板的成交率、销售额、成功订单数、支付状态占比统计。
- [DashBoard.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Charts/DashBoard.php)、[SalesCard.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Charts/SalesCard.php)、[SuccessOrderCard.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Charts/SuccessOrderCard.php)、[PayoutRateCard.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Charts/PayoutRateCard.php) 已改为只负责展示层拼装和调用服务层结果。
- 新增 [AdminDashboardMetricsServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AdminDashboardMetricsServiceTest.php)，直接守住后台看板统计口径。

影响范围：

- 后台首页数据看板
- 看板统计口径边界
- 后续后台壳替换成本

验证：

- 当前全量回归结果：`OK (92 tests, 276 assertions)`

下一步：

- 继续处理后台批量动作和导入能力，把更多 Dcat Action / Widget 背后的业务规则往普通服务层收口。

### 71. 将卡密导入能力从 Dcat 表单抽到普通服务层

摘要：

- 新增 [CarmiImportService.php](/Users/apple/Documents/dujiaoshuka/app/Service/CarmiImportService.php)，统一负责卡密文本解析、去重、批量入库和上传文件清理。
- [ImportCarmis.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Forms/ImportCarmis.php) 现在只保留表单输入和响应逻辑，不再直接承担导入规则。
- 新增 [CarmiImportServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/CarmiImportServiceTest.php)，守住“文本导入 / 上传文件导入 / 去重 / 空输入报错”几条关键约束。

影响范围：

- 后台卡密导入表单
- 卡密批量入库规则
- 后续后台导入能力迁移成本

验证：

- 当前全量回归结果：`OK (95 tests, 282 assertions)`

下一步：

- 继续清理后台批量动作和恢复类 Action，把通用恢复/导入逻辑进一步下沉到普通服务层。

### 72. 将后台通用恢复动作从 Dcat Action 抽到普通服务层

摘要：

- 新增 [SoftDeleteRestoreService.php](/Users/apple/Documents/dujiaoshuka/app/Service/SoftDeleteRestoreService.php)，统一负责软删除模型的单条恢复、批量恢复以及模型类型校验。
- [Restore.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Actions/Post/Restore.php) 与 [BatchRestore.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Actions/Post/BatchRestore.php) 已切到普通服务层，不再在 Dcat Action 内直接操作模型。
- 新增 [SoftDeleteRestoreServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/SoftDeleteRestoreServiceTest.php)，守住单条恢复、批量恢复和非法模型拒绝行为。

影响范围：

- 后台回收站恢复动作
- 后台批量恢复动作
- 后续后台 Action 迁移成本

验证：

- 当前全量回归结果：`OK (98 tests, 288 assertions)`

下一步：

- 继续沿着后台降耦合路线，优先检查后台控制器里还残留的批量导出、筛选和特殊格式化逻辑。

### 73. 将商品列表库存计算从后台控制器抽到普通服务层

摘要：

- 新增 [GoodsInventoryService.php](/Users/apple/Documents/dujiaoshuka/app/Service/GoodsInventoryService.php)，统一负责“自动发货商品按未售卡密实时统计库存、人工商品读取固定库存”这条规则。
- [GoodsController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/GoodsController.php) 已改为通过服务层解析库存，不再在 Grid 闭包里直接查库。
- 新增 [GoodsInventoryServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/GoodsInventoryServiceTest.php)，守住自动发货与人工处理两种库存规则。

影响范围：

- 后台商品列表库存展示
- 商品库存规则边界
- 后续后台列表层替换成本

验证：

- 当前全量回归结果：`OK (100 tests, 290 assertions)`

下一步：

- 继续清理后台控制器中的筛选、格式化和展示规则，把高频列表页进一步收敛到普通服务层或展示辅助层。

### 74. 将后台支付页与详情页展示格式化抽到普通服务层

摘要：

- 新增 [PayAdminPresenterService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PayAdminPresenterService.php)，统一负责支付通道生命周期徽标、客户端标签、支付方式标签和启用状态标签。
- 新增 [AdminTextareaPresenterService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminTextareaPresenterService.php)，统一负责后台详情页中长文本 textarea 的安全渲染。
- [PayController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/PayController.php)、[OrderController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/OrderController.php)、[GoodsController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/GoodsController.php) 已改为通过 presenter 服务输出展示内容，不再在控制器闭包里内嵌格式化分支。
- 新增 [PayAdminPresenterServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/PayAdminPresenterServiceTest.php) 与 [AdminTextareaPresenterServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AdminTextareaPresenterServiceTest.php)。

影响范围：

- 后台支付通道列表与详情页
- 后台订单详情页
- 后台商品详情页

验证：

- 当前全量回归结果：`OK (102 tests, 296 assertions)`

下一步：

- 继续清理后台控制器里的筛选选项来源和多对多表单格式化逻辑，让后台 CRUD 更接近纯展示壳。

### 75. 将后台 CRUD 选项来源与多选格式化抽到普通服务层

摘要：

- 新增 [AdminSelectOptionService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminSelectOptionService.php)，统一负责商品、自动发货商品、优惠码、支付通道、商品分组等后台下拉选项来源。
- 新增 [CouponAdminPresenterService.php](/Users/apple/Documents/dujiaoshuka/app/Service/CouponAdminPresenterService.php)，统一处理优惠码后台多选商品字段的 relation -> id 格式化。
- [CarmisController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/CarmisController.php)、[CouponController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/CouponController.php)、[GoodsController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/GoodsController.php)、[OrderController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/OrderController.php)、[ImportCarmis.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Forms/ImportCarmis.php) 已切到普通服务层，不再各自直接查模型拼选项。
- 新增 [AdminSelectOptionServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AdminSelectOptionServiceTest.php) 与 [CouponAdminPresenterServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/CouponAdminPresenterServiceTest.php)。

影响范围：

- 后台订单、商品、优惠码、卡密导入等高频 CRUD 页面
- 后台表单选项源与多对多字段格式化
- 后续后台壳层替换的重复工作量

验证：

- 当前全量回归结果：`OK (106 tests, 305 assertions)`

下一步：

- 继续清理后台控制器里剩余的状态/标签映射和详情页格式化分支，让 `app/Admin` 更接近纯展示入口。

### 76. 为仓库补上 GitHub Actions CI 基线工作流

摘要：

- 新增 [ci.yml](/Users/apple/Documents/dujiaoshuka/.github/workflows/ci.yml)，为仓库补上首个 GitHub Actions 工作流。
- 当前工作流会在 `push`、`pull_request`、手动 `workflow_dispatch` 下运行，使用 PHP `7.4` + MariaDB 恢复测试数据库并执行 PHPUnit。
- [README.md](/Users/apple/Documents/dujiaoshuka/README.md) 已加入 CI 徽章，并同步更新当前测试基线到 `OK (106 tests, 305 assertions)`。

影响范围：

- GitHub Actions 页面将不再是空白状态
- 主线提交和 PR 将获得基础自动回归
- 后续可以在此基础上再扩展到多版本或前端构建检查

验证：

- 本地当前全量回归结果：`OK (106 tests, 305 assertions)`

下一步：

- 继续推进后台薄壳化，同时观察首轮 GitHub Actions 是否一次通过，再决定是否追加第二条 workflow。

### 77. 补上本地快速拉站模板与准备脚本

摘要：

- 新增 [/.env.local.example](/Users/apple/Documents/dujiaoshuka/.env.local.example)，为本地快速启动提供一份不进仓的开发模板。
- 新增 [/scripts/prepare-local-dev](/Users/apple/Documents/dujiaoshuka/scripts/prepare-local-dev)，统一负责准备 `.env`、生成 `APP_KEY`、补齐 `install.lock`。
- [README.md](/Users/apple/Documents/dujiaoshuka/README.md) 已补上本地快速启动路径，仓库现在同时具备：
  - PHPUnit 回归入口
  - GitHub Actions CI 入口
  - 本地快速拉站入口

影响范围：

- 本地开发启动体验
- 新机器接手仓库的落地速度
- 后续把“拉站验证”纳入整改节奏的可操作性

验证：

- 本地当前全量回归结果：`OK (106 tests, 305 assertions)`

下一步：

- 继续完成一次真实 HTTP 启动验证，并把结果记回升级日志。

### 78. 完成本地 HTTP 启动验证

摘要：

- 使用 [/scripts/prepare-local-dev](/Users/apple/Documents/dujiaoshuka/scripts/prepare-local-dev) 生成了本地 `.env`，并验证了 Homebrew MariaDB socket 自适应路径。
- `./scripts/php74 artisan migrate:status --no-ansi` 已经验证数据库连接成功。
- 通过 `./scripts/php74 -S 127.0.0.1:8030 -t public` 启动本地服务，并使用 `curl -I http://127.0.0.1:8030/` 验证首页返回 `HTTP/1.1 200 OK`。
- 新增 [local-dev-quickstart.md](/Users/apple/Documents/dujiaoshuka/docs/local-dev-quickstart.md)，把这条本地拉站路径正式沉淀为文档。

影响范围：

- 本地开发启动体验
- 新机器接手仓库的可操作性
- 后续把“真实拉站验证”纳入整改节奏的可重复性

验证：

- `./scripts/php74 artisan --version`
- `./scripts/php74 artisan route:list`
- `./scripts/php74 artisan migrate:status --no-ansi`
- `curl -I http://127.0.0.1:8030/`
- 当前全量回归结果：`OK (106 tests, 305 assertions)`

下一步：

- 继续后台薄壳化与升级前清障，同时观察首轮 GitHub Actions CI 的运行结果。

补充观察：

- GitHub Actions `CI` 工作流已在 `add github actions ci baseline` 与 `add local dev bootstrap workflow` 两次主线推送上连续通过。

### 79. 将后台通用状态映射继续抽到普通服务层

摘要：

- 新增 [AdminStatusPresenterService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminStatusPresenterService.php)，统一负责后台“开关状态”“优惠码使用状态”等通用标签映射。
- [PayAdminPresenterService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PayAdminPresenterService.php) 已复用这组通用状态 presenter，不再自己维护开启/关闭标签。
- [GoodsGroupController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/GoodsGroupController.php) 与 [CouponController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/CouponController.php) 已切到普通服务层，不再在详情页闭包里手写状态映射。
- 新增 [AdminStatusPresenterServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AdminStatusPresenterServiceTest.php)。

影响范围：

- 后台商品分组详情页
- 后台优惠码详情页
- 后台通用状态标签映射边界

验证：

- GitHub Actions `CI` 连续两次主线推送通过
- 当前全量回归结果：`OK (108 tests, 309 assertions)`

下一步：

- 继续清理后台控制器里剩余的闭包格式化和映射逻辑，进一步把 `app/Admin` 压到展示壳层。

### 80. 将商品与卡密详情页展示映射继续抽到普通服务层

摘要：

- 新增 [CatalogAdminPresenterService.php](/Users/apple/Documents/dujiaoshuka/app/Service/CatalogAdminPresenterService.php)，统一负责商品类型标签、卡密状态标签、循环卡密标记。
- [GoodsController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/GoodsController.php) 已切到该 presenter，不再在详情页闭包里自己判断自动发货/人工处理。
- [CarmisController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/CarmisController.php) 的详情页和列表页也已复用该 presenter，不再在控制器里手写状态与循环标记逻辑。
- 新增 [CatalogAdminPresenterServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/CatalogAdminPresenterServiceTest.php)。

影响范围：

- 后台商品详情页
- 后台卡密列表页与详情页
- 后台目录与库存相关展示壳

验证：

- 当前全量回归结果：`OK (110 tests, 315 assertions)`

下一步：

- 继续清理剩余后台控制器中的闭包和格式化分支，并把高频页面尽量收口到 presenter / option service 层。

### 81. 将后台重复的回收站作用域判断抽到普通服务层

摘要：

- 新增 [AdminTrashScopeService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminTrashScopeService.php)，统一负责后台 `_scope_ = trashed` 判断。
- [GoodsGroupController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/GoodsGroupController.php)、[PayController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/PayController.php)、[CarmisController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/CarmisController.php)、[GoodsController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/GoodsController.php)、[OrderController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/OrderController.php)、[CouponController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/CouponController.php)、[EmailtplController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/EmailtplController.php) 已切到这层服务。
- 新增 [AdminTrashScopeServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AdminTrashScopeServiceTest.php)。

影响范围：

- 后台所有带回收站恢复动作的高频 CRUD 页面
- 后台 Action 显示条件的一致性
- 后续后台壳替换时的重复判断清理成本

验证：

- 当前全量回归结果：`OK (111 tests, 317 assertions)`

下一步：

- 继续清理后台控制器里剩余的闭包与细碎判断，优先收尾到“高频后台页几乎只做展示壳”的状态。

### 82. 将后台表单重复行为抽到普通服务层

摘要：

- 新增 [AdminFormBehaviorService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminFormBehaviorService.php)，统一负责：
  - 邮件模板 `tpl_token` 字段在创建/编辑场景下的行为策略
  - 后台表单 footer 的 `disableViewCheck` 行为
- [EmailtplController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/EmailtplController.php) 与 [GoodsGroupController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/GoodsGroupController.php) 已切到这层服务，不再在控制器里直接拼表单行为细节。
- 顺手移除了 [HomeController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/HomeController.php) 中无效的 `PopularGoodsCard` 引用。
- 新增 [AdminFormBehaviorServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AdminFormBehaviorServiceTest.php)。

影响范围：

- 后台邮件模板表单
- 后台商品分组表单
- 后台表单共性行为收口

验证：

- 当前全量回归结果：`OK (113 tests, 320 assertions)`

下一步：

- 继续清理后台控制器里剩余的零散闭包与判断分支，优先把高频后台页整理到“配置 + 展示壳 + 普通服务”的结构。

### 83. 将后台恢复动作挂载逻辑抽到普通服务层

摘要：

- 新增 [AdminGridRestoreActionService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminGridRestoreActionService.php)，统一负责后台列表页“是否挂载恢复动作”以及模型类传递。
- [GoodsGroupController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/GoodsGroupController.php)、[PayController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/PayController.php)、[CarmisController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/CarmisController.php)、[GoodsController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/GoodsController.php)、[OrderController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/OrderController.php)、[CouponController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/CouponController.php)、[EmailtplController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/EmailtplController.php) 已改为通过这层服务挂载 `Restore` / `BatchRestore`。
- 新增 [AdminGridRestoreActionServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AdminGridRestoreActionServiceTest.php)。

影响范围：

- 后台所有支持回收站恢复的高频 CRUD 页面
- 后台动作挂载条件的一致性
- 后续替换后台壳时的动作接线成本

验证：

- 当前全量回归结果：`OK (115 tests, 323 assertions)`

下一步：

- 继续把后台剩余细碎判断和展示闭包往普通服务层收口，朝“高频后台页仅保留展示配置”继续推进。

### 84. 将后台筛选规则抽到普通服务层

摘要：

- 新增 [AdminFilterService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminFilterService.php)，统一负责后台列表页的“回收站”筛选挂载，以及订单页 `created_at` 日期区间过滤条件应用。
- [GoodsGroupController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/GoodsGroupController.php)、[PayController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/PayController.php)、[CarmisController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/CarmisController.php)、[GoodsController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/GoodsController.php)、[OrderController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/OrderController.php)、[CouponController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/CouponController.php) 已改为通过这层服务复用筛选逻辑。
- 新增 [AdminFilterServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AdminFilterServiceTest.php)。

影响范围：

- 后台高频 CRUD 页面筛选条件的一致性
- 订单后台日期区间过滤逻辑的复用性
- 后续替换 Dcat 列表筛选层时的迁移成本

验证：

- 当前全量回归结果：`OK (117 tests, 326 assertions)`

下一步：

- 继续把后台详情页字段映射、状态文案和剩余零散闭包往普通服务层收口，进一步压薄 `app/Admin`。

### 85. 将后台详情字段挂载和展示回调进一步收口

摘要：

- 新增 [AdminDetailFieldService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminDetailFieldService.php)，统一负责后台 `Show` / `Form` 的机械字段挂载。
- [GoodsGroupController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/GoodsGroupController.php)、[PayController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/PayController.php)、[CarmisController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/CarmisController.php)、[OrderController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/OrderController.php)、[CouponController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/CouponController.php)、[EmailtplController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/EmailtplController.php) 里的详情页与只读表单字段长列表已收口到这层服务。
- 同时把一批简单的 `as/display` 匿名闭包改成了直接指向 presenter/service 的 callable，后台控制器里只剩少数确实依赖行上下文的展示闭包。
- 新增 [AdminDetailFieldServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AdminDetailFieldServiceTest.php)。

影响范围：

- 后台详情页与只读表单字段声明的一致性
- 后台 presenter 回调的可读性与可迁移性
- 后续替换 Dcat 时的字段映射搬迁成本

验证：

- 当前全量回归结果：`OK (119 tests, 328 assertions)`

下一步：

- 继续清理后台仅剩的高上下文展示闭包，并开始评估哪些后台页面已经足够适合迁到新的展示壳。

### 86. 将后台页面壳和首页看板布局抽到普通服务层

摘要：

- 新增 [AdminPageCardService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminPageCardService.php)，统一负责“标题 + Card 表单页”这一类后台页面壳拼装。
- [SystemSettingController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/SystemSettingController.php)、[EmailTestController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/EmailTestController.php)、[CarmisController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/CarmisController.php) 的对应页面已切到这层服务。
- 新增 [AdminDashboardLayoutService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminDashboardLayoutService.php)，后台首页 [HomeController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/HomeController.php) 不再直接手写看板布局。
- 新增 [AdminPageCardServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AdminPageCardServiceTest.php) 与 [AdminDashboardLayoutServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AdminDashboardLayoutServiceTest.php)。

影响范围：

- 后台首页看板布局的可替换性
- 后台卡片式管理页的壳层一致性
- 后续替换 Dcat 时的页面装配成本

验证：

- 当前全量回归结果：`OK (121 tests, 333 assertions)`

下一步：

- 继续清理后台仅剩的高上下文闭包，并开始标记哪些后台页已经接近可迁移状态。

### 87. 将后台恢复接线与剩余展示边界进一步压薄

摘要：

- [AdminGridRestoreActionService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminGridRestoreActionService.php) 新增 `attachRowRestore()` 与 `attachBatchRestore()`，后台各 CRUD 页不再自己判断回收站作用域后再手动挂 `Restore` / `BatchRestore`。
- [GoodsGroupController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/GoodsGroupController.php)、[PayController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/PayController.php)、[CarmisController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/CarmisController.php)、[GoodsController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/GoodsController.php)、[OrderController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/OrderController.php)、[CouponController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/CouponController.php)、[EmailtplController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/EmailtplController.php) 的恢复动作接线已进一步收口。
- [PayController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/PayController.php) 的生命周期徽章展示已经改成模型 accessor + presenter callable，不再依赖控制器匿名闭包。
- [GoodsInventoryService.php](/Users/apple/Documents/dujiaoshuka/app/Service/GoodsInventoryService.php) 新增 `resolveStockFromRow()`，商品列表库存展示仍保留 Dcat 行上下文闭包，但控制器已不再临时组装模型对象。
- 更新了 [AdminGridRestoreActionServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AdminGridRestoreActionServiceTest.php) 与 [GoodsInventoryServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/GoodsInventoryServiceTest.php)。

影响范围：

- 后台 CRUD 页动作挂载的一致性
- 支付后台页展示壳的可迁移性
- 商品列表库存展示的控制器耦合度

验证：

- 当前全量回归结果：`OK (124 tests, 338 assertions)`

下一步：

- 继续围绕 `GoodsController` 最后一个高上下文库存闭包评估更合适的迁移切口，并开始标记优先迁移的后台页面。

### 88. 去掉后台控制器中最后一个高上下文库存闭包

摘要：

- [GoodsController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/GoodsController.php) 的商品库存列已改为通过列表查询 `withCount` 预载未售卡密数量，不再依赖控制器内的 `display(function () {})` 闭包。
- [Goods.php](/Users/apple/Documents/dujiaoshuka/app/Models/Goods.php) 既有的 `getInStockAttribute()` 现在真正成为后台库存展示的主边界：自动发货商品读取未售卡密数，人工商品继续读取存量字段。
- 新增 [GoodsModelInventoryAttributeTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/GoodsModelInventoryAttributeTest.php) 护栏，直接守住模型库存读取行为。

影响范围：

- 商品后台列表库存展示
- 后台控制器对 Dcat 行上下文的耦合度
- 后续迁移商品后台页时的数据读取边界

验证：

- 当前全量回归结果：`OK (126 tests, 340 assertions)`

下一步：

- 开始盘点哪些后台页面已经足够“服务化 + 壳层化”，可以进入优先迁移名单。

### 89. 固化后台迁移优先级清单

摘要：

- 新增 [admin-migration-candidates.md](/Users/apple/Documents/dujiaoshuka/docs/admin-migration-candidates.md)，将后台页面按迁移风险、Dcat 绑定度、服务化完成度、验证成本划分为三档优先级。
- [admin-replacement-assessment.md](/Users/apple/Documents/dujiaoshuka/docs/admin-replacement-assessment.md) 已接入这份优先级清单，后续后台替换不再从零盘点页面顺序。
- 当前默认迁移顺序已明确为：商品分类 -> 邮件模板 -> 支付通道 -> 优惠码 -> 卡密 -> 系统设置/邮件测试 -> 商品 -> 订单 -> 首页看板。

影响范围：

- 后台替换阶段的实施顺序
- 后续新后台壳样板选择
- 资源投入与验证计划安排

验证：

- 当前全量回归结果：`OK (126 tests, 340 assertions)`

下一步：

- 继续沿优先级清单推进，优先把第一优先级页面的 Dcat 迁移切口做得更清楚。

### 90. 固化第一批后台迁移页面合同

摘要：

- 新增 [admin-first-batch-migration-contracts.md](/Users/apple/Documents/dujiaoshuka/docs/admin-first-batch-migration-contracts.md)，明确第一批迁移页面的列表列、筛选项、详情字段、表单字段和页面行为。
- 当前已为商品分类、邮件模板、支付通道 3 组页面固定迁移合同。
- [admin-migration-candidates.md](/Users/apple/Documents/dujiaoshuka/docs/admin-migration-candidates.md) 已接入这份合同文档，后续新后台壳开发可以直接按合同落地，而不需要再次反推 Dcat 页面。

影响范围：

- 第一批后台迁移实施效率
- 新后台壳开发的输入清晰度
- 后续验收清单的稳定性

验证：

- 当前全量回归结果：`OK (126 tests, 340 assertions)`

下一步：

- 在第一批页面中选择一个页面作为真正的新后台壳样板，优先从商品分类或邮件模板入手。

### 91. 落地第一个新后台壳样板页

摘要：

- 新增 [GoodsGroupShellController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/AdminShell/GoodsGroupShellController.php) 与 [AdminShellGoodsGroupPageService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellGoodsGroupPageService.php)，把商品分类管理的列表页与详情页先用普通 Laravel 控制器 + 服务层跑起来。
- 新增后台样板页视图：
  - [admin-shell/layout.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin-shell/layout.blade.php)
  - [admin-shell/goods-group/index.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin-shell/goods-group/index.blade.php)
  - [admin-shell/goods-group/show.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin-shell/goods-group/show.blade.php)
- [app/Admin/routes.php](/Users/apple/Documents/dujiaoshuka/app/Admin/routes.php) 已挂出 `/admin/v2/goods-group` 和 `/admin/v2/goods-group/{id}` 作为实验性新后台壳入口。
- [config/admin.php](/Users/apple/Documents/dujiaoshuka/config/admin.php) 已将 `v2/goods-group*` 加入权限例外，保证样板页在现有后台鉴权下可直接访问。
- 新增 [AdminShellGoodsGroupControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Feature/AdminShellGoodsGroupControllerTest.php) 验证样板列表页和详情页真实可访问。

影响范围：

- 第一批后台迁移的真实落地起点
- 新后台壳布局、筛选、列表、详情页的样板实现
- 后续迁移邮件模板和支付通道页的复用基础

验证：

- 当前全量回归结果：`OK (128 tests, 346 assertions)`

下一步：

- 继续沿第一批合同推进，优先补邮件模板或支付通道的新后台壳样板。

### 92. 落地第二个新后台壳样板页

摘要：

- 新增 [EmailTemplateShellController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/AdminShell/EmailTemplateShellController.php) 与 [AdminShellEmailTemplatePageService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellEmailTemplatePageService.php)，把邮件模板管理的列表页与详情页落成普通 Laravel 页面。
- 新增后台样板页视图：
  - [admin-shell/emailtpl/index.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin-shell/emailtpl/index.blade.php)
  - [admin-shell/emailtpl/show.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin-shell/emailtpl/show.blade.php)
- [app/Admin/routes.php](/Users/apple/Documents/dujiaoshuka/app/Admin/routes.php) 已挂出 `/admin/v2/emailtpl` 与 `/admin/v2/emailtpl/{id}`。
- [config/admin.php](/Users/apple/Documents/dujiaoshuka/config/admin.php) 已将 `v2/emailtpl*` 加入后台权限例外。
- 新增 [AdminShellEmailTemplateControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Feature/AdminShellEmailTemplateControllerTest.php) 验证列表页与详情页真实可访问。

影响范围：

- 第一批后台迁移第二张样板页
- 新后台壳在“列表 + 详情 + 文本内容展示”场景下的复用基础
- 后续第三张支付通道样板页的布局复用

验证：

- 当前全量回归结果：`OK (130 tests, 353 assertions)`

下一步：

- 继续沿第一批合同推进，优先补支付通道的新后台壳样板。

### 93. 落地第三个新后台壳样板页

摘要：

- 新增 [PayShellController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/AdminShell/PayShellController.php) 与 [AdminShellPayPageService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellPayPageService.php)，把支付通道管理的列表页与详情页也落成普通 Laravel 页面。
- 新增后台样板页视图：
  - [admin-shell/pay/index.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin-shell/pay/index.blade.php)
  - [admin-shell/pay/show.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin-shell/pay/show.blade.php)
- [app/Admin/routes.php](/Users/apple/Documents/dujiaoshuka/app/Admin/routes.php) 已挂出 `/admin/v2/pay` 与 `/admin/v2/pay/{id}`。
- [config/admin.php](/Users/apple/Documents/dujiaoshuka/config/admin.php) 已将 `v2/pay*` 加入后台权限例外。
- 新增 [AdminShellPayControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Feature/AdminShellPayControllerTest.php) 验证样板列表页与详情页真实可访问。

影响范围：

- 第一批后台迁移三张样板页全部落地
- 新后台壳在“状态文案 + 生命周期徽章 + 列表/详情”场景下的复用基础
- 后续可以从样板页阶段切到批量迁移阶段

验证：

- 当前全量回归结果：`OK (132 tests, 360 assertions)`

下一步：

- 开始把第一批样板页提炼成可复用的后台壳组件，准备进入批量迁移阶段。

### 94. 抽出新后台壳可复用视图片段

摘要：

- 新增后台壳公共视图片段：
  - [partials/page-header.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin-shell/partials/page-header.blade.php)
  - [partials/filter-panel.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin-shell/partials/filter-panel.blade.php)
  - [partials/detail-grid.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin-shell/partials/detail-grid.blade.php)
- 已将商品分类、邮件模板、支付通道三组样板页的头部、筛选面板、详情网格切到统一片段。
- 新后台壳已经从“单页样板复制”进入“可复用页面片段”阶段，为后续批量迁移打底。

影响范围：

- 第一批样板页结构一致性
- 后续后台页面迁移的模板复用成本
- 新后台壳设计语言的一致性

验证：

- 当前全量回归结果：`OK (132 tests, 360 assertions)`

下一步：

- 继续把列表表格与详情字段数据源也抽成更统一的数据结构，进入批量迁移底座阶段。

### 95. 修复 GitHub Actions 测试环境缺失 APP_KEY

摘要：

- GitHub Actions 最近几次 `CI` 失败并非业务回归，而是新增后台壳 Feature 测试在 HTTP 请求阶段触发了 Laravel 加密服务，而工作流环境没有提供 `APP_KEY`。
- 已在 [phpunit.xml](/Users/apple/Documents/dujiaoshuka/phpunit.xml) 和 [ci.yml](/Users/apple/Documents/dujiaoshuka/.github/workflows/ci.yml) 中补齐测试专用 `APP_KEY`，让本地 PHPUnit 与 GitHub Actions 保持同一套测试环境前提。

影响范围：

- GitHub Actions `CI`
- 全量 PHPUnit 运行环境一致性
- 后续 Feature 测试继续扩充时的稳定性

验证：

- 当前全量回归结果：`OK (132 tests, 360 assertions)`

下一步：

- 观察最新一轮 GitHub Actions 结果，确认 `CI` 恢复为绿色后继续后台壳底座抽象。

### 96. 移除仓库内硬编码测试 APP_KEY

摘要：

- GitHub secret scanning 提示仓库暴露了 Laravel `APP_KEY`，定位到问题来自测试环境里硬编码的测试 key，而不是运行中的生产配置。
- 已将 PHPUnit 启动方式改为加载 [tests/bootstrap.php](/Users/apple/Documents/dujiaoshuka/tests/bootstrap.php)，在测试进程启动时动态生成临时 `APP_KEY`。
- 已从 [phpunit.xml](/Users/apple/Documents/dujiaoshuka/phpunit.xml) 和 [ci.yml](/Users/apple/Documents/dujiaoshuka/.github/workflows/ci.yml) 中移除明文 `APP_KEY`，避免后续再次触发 GitHub 告警。

影响范围：

- GitHub secret scanning 告警面
- 本地 PHPUnit 启动方式
- GitHub Actions `CI` 测试环境初始化

验证：

- 预期全量 PHPUnit 仍保持通过

下一步：

- 重新跑本地 PHPUnit，并观察 GitHub Actions 新一轮结果，确认仓库已不再包含可疑 Laravel key。

### 97. 将新后台壳样板页切到结构化表格与详情数据

摘要：

- 新增 [data-table.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin-shell/partials/data-table.blade.php)，作为新后台壳统一列表表格片段。
- 商品分类、邮件模板、支付通道三张后台壳样板页已从“Blade 内部手写表头/行/详情项”改为“页面服务产出结构化数据，视图只负责渲染”。
- [AdminShellGoodsGroupPageService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellGoodsGroupPageService.php)、[AdminShellEmailTemplatePageService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellEmailTemplatePageService.php)、[AdminShellPayPageService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellPayPageService.php) 现在都具备了 `buildTable()` / `detailItems()` 这一层页面数据合同。
- 新增 [AdminShellPageStructureTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AdminShellPageStructureTest.php)，专门守住三张后台壳样板页的结构化数据输出。

影响范围：

- 第一批后台壳样板页的复用粒度
- 后续批量迁移后台页面时的数据合同一致性
- Blade 模板与页面服务职责边界

验证：

- 当前全量回归结果：`OK (135 tests, 372 assertions)`

下一步：

- 继续抽筛选字段与页面元信息合同，把后台壳迁移从“复用片段”推进到“复用整页结构配置”。

### 98. 将新后台壳样板页切到结构化头部与筛选合同

摘要：

- 商品分类、邮件模板、支付通道三张后台壳样板页，已将页面头部信息与筛选面板配置收进页面服务。
- [AdminShellGoodsGroupPageService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellGoodsGroupPageService.php)、[AdminShellEmailTemplatePageService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellEmailTemplatePageService.php)、[AdminShellPayPageService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellPayPageService.php) 现在都具备 `buildHeader()` / `buildFilters()`。
- 三个后台壳控制器现在统一按“头部合同 + 筛选合同 + 表格合同 + 详情合同”组合页面数据，新页面迁移已经更接近纯配置式接入。
- [AdminShellPageStructureTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AdminShellPageStructureTest.php) 已扩展为同时守住头部、筛选、表格和详情四层结构输出。

影响范围：

- 第一批后台壳样板页的页面装配方式
- 后续后台壳批量迁移时的结构复用粒度
- 控制器到页面服务的数据边界

验证：

- 当前全量回归结果：`OK (135 tests, 378 assertions)`

下一步：

- 继续抽详情动作、列表操作和通用空态/文案配置，让后台壳进入更完整的批量迁移底座阶段。

### 99. 将新后台壳样板页切到结构化详情页头部动作

摘要：

- 商品分类、邮件模板、支付通道三张后台壳样板页，已将详情页头部标题、说明和返回动作一并收进页面服务。
- 三个页面服务现在都具备 `buildShowHeader()`，控制器和 Blade 不再自己拼详情页头部数组。
- [AdminShellPageStructureTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AdminShellPageStructureTest.php) 已扩展为覆盖详情页动作链接与 scope 保留行为。

影响范围：

- 第一批后台壳样板页详情页装配方式
- 后续后台壳详情页迁移时的动作复用粒度
- 页面服务对“头部 + 筛选 + 表格 + 详情 + 动作”的完整合同能力

验证：

- 当前全量回归结果：`OK (135 tests, 384 assertions)`

下一步：

- 继续抽列表操作列和通用空态/说明文案配置，让后台壳底座更接近批量迁移模板。

### 100. 将新后台壳样板页切到结构化操作列与空态文案

摘要：

- 商品分类、邮件模板、支付通道三张后台壳样板页，已将列表操作列文本与空态文案收进页面服务。
- [data-table.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin-shell/partials/data-table.blade.php) 现在支持统一渲染 `empty_title` 与 `empty_description`。
- 三个页面服务的 `buildTable()` 已开始统一产出操作列内容、空态标题与空态说明；`buildHeader()` 也已经带有统一的“迁移合同”入口动作。
- [AdminShellPageStructureTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AdminShellPageStructureTest.php) 已扩展为覆盖操作列与空态结构输出。

影响范围：

- 第一批后台壳样板页的列表空态与操作列一致性
- 后续后台壳页面批量迁移时的列表装配复用粒度
- 页面服务对整页展示文案的集中承载能力

验证：

- 当前全量回归结果：`OK (135 tests, 390 assertions)`

下一步：

- 继续抽通用详情动作与页面级配置对象，让后台壳从“页面服务合同”继续走向“迁移模板骨架”。

### 101. 将新后台壳样板页切到通用页面模板

摘要：

- 新增通用后台壳页面模板：
  - [pages/index.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin-shell/pages/index.blade.php)
  - [pages/show.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin-shell/pages/show.blade.php)
- 商品分类、邮件模板、支付通道三张后台壳样板页已经不再依赖各自单独的页面模板，统一走通用页面骨架。
- 三个页面服务现在都具备 `buildIndexPageData()` / `buildShowPageData()`，控制器进一步收敛成“查询数据 + 交给页面服务组装整页配置”。
- [AdminShellPageStructureTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AdminShellPageStructureTest.php) 已扩展为守住整页配置对象输出。

影响范围：

- 第一批后台壳样板页的模板复用方式
- 后续批量迁移后台页面时的骨架复用粒度
- 控制器、页面服务与 Blade 模板之间的职责分层

验证：

- 当前全量回归结果：`OK (135 tests, 396 assertions)`

下一步：

- 继续把筛选解析和页面配置再往上抽，朝“单页声明式迁移模板”推进。

### 102. 将新后台壳样板页切到服务内筛选解析

摘要：

- 商品分类、邮件模板、支付通道三张后台壳样板页，已将 `Request` 中的筛选参数解析下沉到页面服务。
- 三个页面服务现在都具备 `extractFilters()`，控制器不再自己维护筛选字段白名单数组。
- [AdminShellPageStructureTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AdminShellPageStructureTest.php) 已扩展为守住筛选解析输出。

影响范围：

- 第一批后台壳样板页控制器复杂度
- 后续后台壳页面迁移时的筛选参数接入方式
- 页面服务对“筛选输入 -> 页面配置输出”的闭环能力

验证：

- 当前全量回归结果：`OK (135 tests, 399 assertions)`

下一步：

- 继续往“单页声明式迁移模板”推进，开始提炼更明确的页面配置对象或通用壳控制器基类。

### 103. 为新后台壳引入页面配置对象

摘要：

- 新增后台壳页面配置 DTO：
  - [AdminShellIndexPageData.php](/Users/apple/Documents/dujiaoshuka/app/Service/DataTransferObjects/AdminShellIndexPageData.php)
  - [AdminShellShowPageData.php](/Users/apple/Documents/dujiaoshuka/app/Service/DataTransferObjects/AdminShellShowPageData.php)
- 商品分类、邮件模板、支付通道三张后台壳样板页的页面服务，已将 `buildIndexPageData()` / `buildShowPageData()` 从散数组升级为明确的页面配置对象。
- 三个后台壳控制器现在统一通过 `toViewData()` 把页面配置对象交给通用页面模板，新后台壳已经更接近声明式迁移骨架。
- [AdminShellPageStructureTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AdminShellPageStructureTest.php) 已扩展为覆盖页面配置对象实例与 `toViewData()` 输出。

影响范围：

- 第一批后台壳样板页的整页配置边界
- 后续后台壳批量迁移的声明式接入能力
- 控制器、页面服务、Blade 模板之间的数据传递契约

验证：

- 当前全量回归结果：`OK (135 tests, 408 assertions)`

下一步：

- 继续提炼通用壳控制器基类或页面工厂，让不同后台页的迁移代码进一步收敛。

### 104. 为新后台壳引入通用控制器基类

摘要：

- 新增 [BaseAdminShellController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/AdminShell/BaseAdminShellController.php)，统一承载后台壳 `index/show` 页的通用渲染流程。
- 商品分类、邮件模板、支付通道三张后台壳样板页控制器已切到通用基类，不再各自重复维护“提取筛选 -> 查询 -> 组装页面配置 -> 渲染模板”这条流程。
- 新后台壳控制器层已经进一步退化为“声明式接线”，后续迁移新页面时只需要关注页级差异。

影响范围：

- 第一批后台壳样板页控制器结构
- 后续后台壳页面迁移时的控制器复用能力
- 后台壳基座从页面服务扩展到控制器层

验证：

- 当前全量回归结果：`OK (135 tests, 408 assertions)`

下一步：

- 继续提炼页面工厂或页级配置协议，让不同后台页能更统一地接入壳模板与控制器基类。

### 105. 为新后台壳引入页面服务协议

摘要：

- 新增 [AdminShellPageServiceInterface.php](/Users/apple/Documents/dujiaoshuka/app/Service/Contracts/AdminShellPageServiceInterface.php)，把后台壳页面服务统一到同一套协议下。
- 商品分类、邮件模板、支付通道三张后台壳样板页的页面服务已经全部实现该协议。
- [BaseAdminShellController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/AdminShell/BaseAdminShellController.php) 现在通过统一协议驱动页面服务，三个后台壳控制器已进一步退化成仅声明服务类和 scope 行为。
- [AdminShellPageStructureTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AdminShellPageStructureTest.php) 已扩展为覆盖服务协议实现。

影响范围：

- 第一批后台壳样板页页面服务的统一契约
- 后续后台壳页面迁移时的可替换性与复用度
- 控制器基类与页面服务之间的协议边界

验证：

- 当前全量回归结果：`OK (135 tests, 411 assertions)`

下一步：

- 继续提炼页面工厂或资源注册表，让不同后台页能按统一注册方式挂入后台壳。

### 106. 为新后台壳引入资源注册表

摘要：

- 新增 [AdminShellResourceRegistry.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellResourceRegistry.php)，统一注册后台壳资源与其页面服务、`scope` 行为。
- [BaseAdminShellController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/AdminShell/BaseAdminShellController.php) 已改为通过资源注册表解析页面服务，不再依赖子控制器手写服务类属性之外的接线逻辑。
- 商品分类、邮件模板、支付通道三张后台壳样板页控制器已进一步退化成仅声明 `resourceKey`。
- 新增 [AdminShellResourceRegistryTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AdminShellResourceRegistryTest.php) 守住注册表映射。

影响范围：

- 第一批后台壳样板页的资源接入方式
- 后续后台壳页面批量迁移时的统一注册能力
- 控制器基类与页面服务协议之间的调度层

验证：

- 当前全量回归结果：`OK (138 tests, 417 assertions)`

下一步：

- 继续提炼更明确的资源元数据或页面工厂，让新后台页真正按“注册一个资源”即可接入后台壳。

### 107. 修复后台看板统计的跨时段脆弱测试

摘要：

- [AdminDashboardMetricsServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AdminDashboardMetricsServiceTest.php) 中有一条基于“today”范围的测试依赖当前时间，白天与凌晨运行结果不稳定。
- 已将测试订单时间改为“当天开始后 1 秒”，避免在凌晨或白天不同时间段落入未来时间导致统计为 0。

影响范围：

- 后台看板统计测试稳定性
- GitHub Actions 与本地全量回归的一致性

验证：

- 当前全量回归结果：`OK (138 tests, 417 assertions)`

下一步：

- 继续保持测试对时间边界的稳定性要求，避免后续 CI 再出现时段型假红。

### 108. 为新后台壳引入路由注册器

摘要：

- 新增 [AdminShellRouteRegistrar.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellRouteRegistrar.php)，把后台壳 `index/show` 路由注册逻辑接入资源注册表。
- [app/Admin/routes.php](/Users/apple/Documents/dujiaoshuka/app/Admin/routes.php) 已从手写三组 `v2/*` 路由切到通过注册器统一挂载。
- [AdminShellResourceRegistry.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellResourceRegistry.php) 现在除了页面服务与 `scope` 行为，也统一记录后台壳控制器映射。
- 新增 [AdminShellRouteRegistrarTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AdminShellRouteRegistrarTest.php) 守住路由挂载行为。

影响范围：

- 第一批后台壳样板页的路由接入方式
- 后续后台壳资源批量注册能力
- 资源注册表从“页面服务注册”扩展到“控制器 + 路由注册”调度中心

验证：

- 当前全量回归结果：`OK (139 tests, 420 assertions)`

下一步：

- 继续提炼资源元数据和通用路由约定，朝“注册一个资源即可接入后台壳”再收一层。

### 109. 让后台壳权限白名单跟随资源注册表生成

摘要：

- [AdminShellResourceRegistry.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellResourceRegistry.php) 现在新增 `definitions()` 与 `permissionExceptPatterns()`，可以统一产出后台壳资源元数据与权限白名单模式。
- [config/admin.php](/Users/apple/Documents/dujiaoshuka/config/admin.php) 已从手写三条 `v2/*` 白名单，切到通过资源注册表动态派生。
- [AdminShellResourceRegistryTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AdminShellResourceRegistryTest.php) 已扩展为覆盖权限白名单派生结果。

影响范围：

- 后台壳权限白名单维护方式
- 后续新增后台壳资源时的接入一致性
- 资源注册表从路由与控制器调度中心继续扩展到权限约定中心

验证：

- 当前全量回归结果：`OK (140 tests, 421 assertions)`

下一步：

- 继续提炼资源元数据，让菜单、导航或页面元信息也逐步从注册表派生。

### 110. 让后台壳导航跟随资源注册表生成

摘要：

- [AdminShellResourceRegistry.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellResourceRegistry.php) 现在新增导航元信息与 `navigationItems()`。
- [layout.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin-shell/layout.blade.php) 已从手写三条侧边导航，切到通过资源注册表动态派生。
- [AdminShellResourceRegistryTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AdminShellResourceRegistryTest.php) 已扩展为覆盖导航项派生结果。

影响范围：

- 后台壳样板页侧边导航的维护方式
- 后续新增后台壳资源时的导航接入成本
- 资源注册表从路由、控制器、权限中心扩展到导航元数据中心

验证：

- 当前全量回归结果：`OK (141 tests, 424 assertions)`

下一步：

- 继续提炼资源元数据，让资源标题、分组、说明等页面级元信息也逐步从注册表集中派生。

### 111. 让后台壳页面元信息跟随资源注册表生成

摘要：

- [AdminShellResourceRegistry.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellResourceRegistry.php) 现在继续收拢页面级元信息，包含：
  - 导航分组标题
  - 列表页标题与说明
  - 详情页标题与说明
- 商品分类、邮件模板、支付通道三张后台壳样板页的页面服务，已改为从资源注册表读取这些元信息，而不再各自硬编码重复文案。
- [layout.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin-shell/layout.blade.php) 的导航分组标题也已经走注册表派生。
- [AdminShellResourceRegistryTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AdminShellResourceRegistryTest.php) 已扩展为守住页面元信息派生结果。

影响范围：

- 后台壳样板页标题、说明、导航分组的维护方式
- 后续新增后台壳资源时的页面元信息接入成本
- 资源注册表从路由/权限/导航中心进一步扩展到页面元信息中心

验证：

- 当前全量回归结果：`OK (142 tests, 427 assertions)`

下一步：

- 继续收拢更多资源级配置，让新后台壳更接近“注册一个资源即可完整生成页面骨架”的状态。

### 112. 引入后台壳页面服务抽象基类

摘要：

- 新增 [AbstractAdminShellPageService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AbstractAdminShellPageService.php)，统一承载后台壳页面服务共享的资源定义读取、列表页头构造、详情页头构造、页面标题生成和迁移合同动作。
- 商品分类、邮件模板、支付通道三张后台壳样板页的页面服务，都已经改成继承这层抽象基类，不再分别维护重复的页头和标题装配逻辑。
- [AdminShellPageStructureTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AdminShellPageStructureTest.php) 已补充断言，确保三张样板页继续共享同一套页面服务基座。

影响范围：

- 后台壳页面服务的继承结构
- 后台壳样板页列表/详情页头的共用方式
- 后续新增后台壳资源时的页面服务样板成本

验证：

- 当前全量回归结果：`OK (143 tests, 430 assertions)`

下一步：

- 继续把资源级动作和页面行为往注册表与抽象层收拢，让新增后台壳资源更接近“只写查询和字段合同”。

### 113. 落地第二批后台壳样板页：优惠码管理

摘要：

- 新增 [CouponShellController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/AdminShell/CouponShellController.php) 和 [AdminShellCouponPageService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellCouponPageService.php)，将优惠码管理接入新后台壳。
- [AdminShellResourceRegistry.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellResourceRegistry.php) 已加入 `coupon` 资源定义，因此后台壳路由、权限白名单和导航会自动接入 `/admin/v2/coupon`。
- 新增 [AdminShellCouponControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Feature/AdminShellCouponControllerTest.php) 与 [AdminShellPageStructureTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AdminShellPageStructureTest.php) 的优惠码页断言，守住第二批迁移样板的展示合同。

影响范围：

- 第二批后台页的实际迁移起点
- 后台壳资源注册表的批量扩展能力
- 优惠码管理页的只读样板接入路径

验证：

- 当前全量回归结果：`OK (146 tests, 441 assertions)`

下一步：

- 继续沿着第二批优先级推进卡密管理或系统设置，让后台壳从第一批样板扩展到更复杂的页面类型。

### 114. 落地第二批后台壳样板页：卡密管理

摘要：

- 新增 [CarmisShellController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/AdminShell/CarmisShellController.php) 和 [AdminShellCarmisPageService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellCarmisPageService.php)，将卡密管理接入新后台壳。
- [AdminShellResourceRegistry.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellResourceRegistry.php) 已加入 `carmis` 资源定义，因此后台壳路由、权限白名单和导航会自动接入 `/admin/v2/carmis`。
- 新增 [AdminShellCarmisControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Feature/AdminShellCarmisControllerTest.php) 与 [AdminShellPageStructureTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AdminShellPageStructureTest.php) 的卡密页断言，守住第二批第二张样板页的展示合同。

影响范围：

- 第二批后台页的实际迁移覆盖面
- 后台壳资源注册表的复用能力
- 卡密管理页的只读样板接入路径

验证：

- 当前全量回归结果：`OK (151 tests, 473 assertions)`

下一步：

- 继续沿着第二批优先级推进系统设置或邮件测试，让后台壳开始覆盖非标准 CRUD 页面。

### 115. 落地第二批后台壳配置型样板页：系统设置概览

摘要：

- 新增 [SystemSettingShellController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/AdminShell/SystemSettingShellController.php) 和 [AdminShellSystemSettingPageService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellSystemSettingPageService.php)，把系统设置作为分组化只读页面接入后台壳。
- [AdminShellResourceRegistry.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellResourceRegistry.php) 已加入 `system-setting` 资源定义，因此后台壳路由、权限白名单和导航会自动接入 `/admin/v2/system-setting`。
- 新增 [AdminShellSystemSettingControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Feature/AdminShellSystemSettingControllerTest.php)，并扩展 [AdminShellPageStructureTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AdminShellPageStructureTest.php) 与 [AdminShellResourceRegistryTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AdminShellResourceRegistryTest.php)，守住配置型页面的展示合同。

影响范围：

- 第二批后台页从标准 CRUD 扩展到配置型页面
- 后台壳资源注册表对非标准页面的承载能力
- 系统设置概览页的只读样板接入路径

验证：

- 当前全量回归结果：`OK (154 tests, 493 assertions)`

下一步：

- 继续推进邮件测试或系统设置编辑路径，让后台壳开始覆盖操作型配置页面。

### 116. 落地第二批后台壳配置型样板页：邮件测试概览

摘要：

- 新增 [EmailTestShellController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/AdminShell/EmailTestShellController.php) 和 [AdminShellEmailTestPageService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellEmailTestPageService.php)，把邮件测试页作为配置型样板接入后台壳。
- [AdminShellResourceRegistry.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellResourceRegistry.php) 已加入 `email-test` 资源定义，并将后台壳导航分组统一更新为 `Admin Shell`，以匹配当前已不止第一批样板页的现状。
- 新增 [AdminShellEmailTestControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Feature/AdminShellEmailTestControllerTest.php)，并扩展 [AdminShellPageStructureTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AdminShellPageStructureTest.php) 与 [AdminShellResourceRegistryTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AdminShellResourceRegistryTest.php)，守住邮件测试概览页的展示合同。

影响范围：

- 第二批配置型页面的覆盖面
- 后台壳导航元信息的语义一致性
- 邮件测试页的后台壳接入路径

验证：

- 当前全量回归结果：`OK (159 tests, 522 assertions)`

下一步：

- 继续推进操作型配置页面，优先评估系统设置编辑入口或邮件测试发送动作的后台壳过渡方案。

### 117. 建立配置型页面的后台壳过渡操作入口

摘要：

- [AbstractAdminShellPageService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AbstractAdminShellPageService.php) 现在支持从资源注册表派生默认页头动作，并在配置型页面上自动追加“进入旧版功能页”入口。
- [AdminShellResourceRegistry.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellResourceRegistry.php) 已为 `system-setting` 与 `email-test` 增加 `legacy_uri`，用于把后台壳样板页和旧 Dcat 操作页安全接起来。
- [page-header.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin-shell/partials/page-header.blade.php) 已调整为可同时显示元信息和操作按钮，使后台壳页可以一边展示概览，一边提供过渡入口。

影响范围：

- 配置型后台壳页面的可操作性
- 新后台壳与旧后台功能页之间的过渡路径
- 后台壳页头组件的通用能力

验证：

- 当前全量回归结果：`OK (159 tests, 532 assertions)`

下一步：

- 继续把“只读概览 -> 旧版操作入口”沉淀成更明确的过渡模式，再择机把首个操作型配置页面接入后台壳。

### 118. 落地首个后台壳操作型配置页面：发送测试邮件

摘要：

- 新增 [EmailTestSendService.php](/Users/apple/Documents/dujiaoshuka/app/Service/EmailTestSendService.php)，把邮件测试发送逻辑从 Dcat Widget 里抽成普通 Laravel 服务，并让旧版 [EmailTest.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Forms/EmailTest.php) 也开始复用这层服务。
- 新增 [EmailTestActionController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/AdminShell/EmailTestActionController.php) 与 [send.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin-shell/email-test/send.blade.php)，落地 `/admin/v2/email-test/send` 作为后台壳中的首个真实操作型配置页面。
- [app/Admin/routes.php](/Users/apple/Documents/dujiaoshuka/app/Admin/routes.php) 已接入新的 GET/POST 路由，后台壳现在不只是展示概览，还能直接承接测试邮件发送动作。

影响范围：

- 邮件测试发送逻辑的复用边界
- 后台壳从只读概览页走向可执行操作页
- 旧 Dcat 页面与新后台壳之间的过渡质量

验证：

- 当前全量回归结果：`OK (161 tests, 544 assertions)`

下一步：

- 继续沿着这条模式推进系统设置编辑入口或其他低风险操作型页面，让后台壳逐步替代旧配置中心。

### 119. 落地系统设置基础配置编辑入口样板

摘要：

- 新增 [SystemSettingActionController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/AdminShell/SystemSettingActionController.php) 与 [edit-base.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin-shell/system-setting/edit-base.blade.php)，把基础站点配置编辑入口接入后台壳。
- [app/Admin/routes.php](/Users/apple/Documents/dujiaoshuka/app/Admin/routes.php) 已新增 `/admin/v2/system-setting/base` 的 GET/POST 路由，后台壳现在不仅能展示系统设置概览，也能直接保存一组真实配置。
- [AdminShellSystemSettingPageService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellSystemSettingPageService.php) 已在概览页头补上“编辑基础站点配置”入口，把概览页和操作页直接接起来。

影响范围：

- 系统设置页的后台壳承接深度
- 配置型页面从只读概览到真实保存动作的过渡质量
- 后台壳对低风险操作型页面的支持能力

验证：

- 当前全量回归结果：`OK (165 tests, 553 assertions)`

下一步：

- 继续沿着这条模式推进更多系统设置分组或其他低风险配置动作，让后台壳进一步替代旧 Dcat 配置中心。

### 120. 落地系统设置邮件配置编辑入口样板

摘要：

- 在 [SystemSettingActionController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/AdminShell/SystemSettingActionController.php) 中新增 `editMail()` / `updateMail()`，把邮件配置编辑入口接入后台壳。
- 新增 [edit-mail.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin-shell/system-setting/edit-mail.blade.php)，承接 SMTP 驱动、主机、端口、账号、协议与发件身份等字段保存。
- [AdminShellSystemSettingPageService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellSystemSettingPageService.php) 已在系统设置概览页头补上“编辑邮件配置”入口，使概览页与第二个真实配置动作页直接连通。

影响范围：

- 系统设置邮件配置的后台壳承接深度
- 后台壳对多分组配置动作的支持能力
- 配置型页面过渡模式的可复制性

验证：

- 当前全量回归结果：`OK (165 tests, 562 assertions)`

下一步：

- 继续沿着系统设置分组推进低风险配置动作，或转向通知/推送配置编辑入口，进一步扩大后台壳承接范围。

### 121. 固化长周期整改总纲并切换为按批次连续执行

摘要：

- 重写 [rectification-execution-plan.md](/Users/apple/Documents/dujiaoshuka/docs/rectification-execution-plan.md)，把整改路线升级成“六大阶段 + 当前执行批次”的长周期执行总纲。
- 明确后台壳扩容、支付层收口、安装与配置现代化、安全治理、升级前清障之间的默认先后顺序。
- 明确后续执行默认按批次连续推进，只在高风险分叉、不可逆操作或外部信息缺失时暂停。

影响范围：

- 整体执行节奏
- 阶段优先级管理
- 文档化治理与低打扰协作方式

验证：

- 当前全量回归结果：`OK (165 tests, 562 assertions)`

下一步：

- 按执行总纲继续推进后台壳扩容，优先补更多系统设置分组和低风险操作型页面。

### 122. 落地系统设置通知推送配置编辑入口样板

摘要：

- 在 [SystemSettingActionController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/AdminShell/SystemSettingActionController.php) 中新增 `editPush()` / `updatePush()`，把通知推送配置编辑入口接入后台壳。
- 新增 [edit-push.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin-shell/system-setting/edit-push.blade.php)，承接 Server 酱、Telegram、Bark 与企业微信机器人推送相关字段保存。
- [AdminShellSystemSettingPageService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellSystemSettingPageService.php) 已在系统设置概览页头补上“编辑通知推送配置”入口，使概览页与第三个真实配置动作页直接连通。

影响范围：

- 系统设置通知推送分组的后台壳承接深度
- 后台壳对多分组配置动作的覆盖能力
- 配置型页面过渡模式的可复制性

验证：

- 当前全量回归结果：`OK (167 tests, 574 assertions)`

下一步：

- 继续沿着系统设置分组推进低风险配置动作，或转向更复杂的配置型/导入型后台壳页面，进一步扩大后台壳承接范围。

### 123. 落地首个后台壳导入型动作页：卡密导入

摘要：

- 新增 [CarmiImportActionController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/AdminShell/CarmiImportActionController.php) 与 [import.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin-shell/carmis/import.blade.php)，把卡密导入接入后台壳。
- [app/Admin/routes.php](/Users/apple/Documents/dujiaoshuka/app/Admin/routes.php) 已新增 `/admin/v2/carmis/import` 的 GET/POST 路由，后台壳现在不只支持配置动作页，也开始承接导入型业务动作。
- [AdminShellCarmisPageService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellCarmisPageService.php) 已在卡密概览页头补上“导入卡密”入口，使卡密列表与导入动作页直接连通。
- 新动作页复用了 [CarmiImportService.php](/Users/apple/Documents/dujiaoshuka/app/Service/CarmiImportService.php) 这条既有服务边界，避免把导入逻辑重新写回控制器。

影响范围：

- 后台壳对导入型页面的承接能力
- 卡密管理页与批量导入动作之间的过渡路径
- 旧 Dcat 导入页到新后台壳导入页的迁移可行性

验证：

- 当前全量回归结果：`OK (170 tests, 585 assertions)`

下一步：

- 继续推进后台壳中的中风险动作页，优先考虑更复杂的配置动作或导入/批量操作型页面。

### 124. 落地系统设置站点体验配置编辑入口样板

摘要：

- 在 [SystemSettingActionController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/AdminShell/SystemSettingActionController.php) 中新增 `editExperience()` / `updateExperience()`，把站点体验配置编辑入口接入后台壳。
- 新增 [edit-experience.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin-shell/system-setting/edit-experience.blade.php)，承接前台搜索密码、图形验证码、Google 翻译、防红、站点公告和页脚代码等低风险字段保存。
- [AdminShellSystemSettingPageService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellSystemSettingPageService.php) 已在系统设置概览页头补上“编辑站点体验配置”入口，使系统设置概览与第四个真实配置动作页直接连通。

影响范围：

- 系统设置前台行为分组的后台壳承接深度
- 后台壳对低风险展示型配置的覆盖能力
- 系统设置多分组迁移模式的完整度

验证：

- 当前全量回归结果：`OK (172 tests, 598 assertions)`

下一步：

- 继续沿着后台壳动作页路线推进更复杂一点的配置型或批量型页面，逐步扩大新后台壳的实际承载范围。

### 125. 落地邮件模板新建与编辑动作页样板

摘要：

- 新增 [EmailTemplateActionController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/AdminShell/EmailTemplateActionController.php) 与 [form.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin-shell/emailtpl/form.blade.php)，把邮件模板新建和编辑动作接入后台壳。
- 新增 [EmailTemplateActionService.php](/Users/apple/Documents/dujiaoshuka/app/Service/EmailTemplateActionService.php)，将模板创建与更新写入边界收口到普通服务层，而不是继续依赖旧 Dcat 表单壳。
- [AdminShellEmailTemplatePageService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellEmailTemplatePageService.php) 已在模板概览页头补上“新建邮件模板”入口，并在表格操作列补上“编辑模板”入口。
- [app/Admin/routes.php](/Users/apple/Documents/dujiaoshuka/app/Admin/routes.php) 已新增 `/admin/v2/emailtpl/create` 与 `/admin/v2/emailtpl/{id}/edit` 的 GET/POST 路由，后台壳开始承接标准业务表单页面。

影响范围：

- 后台壳对标准 CRUD 编辑页的承接能力
- 邮件模板管理从只读概览页走向真实业务编辑页
- 旧后台表单逻辑向普通服务层迁移的模式验证

验证：

- 当前全量回归结果：`OK (176 tests, 615 assertions)`

下一步：

- 继续沿着这条路径推进更多标准业务编辑页，或挑一张中风险批量动作页继续扩大后台壳的实际承载范围。

### 126. 落地优惠码新建与编辑动作页样板

摘要：

- 新增 [CouponActionController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/AdminShell/CouponActionController.php) 与 [form.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin-shell/coupon/form.blade.php)，把优惠码新建和编辑动作接入后台壳。
- 新增 [CouponActionService.php](/Users/apple/Documents/dujiaoshuka/app/Service/CouponActionService.php)，将优惠码创建、更新和关联商品同步收口到普通服务层。
- [AdminShellCouponPageService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellCouponPageService.php) 已在优惠码概览页头补上“新建优惠码”入口，并在表格操作列补上“编辑优惠码”入口。
- [app/Admin/routes.php](/Users/apple/Documents/dujiaoshuka/app/Admin/routes.php) 已新增 `/admin/v2/coupon/create` 与 `/admin/v2/coupon/{id}/edit` 的 GET/POST 路由，后台壳开始承接优惠码标准业务表单页面。

影响范围：

- 后台壳对带关联商品的标准业务表单页的承接能力
- 优惠码管理从只读概览页走向真实创建与编辑页
- 旧后台表单逻辑向普通服务层迁移的模式验证

验证：

- 当前全量回归结果：`OK (180 tests, 636 assertions)`

下一步：

- 继续沿着这条路线推进更多标准业务编辑页，或挑一张中风险批量动作页继续扩大后台壳的实际承载范围。

### 127. 落地商品分类新建与编辑动作页样板

摘要：

- 新增 [GoodsGroupActionController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/AdminShell/GoodsGroupActionController.php) 与 [form.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin-shell/goods-group/form.blade.php)，把商品分类新建和编辑动作接入后台壳。
- 新增 [GoodsGroupActionService.php](/Users/apple/Documents/dujiaoshuka/app/Service/GoodsGroupActionService.php)，将分类创建与更新写入边界收口到普通服务层。
- [AdminShellGoodsGroupPageService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellGoodsGroupPageService.php) 已在分类概览页头补上“新建商品分类”入口，并在表格操作列补上“编辑分类”入口。
- [app/Admin/routes.php](/Users/apple/Documents/dujiaoshuka/app/Admin/routes.php) 已新增 `/admin/v2/goods-group/create` 与 `/admin/v2/goods-group/{id}/edit` 的 GET/POST 路由，后台壳开始承接更基础的标准 CRUD 页面。

影响范围：

- 后台壳对基础 CRUD 编辑页的承接能力
- 商品分类管理从只读概览页走向真实创建与编辑页
- 低风险管理对象迁移到普通服务层的模式验证

验证：

- 当前全量回归结果：`OK (184 tests, 654 assertions)`

下一步：

- 继续沿着这条路线推进更多标准业务编辑页，或挑一张中风险批量动作页继续扩大后台壳的实际承载范围。

### 128. 落地支付通道新建与编辑动作页样板

摘要：

- 新增 [PayActionController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/AdminShell/PayActionController.php) 与 [form.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin-shell/pay/form.blade.php)，把支付通道新建和编辑动作接入后台壳。
- 新增 [PayActionService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PayActionService.php)，将支付通道创建与更新写入边界收口到普通服务层。
- [AdminShellPayPageService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellPayPageService.php) 已在支付通道概览页头补上“新建支付通道”入口，并在表格操作列补上“编辑通道”入口。
- [app/Admin/routes.php](/Users/apple/Documents/dujiaoshuka/app/Admin/routes.php) 已新增 `/admin/v2/pay/create` 与 `/admin/v2/pay/{id}/edit` 的 GET/POST 路由，后台壳开始承接更接近中风险的业务编辑页。

影响范围：

- 后台壳对支付通道标准业务表单页的承接能力
- 支付通道管理从只读概览页走向真实创建与编辑页
- 支付配置写入逻辑向普通服务层迁移的模式验证

验证：

- 当前全量回归结果：`OK (188 tests, 675 assertions)`

下一步：

- 继续沿着这条路线推进更多真实业务页，或挑一张中风险批量动作页继续扩大后台壳的实际承载范围。

### 129. 落地商品管理后台壳复杂资源样板页

摘要：

- 新增 [AdminShellGoodsPageService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellGoodsPageService.php) 与 [GoodsShellController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/AdminShell/GoodsShellController.php)，将商品管理接入后台壳。
- [AdminShellResourceRegistry.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellResourceRegistry.php) 已新增 `goods` 资源定义，因此路由、权限白名单和侧边导航会自动带上 `/admin/v2/goods`。
- 新页面承接了商品列表、筛选和详情展示合同，覆盖分类、商品类型、价格、库存、销量、关联优惠码以及多组配置文本字段，为后续迁移编辑和批量动作打下基础。
- 新增 [AdminShellGoodsControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Feature/AdminShellGoodsControllerTest.php)，并扩充了资源注册与页面结构测试，保证商品管理这张复杂资源页纳入后台壳基础设施。

影响范围：

- 后台壳对复杂资源列表页和详情页的承接能力
- 资源注册表、导航、权限白名单与页面结构测试覆盖范围
- 商品管理迁移从“待评估”推进到“已落地样板”

验证：

- 当前全量回归结果：`OK (192 tests, 701 assertions)`

下一步：

- 继续沿着这条路线推进商品管理的编辑页或批量动作页，逐步把复杂资源从只读样板推进到真实可操作页面。

### 130. 落地商品管理新建与编辑动作页样板

摘要：

- 新增 [GoodsActionController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/AdminShell/GoodsActionController.php) 与 [form.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin-shell/goods/form.blade.php)，把商品新建和编辑动作接入后台壳。
- 新增 [GoodsActionService.php](/Users/apple/Documents/dujiaoshuka/app/Service/GoodsActionService.php)，将商品基础信息、价格、库存、文本配置和关联优惠码写入边界收口到普通服务层。
- [AdminShellGoodsPageService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellGoodsPageService.php) 已在商品概览页头补上“新建商品”入口，并在表格操作列补上“编辑商品”入口。
- [app/Admin/routes.php](/Users/apple/Documents/dujiaoshuka/app/Admin/routes.php) 已新增 `/admin/v2/goods/create` 与 `/admin/v2/goods/{id}/edit` 的 GET/POST 路由，后台壳开始承接复杂资源的真实编辑动作。

影响范围：

- 后台壳对复杂资源标准业务编辑页的承接能力
- 商品管理从只读概览页走向真实创建与编辑页
- 商品基础写入逻辑向普通服务层迁移的模式验证

验证：

- 当前全量回归结果：`OK (196 tests, 731 assertions)`

下一步：

- 继续沿着这条路线推进更复杂的商品动作或批量场景，逐步扩大后台壳对复杂业务资源的实际承载范围。

### 131. 落地订单管理后台壳列表与详情样板页

摘要：

- 新增 [AdminShellOrderPageService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellOrderPageService.php) 与 [OrderShellController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/AdminShell/OrderShellController.php)，将订单管理接入后台壳。
- [AdminShellResourceRegistry.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellResourceRegistry.php) 已新增 `order` 资源定义，因此路由、权限白名单与侧边导航会自动带上 `/admin/v2/order`。
- 新页面承接了订单列表、筛选和详情展示合同，覆盖订单号、标题、类型、状态、商品、优惠码、支付通道、价格结构和附加信息等关键字段。
- 新增 [AdminShellOrderControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Feature/AdminShellOrderControllerTest.php)，并扩充资源注册与页面结构测试，保证订单管理这张高上下文业务页纳入后台壳基础设施。

影响范围：

- 后台壳对高上下文业务资源列表页和详情页的承接能力
- 资源注册表、导航、权限白名单与页面结构测试覆盖范围
- 订单管理迁移从“高风险待评估”推进到“已落地只读样板”

验证：

- 当前全量回归结果：`OK (200 tests, 759 assertions)`

下一步：

- 继续沿着这条路线推进订单管理的低风险动作或更多中复杂度后台壳页面，逐步扩大后台壳对主链业务页的承载范围。

### 132. 落地卡密管理新建与编辑动作页样板

摘要：

- 新增 [CarmiActionController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/AdminShell/CarmiActionController.php) 与 [form.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin-shell/carmis/form.blade.php)，把卡密新建和编辑动作接入后台壳。
- 新增 [CarmiActionService.php](/Users/apple/Documents/dujiaoshuka/app/Service/CarmiActionService.php)，将卡密的关联商品、销售状态、循环使用标记和卡密内容写入边界收口到普通服务层。
- [AdminShellCarmisPageService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellCarmisPageService.php) 已在卡密概览页头补上“新建卡密”入口，并在表格操作列补上“编辑卡密”入口；导入页与编辑页可并存工作。
- [app/Admin/routes.php](/Users/apple/Documents/dujiaoshuka/app/Admin/routes.php) 已新增 `/admin/v2/carmis/create` 与 `/admin/v2/carmis/{id}/edit` 的 GET/POST 路由，后台壳开始承接卡密这类中风险业务编辑页。

影响范围：

- 后台壳对库存履约侧标准业务编辑页的承接能力
- 卡密管理从只读概览页和导入页走向真实创建与编辑页
- 卡密写入逻辑向普通服务层迁移的模式验证

验证：

- 当前全量回归结果：`OK (204 tests, 776 assertions)`

下一步：

- 继续沿着这条路线推进更多中复杂度后台壳动作页，逐步扩大后台壳对真实业务页的实际承载范围。

### 133. 落地品牌与 Logo 配置后台壳动作页

摘要：

- 新增 [edit-branding.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin-shell/system-setting/edit-branding.blade.php)，把品牌与 Logo 配置接入后台壳。
- [SystemSettingActionController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/AdminShell/SystemSettingActionController.php) 已新增 `editBranding/updateBranding`，承接站点标题、文字 Logo、图片 Logo 路径、默认主题与默认语言的保存逻辑。
- [AdminShellSystemSettingPageService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellSystemSettingPageService.php) 已在系统设置概览页头补上“编辑品牌与 Logo 配置”入口，并在概览详情中增加图片 Logo 字段展示。
- [app/Admin/routes.php](/Users/apple/Documents/dujiaoshuka/app/Admin/routes.php) 已新增 `/admin/v2/system-setting/branding` 的 GET/POST 路由，后台壳继续扩大对真实配置动作页的承载范围。

影响范围：

- 后台壳对品牌与展示类配置动作页的承接能力
- 系统设置从基础站点/邮件/推送/体验继续扩展到品牌媒体配置
- 低风险展示配置向普通 Laravel 配置保存链路迁移的模式验证

验证：

- 当前全量回归结果：`OK (206 tests, 786 assertions)`

下一步：

- 继续沿着这条路线推进更多中复杂度后台壳动作页，或回到主链业务页上补更低风险的可操作入口。

### 134. 落地后台壳首页总览样板

摘要：

- 新增 [AdminShellDashboardPageService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellDashboardPageService.php) 与 [DashboardShellController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/AdminShell/DashboardShellController.php)，把后台首页总览接入后台壳。
- 新增 [index.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin-shell/dashboard/index.blade.php)，将已有统计服务组合成新的后台壳 dashboard 页面。
- [app/Admin/routes.php](/Users/apple/Documents/dujiaoshuka/app/Admin/routes.php) 已新增 `/admin/v2/dashboard` 路由，[config/admin.php](/Users/apple/Documents/dujiaoshuka/config/admin.php) 已补上对应权限白名单，后台壳首页可以直接访问。
- [layout.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin-shell/layout.blade.php) 已新增“后台总览”导航入口，后台壳开始承接首页级页面而不只是 CRUD 与配置页。

影响范围：

- 后台壳对首页总览与统计型页面的承接能力
- 旧 Dcat dashboard 继续降耦合
- 后台壳导航与权限例外范围扩展到非资源型页面

验证：

- 当前全量回归结果：`OK (208 tests, 796 assertions)`

下一步：

- 继续沿着这条路线推进更多中复杂度后台壳动作页，或开始把后台首页上的更多旧卡片逻辑继续往普通 Laravel 页面壳迁移。

### 135. 将后台默认首页切到后台壳总览

摘要：

- [HomeController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/HomeController.php) 已从旧 Dcat dashboard 构建逻辑切换为直接跳转到 `/admin/v2/dashboard`。
- 新增对 `/admin` 默认入口的 Feature 护栏，确保后台登录后的默认落点已经稳定切到后台壳首页。
- 这一步没有改变原有统计口径，只是把后台首页的默认承载层从旧 Dcat dashboard 切到了新的后台壳总览页。

影响范围：

- 后台默认首页落点
- 旧 Dcat dashboard 的继续退场
- 后台壳首页的真实使用优先级

验证：

- 当前全量回归结果：`OK (209 tests, 798 assertions)`

下一步：

- 继续沿着这条路线推进更多中复杂度后台壳动作页，或继续压缩旧 Dcat 在首页与高频页上的承载范围。

### 136. 完成当前基线审计并更新仓库说明

摘要：

- 新增 [current-baseline-audit.md](/Users/apple/Documents/dujiaoshuka/docs/current-baseline-audit.md)，集中盘点当前已完成进度、正在进行中的阶段、剩余重点工作与当前基线数字。
- [README.md](/Users/apple/Documents/dujiaoshuka/README.md) 已按当前真实状态重新更新，补齐后台壳进度、当前主线定位、测试基线和审计入口。
- 这次更新的重点不是新增功能，而是把“现在做到哪里、后面还剩多少”重新说清楚，避免仓库说明继续停留在旧阶段口径。

影响范围：

- 仓库首页说明口径
- 当前阶段认知与后续推进优先级
- 外部协作者理解项目现状的速度

验证：

- README 与审计文档已完成同步更新
- 当前主线最近一次完整回归结果仍为：`OK (209 tests, 798 assertions)`

下一步：

- 按审计结论继续推进后台壳扩容、旧 Dcat 降耦合和支付层收尾，不再依赖分散的阶段记忆推进主线。
- [AbstractAdminShellPageService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AbstractAdminShellPageService.php) 已移除后台壳默认的旧版回退入口派生逻辑，页头动作现在只保留新后台壳自身需要的入口。
- [AdminShellResourceRegistry.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellResourceRegistry.php) 已删除 `system-setting` 与 `email-test` 的 `legacy_uri` 兼容定义，后台壳不再默认向旧 Dcat 功能页回退。
- [AdminShellDashboardPageService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellDashboardPageService.php) 与多张后台壳动作页已去掉“进入旧版首页/功能页”按钮，后台替换方向正式切换为单向收缩旧版。
- 新增 [OrderActionController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/AdminShell/OrderActionController.php)、[OrderActionService.php](/Users/apple/Documents/dujiaoshuka/app/Service/OrderActionService.php) 和 [order/form.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin-shell/order/form.blade.php)，把订单编辑页正式接进后台壳。
- [AdminShellOrderPageService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellOrderPageService.php) 已为订单列表与详情页补上“编辑订单”动作入口，后台壳里的订单资源从只读样板推进为低风险可操作页面。
- [AdminShellOrderControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Feature/AdminShellOrderControllerTest.php) 与 [AdminShellPageStructureTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AdminShellPageStructureTest.php) 已补上订单编辑页护栏，当前只承接旧后台原本就允许修改的低风险字段。

### 137. 创建首个预发布版本基线说明

摘要：

- 新增 [v3.0.0-alpha.1.md](/Users/apple/Documents/dujiaoshuka/docs/releases/v3.0.0-alpha.1.md)，正式整理首个现代化重构预发布版本的范围、定位、已完成成果与未完成项。
- 该版本明确标记为 `alpha`，用于集中验证当前主线的安装链、后台壳、支付层收口与测试基线，不作为稳定正式版。
- 这一步的目的不是新增功能，而是给当前阶段一个可对外引用、可回看、可继续迭代的版本锚点。

影响范围：

- 版本管理与阶段性对外沟通
- 后续 `beta` / 正式版的比较基线
- GitHub Release 与仓库文档的一致性

验证：

- 当前主线完整回归结果：`OK (211 tests, 812 assertions)`

下一步：

- 继续沿着当前总纲推进后台壳扩容、旧 Dcat 降耦合、支付层收口与升级前清障。

### 138. 将旧后台高频入口切到后台壳

摘要：

- 新增 [LegacyAdminShellRedirectService.php](/Users/apple/Documents/dujiaoshuka/app/Service/LegacyAdminShellRedirectService.php)，集中承接旧 Dcat 后台入口到新后台壳的跳转逻辑，并保留列表页查询参数。
- `GoodsGroup`、`Emailtpl`、`Pay`、`Coupon`、`Goods`、`Carmis`、`Order` 这些已具备后台壳浏览或编辑能力的旧控制器，现已把 `index/show/create/edit` 中的用户入口切到 `/admin/v2/*`。
- `SystemSettingController` 和 `EmailTestController` 也已切到后台壳配置页入口，旧 Dcat 页面不再作为默认承载层。

影响范围：

- 旧后台高频浏览入口
- 后台壳成为日常使用主路径的覆盖面
- 旧 Dcat 继续从主承载层退化为兼容壳

验证：

- 新增 [LegacyAdminShellRedirectControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Feature/LegacyAdminShellRedirectControllerTest.php) 覆盖旧地址到后台壳的跳转行为

下一步：

- 继续沿着这条路线推进更多后台壳动作页，并逐步缩小旧 Dcat 对高频 CRUD 的实际使用范围。

### 139. 打磨后台壳共享视觉层

摘要：

- [layout.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin-shell/layout.blade.php) 已改成加载独立静态样式，不再把整套后台壳视觉规则内嵌在模板里。
- 新增 [admin-shell.css](/Users/apple/Documents/dujiaoshuka/public/assets/avatar/css/admin-shell.css)，统一承载侧边栏、页头、卡片、筛选区、表格、按钮和移动端响应式样式。
- [page-header.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin-shell/partials/page-header.blade.php)、[filter-panel.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin-shell/partials/filter-panel.blade.php)、[data-table.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin-shell/partials/data-table.blade.php) 也已同步微调，使共享壳层在不同后台壳页面上的表现更统一。

影响范围：

- 后台壳共享视觉底座
- 不同资源页之间的视觉一致性
- 后续继续迁移后台页时的 UI 维护成本

验证：

- 当前主线完整回归结果：`OK (217 tests, 853 assertions)`

下一步：

- 继续沿着后台壳扩容主线推进更多真实页面，同时保持共享壳层的统一视觉语言。

### 139. 新增订单行为配置页

摘要：

- 在 [SystemSettingActionController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/AdminShell/SystemSettingActionController.php) 中新增订单行为配置动作页，使用现有 `order_expire_time`、`is_open_img_code` 和 `is_open_search_pwd` 作为页面承接字段。
- 在 [AdminShellSystemSettingPageService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellSystemSettingPageService.php) 的系统设置概览中新增订单行为分组，并为概览页补充“编辑订单行为配置”入口。
- 新增 [resources/views/admin-shell/system-setting/edit-order.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin-shell/system-setting/edit-order.blade.php)，将订单过期时间和订单查询相关开关从配置概览中拆出，作为独立动作页管理。

影响范围：

- 系统设置概览页
- 订单过期时间与查询行为配置
- 后台壳对低风险订单行为配置页的承载能力

验证：

- 新增 Feature 测试覆盖订单行为配置页的展示与保存
- 当前主线完整回归结果：`OK (216 tests, 849 assertions)`

下一步：

- 继续沿着后台壳扩容和旧 Dcat 降耦合两条线往下推，优先处理更多中低风险配置页与高频管理页。

### 140. 压缩旧入口重定向

摘要：

- [HomeController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/HomeController.php) 现在通过 [LegacyAdminShellRedirectService.php](/Users/apple/Documents/dujiaoshuka/app/Service/LegacyAdminShellRedirectService.php) 显式跳转到后台壳首页，`/admin` 不再保留旧 Dcat 主页承载语义。
- [SystemSettingController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/SystemSettingController.php) 与 [EmailTestController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/EmailTestController.php) 也切换到语义化的重定向入口方法，减少旧后台控制器里的路径散落。
- [tests/Feature/LegacyAdminShellRedirectControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Feature/LegacyAdminShellRedirectControllerTest.php) 新增对 `/admin` 以及系统设置、邮件测试旧入口查询串保留的兼容护栏，确保旧入口只承担跳转，不再承担业务展示。

影响范围：

- 旧 Dcat 后台首页与配置页入口压缩
- 兼容重定向语义集中到单一服务
- 后台壳作为唯一实际承载面的倾向进一步加强

验证：

- `./scripts/php74 vendor/bin/phpunit` 通过，结果为 `OK (219 tests, 900 assertions)`

下一步：

- 继续沿着后台壳扩容和旧 Dcat 降耦合两条线往下推，优先处理更多中低风险配置页与高频管理页。

### 141. 后台登录入口退壳

摘要：

- [AuthController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/AuthController.php) 不再直接使用 Dcat 默认登录页，而是接管 `auth/login` 与 `auth/logout`，改成普通 Laravel 表单登录流和标准重定向流程。
- 新增 [resources/views/admin-shell/auth/login.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin-shell/auth/login.blade.php)，把后台登录入口切到后台壳视觉体系，登录后默认继续进入新后台壳首页。
- [admin-shell.css](/Users/apple/Documents/dujiaoshuka/public/assets/avatar/css/admin-shell.css) 增加登录页专用样式，统一品牌、壳层视觉和移动端表现。
- [tests/Browser/admin-shell-smoke.sh](/Users/apple/Documents/dujiaoshuka/tests/Browser/admin-shell-smoke.sh) 兼容新的重定向式登录结果，不再假定旧 Dcat 风格的 JSON 成功载荷。
- 新增 [AdminAuthShellLoginTest.php](/Users/apple/Documents/dujiaoshuka/tests/Feature/AdminAuthShellLoginTest.php) 保护登录页展示、成功登录和失败登录路径。

影响范围：

- `/admin/auth/login` 与 `/admin/auth/logout` 主入口
- 后台壳与后台登录体验的一致性
- 旧 Dcat 默认登录页实际承载面的进一步收缩
- 本地后台烟雾检查脚本

验证：

- `./scripts/php74 vendor/bin/phpunit tests/Feature/AdminAuthShellLoginTest.php` 通过
- `./scripts/smoke-admin-shell` 通过
- `./scripts/php74 vendor/bin/phpunit` 通过，结果为 `OK (223 tests, 973 assertions)`

下一步：

- 继续沿着后台壳扩容和旧 Dcat 降耦合主线推进，优先处理剩余高频后台页和更复杂的操作型页面。

### 143. 后台壳动作路由收口

摘要：

- [AdminShellResourceRegistry.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellResourceRegistry.php) 为后台壳资源补充动作路由声明，集中定义 create / store / edit / update / import / send / system-setting 分组动作。
- [AdminShellRouteRegistrar.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellRouteRegistrar.php) 现在统一读取注册表并挂载动作路由，继续保留现有 `/admin/v2/*` URL，不改外部入口。
- [app/Admin/routes.php](/Users/apple/Documents/dujiaoshuka/app/Admin/routes.php) 删除了大段手写的 `admin/v2` 动作路由，只保留资源壳、dashboard 和少量旧入口兼容路由。
- [AdminShellRouteRegistrarTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AdminShellRouteRegistrarTest.php) 新增对关键动作路由的注册断言，覆盖商品、邮件模板、支付、卡密导入、系统设置和邮件测试等入口。

影响范围：

- 后台壳动作路由集中声明
- `app/Admin/routes.php` 的手写路由显著收口
- 现有 `admin/v2` URL 保持不变
- 后续新增后台壳动作页时只需补注册表与控制器，不再散落路由定义

验证：

- `./scripts/php74 vendor/bin/phpunit tests/Unit/AdminShellRouteRegistrarTest.php` 通过
- `./scripts/php74 vendor/bin/phpunit` 通过后补充

下一步：

- 继续沿着后台壳扩容和旧 Dcat 降耦合主线推进，优先处理剩余高频后台页和更复杂的操作型页面。

### 143. 系统设置概览面板化

摘要：

- [AdminShellSystemSettingPageService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellSystemSettingPageService.php) 重新整理了系统设置概览的分组结构，新增 `品牌与展示配置` 分组，并把索引页的定位从“只读列表”改成“配置导航面板”。
- 概览页的每个分组都增加了明确的入口动作，行内现在可以直接进入对应配置页，避免用户在详情页和编辑页之间多绕一层。
- [tests/Feature/AdminShellSystemSettingControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Feature/AdminShellSystemSettingControllerTest.php) 新增对品牌分组、导航面板标题和直达入口文案的护栏，确保概览页不会回退成普通列表视图。
- [AdminShellResourceRegistry.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminShellResourceRegistry.php) 同步修正了系统设置和全量后台壳动作路由的控制器接线，避免 shell controller 误接 create/edit/update 请求导致 500。

影响范围：

- 系统设置概览页从“只读列表”升级为“配置导航面板”
- 品牌 / 基础 / 邮件 / 订单 / 通知 / 体验六个配置入口更清晰
- 便于后续继续扩展系统设置的更多分组或动作页

验证：

- `./scripts/php74 vendor/bin/phpunit tests/Feature/AdminShellSystemSettingControllerTest.php tests/Feature/AdminShellEmailTestControllerTest.php` 通过
- `./scripts/php74 vendor/bin/phpunit` 通过，结果为 `OK (230 tests, 1029 assertions)`

下一步：

- 继续沿着后台壳扩容和旧 Dcat 降耦合主线推进，优先处理剩余高频后台页和更复杂的操作型页面。

### 142. 后台账号设置页退壳

摘要：

- [AuthController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/AuthController.php) 继续接管 `auth/setting`，把个人资料维护从旧 Dcat 表单页迁到后台壳。
- 新增 [AdminAccountSettingService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminAccountSettingService.php)，统一处理管理员昵称、头像上传与密码修改，并保留旧密码校验约束。
- 新增 [resources/views/admin-shell/auth/setting.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin-shell/auth/setting.blade.php)，后台账号设置页现在已经纳入后台壳视觉与交互体系。
- [layout.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/admin-shell/layout.blade.php) 和 [admin-shell.css](/Users/apple/Documents/dujiaoshuka/public/assets/avatar/css/admin-shell.css) 新增账号设置与退出登录入口，让后台高频个人操作留在新壳内完成。
- 新增 [AdminAuthShellSettingTest.php](/Users/apple/Documents/dujiaoshuka/tests/Feature/AdminAuthShellSettingTest.php)，覆盖设置页展示、昵称与头像更新、密码修改成功与旧密码错误拦截。

影响范围：

- `/admin/auth/setting` 个人维护入口
- 后台壳登录后的高频个人操作闭环
- 旧 Dcat 默认个人设置页承载面的进一步收缩
- 后台壳侧边栏尾部导航

验证：

- `./scripts/php74 vendor/bin/phpunit tests/Feature/AdminAuthShellLoginTest.php` 通过
- `./scripts/php74 vendor/bin/phpunit tests/Feature/AdminAuthShellSettingTest.php` 通过
- `./scripts/php74 vendor/bin/phpunit` 通过，结果为 `OK (227 tests, 991 assertions)`

下一步：

- 继续沿着后台壳扩容和旧 Dcat 降耦合主线推进，优先处理剩余高频后台页和更复杂的操作型页面。

### 143. 后台认证控制器迁出 app/Admin

摘要：

- 新增 [AuthShellController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/AdminShell/AuthShellController.php)，把后台登录、退出和账号设置入口彻底迁到普通 HTTP 控制器层。
- [config/admin.php](/Users/apple/Documents/dujiaoshuka/config/admin.php) 的 `auth.controller` 已改为指向新的后台壳认证控制器。
- 原 [app/Admin/Controllers/AuthController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/AuthController.php) 已删除，旧 Dcat 命名空间下不再承载后台认证主入口。

影响范围：

- `/admin/auth/login`
- `/admin/auth/logout`
- `/admin/auth/setting`
- 后台认证入口与新后台壳的边界清晰度

验证：

- `./scripts/php74 vendor/bin/phpunit tests/Feature/AdminAuthShellLoginTest.php` 通过
- `./scripts/php74 vendor/bin/phpunit tests/Feature/AdminAuthShellSettingTest.php` 通过
- `./scripts/php74 vendor/bin/phpunit` 通过，结果为 `OK (230 tests, 1047 assertions)`

下一步：

- 继续沿着后台壳扩容和旧 Dcat 降耦合主线推进，把剩余旧资源控制器进一步压缩为兼容跳转层。
