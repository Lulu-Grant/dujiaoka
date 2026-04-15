# 当前进度总汇

更新时间：2026-04-15

## 一句话总览

当前仓库已经从“停更遗留项目抢救”推进到了“有稳定基线、后台替换主线、安装现代化完成、升级前清障在路上的持续维护项目”。

## 当前数字

- 总体进度估算：`68%`
- PHPUnit：`OK (284 tests, 1505 assertions)`
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

### 4. 订单与支付第一轮重构

- 订单创建、查询、支付完成、履约、通知边界拆开
- 多类支付回调和支付入口服务化
- 退役 `Paysapi`、`Vpay`、`PayJS`
- Stripe 已升级并继续稳定在现代 SDK 基线

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
- 商品：`create / edit / clone / batch-status / batch-buy-limit-num / batch-group / export`
- 订单：`edit / reset search password / batch-status / batch-reset-search-pwd / export`
- 邮件模板：`create / edit / preview / copy / export summary`
- 支付通道：`create / edit / copy / batch-status / batch-client / batch-method / export`
- 优惠码：`create / edit / batch generate / batch-status / batch-ret / export`
- 卡密：`create / edit / import / export / batch-loop`
- 邮件测试：`send`
- 系统设置：`base / branding / mail / order / push / experience`

### 7. 旧后台收缩

- `app/Admin` 目录已退场
- 旧后台兼容层只剩：
  - [config/admin.php](/Users/apple/Documents/dujiaoshuka/config/admin.php)
  - [routes/admin/routes.php](/Users/apple/Documents/dujiaoshuka/routes/admin/routes.php)

## 当前主线在哪里

当前最准确的阶段判断：

- 结构化治理后期
- 后台替换中后期
- 升级前清障中前期

## 当前正在做什么

当前主线仍然是：

1. 后台壳继续扩容
2. 旧 Dcat 继续降耦合
3. 支付层继续收口
4. 再进入安全专项与升级前清障

而且当前最值得持续投入的子方向依然是：

- `goods / order / pay / coupon / carmis` 这些高频资源的低风险批量动作

## 还剩哪些大块工作

### 第一优先级

- 后台壳继续扩大真实操作承载
- 继续压缩旧 Dcat 高频页和兼容层
- 让新后台壳从“主入口”继续推到“主承载”

### 第二优先级

- 支付保留通道继续收口
- PayPal 生命周期与退场边界继续整理
- Stripe 后续保持策略继续稳定

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
- [重构升级日志](docs/refactor-upgrade-log.md)
- [大整改执行方案](docs/rectification-execution-plan.md)
- [现代化路线图](docs/modernization-roadmap.md)
