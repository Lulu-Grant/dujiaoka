# 当前基线审计

更新时间：2026-04-15

## 审计结论

当前仓库已经从“停更遗留项目抢救”推进到了“有稳定基线、持续滚动交付中的现代化改造项目”阶段。

现在最准确的判断是：

- 结构化治理后期
- 后台替换中后期
- 升级前清障中前期

如果只用一句话概括：

- 这已经不是一个“修到能跑”的遗留分叉，而是一条有测试、有 CI、有后台替换主线、有升级路线意识的持续维护分支。

## 当前总进度判断

按整轮现代化改造来估算，当前总进度约为：

- `68%`

拆开看大致是：

- 运行时、测试、CI、安装现代化：`90%+`
- 前台主题与品牌统一：`85%+`
- 订单域 / 支付域第一轮重构：`70%`
- 后台壳替换：`75%`
- 旧 Dcat 退场：`70%`
- 安全治理专项：`25%`
- PHP / Laravel 升级前清障：`35%`

## 当前基线数字

- PHPUnit：`OK (282 tests, 1490 assertions)`
- 当前分支：`master`
- 当前后台默认落点：`/admin -> /admin/v2/dashboard`
- 当前后台主入口：
  - `/admin/auth/login`
  - `/admin/auth/setting`
  - `/admin/v2/*`

## 已完成主轴

### 1. 运行时与测试基线

已完成：

- 恢复 PHP 7.4 遗留运行时基线
- 固定 `scripts/php74` / `scripts/composer74` 的可移植运行方式
- 建立 GitHub Actions `CI`
- 建立本地快速拉站路径
- 建立测试库准备脚本 [prepare-test-db](/Users/apple/Documents/dujiaoshuka/scripts/prepare-test-db)
- 让 CI 与测试库准备彻底脱离 `install.sql`

影响：

- 当前重构已经不再依赖“人工点点看”
- 本地、CI、测试库准备口径已经统一

### 2. 订单与支付主链重构

已完成：

- 订单创建、查询、支付完成、履约、通知拆成独立服务
- 订单创建切到 DTO / 应用服务模式
- 支付入口、支付回调、第三方 SDK 访问压缩到更清晰的服务边界
- 退役 `Paysapi`、`Vpay`、`PayJS`
- `Stripe` 已稳定到现代 SDK 基线
- `PayPal` / `Stripe` 已经过一轮边界收口

影响：

- 订单域已脱离“大控制器 + 大服务”混杂模式
- 支付层具备继续收口和后续升级的基础

### 3. 去守护进程与部署模型收口

已完成：

- 订单过期不再依赖延迟 worker
- 通知与副作用默认改为同步优先、异步可选
- Docker / compose / Debian 文档已移除对 `supervisord` / 常驻 `queue:work` 的硬依赖

影响：

- 部署和排障复杂度明显下降
- 容器与环境统一成本更低

### 4. 安装与数据模型现代化

已完成：

- 主安装路径切到 `migrate + bootstrap seed + 显式管理员创建`
- migration / seed 分层建立完成
- bootstrap / sample / forbidden 策略形成
- `install.sql` 已退出安装主路径、退出 CI，并已从仓库删除

影响：

- 新环境不再依赖历史 SQL 安装
- 测试、安装、仓库卫生终于统一到同一条现代路径

### 5. 前台与品牌统一

已完成：

- 新默认主题切到 `avatar`
- 旧主题已移除，当前走单主题模式
- 品牌统一为“独角数卡西瓜版”
- README、前台、后台、安装页和默认通知品牌口径已统一

影响：

- 前台改造成本继续下降
- 品牌识别与仓库入口保持一致

### 6. 后台壳进入真实主承载阶段

已完成底座：

- 通用后台壳模板
- 页面配置对象
- 通用控制器基类
- 页面服务协议
- 资源注册表
- 路由注册器
- 权限白名单派生
- 导航派生

已落地后台壳资源：

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

已落地后台壳动作页：

- 商品分类：`create / edit`
- 商品：`create / edit / clone / batch-status / batch-buy-limit-num / batch-group / export`
- 订单：`edit / reset search password / batch-status / batch-reset-search-pwd / export`
- 邮件模板：`create / edit / preview / copy / export summary`
- 支付通道：`create / edit / copy / batch-status / batch-client / export`
- 优惠码：`create / edit / batch generate / batch-status / batch-ret / export`
- 卡密：`create / edit / import / export / batch-loop`
- 邮件测试：`send`
- 系统设置：`base / branding / mail / order / push / experience`

影响：

- `/admin` 默认已进入后台壳 dashboard
- 后台登录、退出和账号设置都已进入后台壳
- 新后台壳已经不是样板，而是实际主承载面

### 7. 旧后台目录退场

已完成：

- `app/Admin` 目录已从仓库退场
- Dcat 兼容层只剩：
  - [config/admin.php](/Users/apple/Documents/dujiaoshuka/config/admin.php)
  - [routes/admin/routes.php](/Users/apple/Documents/dujiaoshuka/routes/admin/routes.php)

影响：

- 仓库结构已经明显更贴近当前真实运行时
- 后台替换不再被历史目录结构绑架

## 当前仍在进行中的主轴

### A. 后台壳继续扩容

当前状态：

- 进行中，而且仍然是最值得持续投入的主线

还没完成的点：

- `goods / order / pay / coupon / carmis` 的更多低风险批量动作
- 后台壳统一批量操作能力再收一轮
- 更多中复杂度业务页与高频操作承载

### B. 旧 Dcat 继续降耦合

当前状态：

- 已压掉目录和大量旧壳，但还没有完全收尾

还没完成的点：

- `config/admin.php` 中仍保留 Dcat 兼容配置
- `routes/admin/routes.php` 仍承担旧 `/admin/*` 兼容跳转
- 少量旧后台运行时依赖还没彻底移出主链

### C. 支付层现代化收口

当前状态：

- 已完成第一大轮，且进入可维护状态

还没完成的点：

- PayPal 最终保留 / 退场策略仍需继续收口
- Stripe 虽然已升级，但仍要继续按最佳实践整理
- 保留通道的配置模型与操作面仍可继续统一

### D. 安全治理与升级前清障

当前状态：

- 已经有清单，但还没进入真正主阶段

还没完成的点：

- 默认项治理
- 支付回调安全专项
- 后台安全边界盘点
- PHP / Laravel 升级前残余阻塞清理

## 当前剩余重点进度

### 第一优先级

1. 后台壳继续扩容
2. 继续压缩旧 Dcat 高频页和兼容层承载
3. 将后台壳从“能看、能配、能编辑”继续推进到“能承接更多真实操作和批量动作”

### 第二优先级

1. 支付层剩余保留通道收口
2. PayPal 生命周期与退场边界整理
3. Stripe 后续保持策略与接口面继续稳定

### 第三优先级

1. 安全治理专项盘点
2. PHP / Laravel 升级前清障
3. 后台替换与升级路线并轨

## 当前不建议立即做的事

### 1. 不建议立刻一刀切替换全部后台

原因：

- 现在后台壳方向是对的，但仍处于持续扩容阶段
- 直接整体替换的风险仍高于滚动迁移

### 2. 不建议立刻跳升 Laravel 大版本

原因：

- 后台替换和支付收口还没完全结束
- 现在最值钱的是继续降低升级阻塞，而不是急着冲版本号

## 当前主线判断

如果把项目分成几个成熟度阶段：

- 已脱离“只能救火”
- 已进入“结构化治理”
- 正在逼近“现代化收口”

当前最准确的位置是：

- “结构化治理后期，后台替换中后期，升级前清障中前期”

## 推荐下一步

建议后续默认顺序：

1. 继续后台壳扩容
2. 继续压缩旧 Dcat 最小兼容层
3. 回到支付层收尾
4. 启动安全专项
5. 再进入 Laravel / PHP 升级前清障
