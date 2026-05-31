# 当前进度总汇

更新时间：2026-05-31

## 一句话总览

当前仓库已经从“停更遗留项目抢救”推进到了“有稳定基线、后台替换主线、安装现代化完成、升级前清障在路上的持续维护项目”。

## 当前数字

- 总体进度估算：`90%`
- PHPUnit：`OK (407 tests, 4200 assertions)`
- 当前主分支：`master`
- 当前默认后台落点：`/admin -> /admin/v2/dashboard`
- 当前后台主入口：
  - `/admin/auth/login`
  - `/admin/auth/setting`
  - `/admin/v2/*`

## 已完成的大阶段

### 1. 遗留基线恢复

- PHP 7.4 运行时基线恢复
- `scripts/php74` / `scripts/composer74` 做到可移植
- 本地快速拉站路径建立
- GitHub Actions `CI` 建立

### 2. 测试与验证护栏建立

- 订单、支付、安装、后台壳相关 PHPUnit 持续补齐
- 测试库准备统一收口到 [prepare-test-db](/Users/apple/Documents/dujiaoshuka/scripts/prepare-test-db)
- CI 和测试库准备已经脱离 `install.sql`

### 3. 安装现代化收口

- 主安装路径切到 `migrate + bootstrap seed + 显式管理员创建`
- `install.sql` 已退出安装主路径
- `install.sql` 已退出 CI
- `install.sql` 已从仓库删除
- 默认账号、默认密钥和安装默认值已完成复查并固化到 [安全基线审计](/Users/apple/Documents/dujiaoshuka/docs/security-baseline-audit.md)

### 4. 订单与支付第一轮重构

- 订单创建、查询、支付完成、履约、通知边界拆开
- 多类支付回调和支付入口服务化
- 当前维护支付范围收敛为官方支付宝、官方微信、易支付和 Epusdt
- PayPal、Stripe、Coinbase、Mapay、TokenPay、Paysapi、Vpay、PayJS 已进入退役范围

### 5. 前台与品牌统一

- 默认前台主题切到 `avatar`
- 旧主题已清理
- 品牌统一为“独角数卡西瓜版”
- README、前台、后台、安装页和通知口径统一

### 6. 后台壳从样板走到主承载

已完成底座：

- 通用后台壳模板
- 页面配置对象
- 页面服务协议
- 控制器基类
- 资源注册表
- 路由注册器
- 权限白名单派生
- 导航派生

已完成真实资源页：

- `/admin/auth/login`
- `/admin/auth/setting`
- `/admin/v2/dashboard`
- `/admin/v2/goods-group`
- `/admin/v2/goods`
- `/admin/v2/order`
- `/admin/v2/emailtpl`
- `/admin/v2/pay`
- `/admin/v2/coupon`
- `/admin/v2/carmis`
- `/admin/v2/system-setting`
- `/admin/v2/email-test`

已完成真实动作页：

- 商品分类：`create / edit`
- 商品：`create / edit / clone / batch-status / batch-buy-limit-num / batch-group / batch-sales-volume / batch-ord / batch-buy-prompt / batch-buy-prompt-trim / batch-description / batch-description-trim / batch-keywords / batch-keywords-suffix / batch-keywords-trim / batch-keywords-collapse-spaces / export`
- 订单：`edit / reset search password / batch-status / batch-type / batch-info / batch-title / batch-title-prefix / batch-title-suffix / batch-title-trim / batch-title-collapse-spaces / batch-reset-search-pwd / export`
- 邮件模板：`create / edit / preview / copy / export summary`
- 支付通道：`create / edit / copy / batch-status / batch-client / batch-method / batch-name / batch-name-prefix / batch-name-suffix / batch-name-replace / batch-name-trim / batch-name-collapse-spaces / export`
- 优惠码：`create / edit / batch generate / batch-status / batch-use / batch-discount / batch-ret / batch-code / batch-code-prefix / batch-code-suffix / batch-code-replace / batch-code-trim / batch-code-collapse-spaces / export`
- 卡密：`create / edit / import / export / batch-loop / batch-trim / batch-collapse-spaces / batch-replace / batch-suffix`
- 邮件测试：`send`
- 系统设置：`base / branding / mail / order / push / experience`

### 7. 旧后台收缩

- `app/Admin` 目录已退场
- 旧后台兼容层只剩：
  - [config/admin.php](/Users/apple/Documents/dujiaoshuka/config/admin.php)
  - [routes/admin/routes.php](/Users/apple/Documents/dujiaoshuka/routes/admin/routes.php)
- 旧 `/admin/*` 入口已复查为兼容跳转清单，不再承载业务逻辑；`LegacyAdminShellRedirectService` 负责保留 query string 并跳转到 `/admin/v2/*`
- Dcat 最小兼容层职责、旧入口清单和删除条件已整理到 [Dcat 最小兼容层审计](docs/dcat-compatibility-layer-audit.md)

## 当前主线在哪里

当前最准确的阶段判断：

- 结构化治理后期
- 后台替换 RC 冻结期
- 升级前清障 stable-ready 前置期

## 当前正在做什么

当前主线仍然是：

1. 维护 RC.1 / stable-ready 文档、测试数字和冻结边界
2. 旧 Dcat 继续保持最小兼容层，不在稳定版前删除关键入口
3. 支付层只维护保留通道回调安全和退役通道防回流
4. 安全专项、CI 护栏和升级阻塞矩阵保持可复核
5. 后续 Laravel / PHP 升级只在单独实验分支推进

而且当前最值得持续投入的子方向依然是：

- 旧 Dcat 最小兼容层的测试护栏维护，以及 `goods / order / pay / coupon / carmis` 已有动作的字段边界、smoke 覆盖和 release 文档一致性

## 还剩哪些大块工作

### 第一优先级

- 后台壳冻结维护
- 继续维护旧 Dcat 最小兼容层测试和文档边界
- 保持新后台壳作为主承载，不再以新增批量动作为默认推进方向

### 第二优先级

- 支付保留通道继续收口
- 退役通道保持不可新增、不可恢复、不可进入样例种子
- 保留通道继续补回调异常路径护栏

### 第三优先级

- 安全治理专项真正启动
- PHP / Laravel 升级前阻塞继续盘清
- 后台替换和升级路线并轨

## 当前不建议做的事

- 不建议立刻一刀切替换全部后台
- 不建议立刻硬跳 Laravel 大版本
- 不建议在后台壳主线未收稳前大面积开新战线

## 当前文档入口

- [当前基线审计](docs/current-baseline-audit.md)
- [执行基线](docs/execution-baseline.md)
- [重构升级日志](docs/refactor-upgrade-log.md)
- [大整改执行方案](docs/rectification-execution-plan.md)
- [现代化路线图](docs/modernization-roadmap.md)
