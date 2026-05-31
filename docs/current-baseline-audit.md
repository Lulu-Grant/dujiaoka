# 当前基线审计

更新时间：2026-05-31

## 审计结论

当前仓库已经从“停更遗留项目抢救”推进到了“有稳定基线、持续滚动交付中的现代化改造项目”阶段。

现在最准确的判断是：

- 结构化治理后期
- 后台替换进入 RC 冻结期
- 升级前清障进入 stable-ready 前置期

如果只用一句话概括：

- 这已经不是一个“修到能跑”的遗留分叉，而是一条有测试、有 CI、有后台替换主线、有升级路线意识的持续维护分支。

## 当前总进度判断

按整轮现代化改造来估算，当前总进度约为：

- `90%`

拆开看大致是：

- 运行时、测试、CI、安装现代化：`90%+`
- 前台主题与品牌统一：`85%+`
- 订单域 / 支付域第一轮重构：`70%`
- 后台壳替换：`90%`
- 旧 Dcat 退场：`70%`
- 安全治理专项：`70%`
- PHP / Laravel 升级前清障：`65%`

## 当前基线数字

- PHPUnit：`OK (406 tests, 2689 assertions)`
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
- 当前维护支付范围收敛为官方支付宝、官方微信、易支付和 Epusdt
- `PayPal`、`Stripe`、`Coinbase`、`Mapay`、`TokenPay`、`Paysapi`、`Vpay`、`PayJS` 已进入退役范围
- 支付样例种子和主线路由已停止灌入退役通道

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
- 默认账号、默认密钥和安装默认值已形成 [安全基线审计](/Users/apple/Documents/dujiaoshuka/docs/security-baseline-audit.md)
- 安装页不再预填可预测管理员账号，首个管理员必须显式填写

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
- 商品：`create / edit / clone / batch-status / batch-buy-limit-num / batch-group / batch-sales-volume / batch-ord / batch-buy-prompt / batch-buy-prompt-trim / batch-description / batch-description-trim / batch-keywords / batch-keywords-suffix / batch-keywords-trim / batch-keywords-collapse-spaces / export`
- 订单：`edit / reset search password / batch-status / batch-type / batch-info / batch-title / batch-title-prefix / batch-title-suffix / batch-title-trim / batch-title-collapse-spaces / batch-reset-search-pwd / export`
- 邮件模板：`create / edit / preview / copy / export summary`
- 支付通道：`create / edit / copy / batch-status / batch-client / batch-method / batch-name / batch-name-prefix / batch-name-suffix / batch-name-replace / batch-name-trim / batch-name-collapse-spaces / export`
- 优惠码：`create / edit / batch generate / batch-status / batch-use / batch-discount / batch-ret / batch-code / batch-code-prefix / batch-code-suffix / batch-code-replace / batch-code-trim / batch-code-collapse-spaces / export`
- 卡密：`create / edit / import / export / batch-loop / batch-trim / batch-collapse-spaces / batch-replace / batch-suffix`
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
- 兼容层保留原因和旧入口清单已整理到 [Dcat 最小兼容层审计](/Users/apple/Documents/dujiaoshuka/docs/dcat-compatibility-layer-audit.md)

影响：

- 仓库结构已经明显更贴近当前真实运行时
- 后台替换不再被历史目录结构绑架

## 当前仍在进行中的主轴

### A. 后台壳冻结维护

当前状态：

- 已经成为主承载，当前进入 RC 冻结维护

还没完成的点：

- 现有 `goods / order / pay / coupon / carmis` 批量动作字段边界继续通过测试和 smoke 维护
- 只接受真实运营阻断、回归、安全和文档一致性修正
- 不再以新增批量动作为默认推进方向

### B. 旧 Dcat 继续降耦合

当前状态：

- 已压掉目录和大量旧壳，旧 `/admin/*` 入口本轮复查后确认只剩兼容跳转职责

还没完成的点：

- `config/admin.php` 中仍保留 Dcat 兼容配置
- `routes/admin/routes.php` 仍承担 Dcat 登录/认证路由、后台壳挂载和旧 `/admin/*` 兼容跳转
- 少量旧后台运行时依赖还没彻底移出主链

本轮复查结论：

- `app/Admin` 目录无残留，旧 `App\\Admin\\Controllers` 不再承载业务逻辑
- 旧资源入口 `goods / goods-group / carmis / coupon / emailtpl / pay / order` 只通过 [LegacyAdminShellRedirectService.php](/Users/apple/Documents/dujiaoshuka/app/Service/LegacyAdminShellRedirectService.php) 跳转到 `/admin/v2/*`
- `import-carmis / system-setting / email-test` 三个历史入口仍保留兼容跳转
- 旧入口跳转测试已系统性覆盖资源别名、特殊别名和 query string 保留
- 暂不删除 [config/admin.php](/Users/apple/Documents/dujiaoshuka/config/admin.php) 与 [routes/admin/routes.php](/Users/apple/Documents/dujiaoshuka/routes/admin/routes.php)，避免破坏登录、认证中间件和后台可达性

### C. 支付层现代化收口

当前状态：

- 已完成第一大轮，且进入可维护状态

还没完成的点：

- 退役通道需要继续保持不恢复、不新增、不进入样例种子
- 保留通道的配置模型、回调异常路径和操作面仍可继续统一

### D. 安全治理与升级前清障

当前状态：

- 已经有清单、状态表、依赖阻塞矩阵、CI 护栏和升级实验命令，当前按 RC / stable-ready 候选要求维护

还没完成的点：

- 远端 CI 与最终发版分支复核
- stable-ready 发布说明和遗留边界持续维护
- PHP / Laravel 升级实验单独分支验证

当前 RC / stable-ready 收口口径：

- 后台壳日常运营动作基本可用，现阶段冻结新增功能，只维护预览、显式提交和低风险字段边界。
- Dcat 保留为登录、认证、中间件和旧入口兼容层，不再新增业务承载。
- 支付关键异常路径已有测试护栏，后续保留通道改动必须同步维护重复通知、签名失败、金额不一致和已完成订单重复推进。
- 升级前清障以依赖阻塞矩阵和实验分支验证清单为准，不直接跳 Laravel / PHP 大版本，不混入 stable-ready 收口。

## 当前剩余重点进度

### 第一优先级

1. 后台壳冻结维护
2. 继续维护旧 Dcat 兼容层测试和文档边界
3. 将已完成动作的字段边界、smoke 覆盖和 release 文档保持一致

### 第二优先级

1. 支付层进入维护期，保留通道回调异常路径已有测试护栏
2. 退役支付通道清单保持与代码、依赖和样例种子一致
3. 官方支付宝、官方微信、易支付和 Epusdt 后续改动必须同步维护回调安全测试

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

- “结构化治理后期，后台替换 RC 冻结期，升级前清障 stable-ready 前置期”

## 推荐下一步

后续连续执行时，以 [execution-baseline.md](/Users/apple/Documents/dujiaoshuka/docs/execution-baseline.md) 作为默认执行锚点；本审计文件只负责记录当前状态，不承担逐轮任务排序。

建议后续默认顺序：

1. 维护 RC.1 / stable-ready 文档、测试数字和冻结边界
2. 复核远端 CI 与最终发版分支
3. 保持退役支付通道防回流和保留通道回调安全测试
4. 维护 Dcat 最小兼容层，不在稳定版前删除关键入口
5. 后续 Laravel / PHP 升级只在单独实验分支推进
