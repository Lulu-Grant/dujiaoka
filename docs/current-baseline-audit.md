# 当前基线审计

更新时间：2026-04-13

## 审计结论

当前仓库已经从“停更遗留项目”推进到了“可持续重构中的遗留系统”阶段，而且这条判断比前一轮更扎实了。

现在可以明确说：

- 核心业务链已有稳定护栏，可继续重构
- 安装与测试链已经完全脱离 `install.sql`
- 后台系统已经从“并行样板”进入“新壳主承载、旧壳最小兼容”阶段
- 升级准备已经进入真正的基线清障期

如果用一句话概括当前状态：

- 结构化治理后期，后台替换中后期，升级前清障前期

## 已完成进度

### 1. 运行时与测试基线

已完成：

- 恢复 PHP 7.4 遗留运行时基线
- 固定 `scripts/php74` / `scripts/composer74` 的可移植运行方式
- 建立本地快速拉站路径
- 建立 GitHub Actions `CI`
- 建立测试库准备脚本 [prepare-test-db](/Users/apple/Documents/dujiaoshuka/scripts/prepare-test-db)
- 让 CI 与测试库准备彻底脱离 `install.sql`

当前数字：

- PHPUnit：`OK (245 tests, 1169 assertions)`

影响：

- 后续重构已经不再依赖“人工点点看”
- 本地、CI、测试库准备口径已经统一

### 2. 订单与支付主链重构

已完成：

- 订单创建、支付完成、履约、通知拆成独立服务
- 订单创建切到 DTO / 应用服务模式
- 支付入口、支付回调、第三方 SDK 访问压缩到更清晰的服务层
- 退役 `Paysapi`、`Vpay`、`PayJS`
- `Stripe` 已稳定到现代 SDK 基线

影响：

- 订单域已不再挤在一个超大服务里
- 支付层具备继续收口和后续升级的基础

### 3. 去守护进程与部署模型收口

已完成：

- 订单过期不再依赖延迟 worker
- 通知与副作用默认改为同步优先、异步可选
- Docker / compose / Debian 文档已移除项目对 `supervisord` / 常驻 `queue:work` 的硬依赖

影响：

- 部署和排障复杂度明显下降
- 容器和环境统一成本更低

### 4. 安装与数据模型现代化

已完成：

- 主安装路径切到 `migrate + bootstrap seed + 显式管理员创建`
- migration / seed 分层建立完成
- bootstrap / sample / forbidden 默认数据策略形成
- `install.sql` 已退出安装主路径、退出 CI、并从仓库删除

影响：

- 新环境不再依赖历史 SQL 安装
- 测试、安装和仓库卫生终于统一到同一条现代路径

### 5. 前台与品牌统一

已完成：

- 新默认主题切到 `avatar`
- 旧主题已移除，当前走单主题模式
- 品牌统一为“独角数卡西瓜版”
- README、前台、后台、安装页品牌口径已统一

影响：

- 前台改造成本继续下降
- 品牌识别与仓库入口保持一致

### 6. 后台替换进入真实主承载期

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
- 商品：`create / edit / clone`
- 订单：`edit / reset search password`
- 邮件模板：`create / edit / preview`
- 支付通道：`create / edit / copy`
- 优惠码：`create / edit / batch generate`
- 卡密：`create / edit / import / export`
- 邮件测试：`send`
- 系统设置：`base / branding / mail / order / push / experience`

影响：

- `/admin` 默认已进入后台壳 dashboard
- 后台登录、退出和账号设置也已经进入后台壳
- 旧后台已经从“主承载”退化成“最小兼容层”

### 7. 旧后台目录退场

已完成：

- `app/Admin` 目录已从仓库退场
- Dcat 兼容层只剩：
  - [config/admin.php](/Users/apple/Documents/dujiaoshuka/config/admin.php)
  - [routes/admin/routes.php](/Users/apple/Documents/dujiaoshuka/routes/admin/routes.php)

影响：

- 仓库结构更贴近当前真实运行时
- 后台替换不再被历史目录结构绑架

## 当前仍在进行中的阶段

### A. 后台壳继续扩容

当前状态：

- 进行中，而且仍然是最值得持续投入的主线

还没完成的点：

- 商品、订单、支付通道等高频资源的更多低风险批量动作
- 后台壳统一批量操作能力
- 更多中复杂度业务页承载

### B. 旧 Dcat 继续降耦合

当前状态：

- 已经压掉了目录和大量旧壳，但还没真正收尾

还没完成的点：

- `config/admin.php` 中仍保留 Dcat 兼容配置
- `routes/admin/routes.php` 仍承担旧 `/admin/*` 兼容跳转
- 少量旧后台运行时依赖还没有完全移出主链

### C. 支付层现代化收口

当前状态：

- 已完成第一大轮

还没完成的点：

- PayPal 最终保留/退场策略仍需继续收口
- Stripe 虽然已升级，但仍需要继续按最佳实践整理
- 保留通道的配置模型与动作页还可以继续统一

## 剩余重点进度

### 第一优先级

1. 后台壳继续扩容
2. 继续压缩旧 Dcat 高频页和兼容层承载
3. 将后台壳从“能看、能配、能编辑”推进到“能承接更多真实操作和批量动作”

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

如果把整个项目分成几个成熟度阶段：

- 已脱离“只能救火”
- 已进入“结构化治理”
- 正在逼近“现代化收口”

当前最准确的位置是：

- “结构化治理后期，后台替换中后期，升级前清障前期”

## 推荐下一步

建议后续默认顺序：

1. 继续后台壳扩容
2. 继续压缩旧 Dcat 最小兼容层
3. 回到支付层收尾
4. 启动安全专项
5. 再进入 Laravel / PHP 升级前清障

## 当前基线数字

- PHPUnit：`OK (245 tests, 1169 assertions)`
- 后台壳首页：已成为 `/admin` 默认落点
- 后台壳真实资源页：10 组资源页 + 认证入口
- 后台壳真实动作页：已覆盖配置、CRUD、导入、导出、复制、批量生成、预览、测试发送、查询密码重置
