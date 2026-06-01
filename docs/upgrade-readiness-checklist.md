# 升级前清障清单

## 目标

本文档用于把“后续要升级 Laravel / PHP”拆成一份可以执行的前置清单。

重点不是直接升级，而是先回答：

- 现在被什么卡住
- 哪些卡点已经缓解
- 下一步应先清哪一类阻塞

---

## 当前基线

- Framework：Laravel 7.30.7 实验分支
- Admin：Dcat Admin 2.0.24-beta
- Runtime baseline：PHP 7.4 可验证
- PHP 8.1：Composer 平台检查、artisan、全量 PHPUnit 和后台 smoke 已通过
- 本机现代运行时：PHP 8.5.4
- 订单主链测试：已建立基础护栏
- 安装主路径：已切换到 migration + bootstrap seed

---

## 已缓解阻塞

以下问题已不再是升级前的第一优先级：

### 1. 安装流程完全依赖 `install.sql`

状态：已显著缓解

当前情况：

- 安装主路径已经不再依赖整包 SQL 导入
- Schema 已转入 migration 驱动
- 默认数据已拆分为 bootstrap / sample / forbidden

### 2. 核心订单链缺乏回归护栏

状态：已显著缓解

当前情况：

- 已有 90 条测试、264 个断言
- 核心订单、支付入口、回调、过期流程已有回归保护

### 3. 队列 worker / 守护进程是业务硬前提

状态：已显著缓解

当前情况：

- 订单过期已改为命令扫描
- 通知类副作用默认同步执行
- 项目不再把常驻 worker 当成核心业务前提

---

## 当前主要阻塞

### P0：运行时与依赖版本阻塞

已确认的典型阻塞：

- `bacon/bacon-qr-code 1.0.3`
- `simplesoftwareio/simple-qrcode 2.0.0`

已完成的第一条清障：

- `phpspec/prophecy 1.13.0` 已随 `phpunit/phpunit` 升级到 `9.6.34` 从主锁文件移除
- `germey/geetest v3.1.0` 已从前台下单主路径与主锁文件中移除
- `simplesoftwareio/simple-qrcode 2.0.0` 与 `bacon/bacon-qr-code 1.0.3` 已从主锁文件移除，二维码支付页已切换为前端本地生成

影响：

- PHP 8.1 已可通过 Docker 工具链验证
- Laravel 7.30.7 桥接实验分支已通过 PHP 7.4 / PHP 8.1 双运行时 PHPUnit 与后台 smoke
- PHP 8.5 仍不是当前目标运行时

建议动作：

1. 为阻塞包建立“保留 / 替换 / 移除”决策表
2. 优先验收 Dcat 兼容层与支付保留链这两条剩余高优先级链
3. Laravel 桥接升级按 [laravel-bridge-upgrade-plan.md](/Users/apple/Documents/dujiaoshuka/docs/laravel-bridge-upgrade-plan.md) 执行，先把 Laravel 7 实验分支做成可评审候选，再评估 Laravel 8

参考：

- [dependency-blocker-matrix.md](/Users/apple/Documents/dujiaoshuka/docs/dependency-blocker-matrix.md)

### P0：后台框架阻塞

阻塞点：

- `dcat/laravel-admin 2.*`
- Dcat 包仍参与后台登录、认证中间件、权限白名单和旧入口兼容
- `config/admin.php` 与 `routes/admin/routes.php` 仍是过渡期关键引导文件

已缓解：

- `app/Admin` 生产目录已经退场
- 后台主入口、dashboard、高频资源页和低风险批量动作已经切到后台壳
- 旧 `/admin/*` 资源入口已经退化为 query-preserving 跳转层

影响：

- Laravel 7 已可启动并通过测试，但后续 Laravel 8+ 仍可能卡在 Dcat 包启动、认证 guard、后台中间件和权限配置
- 但业务 CRUD 与批量动作已经不再主要卡在旧 Dcat 控制器

建议动作：

1. 冻结新增 Dcat 绑定型功能
2. 维护 [Dcat 最小兼容层审计](/Users/apple/Documents/dujiaoshuka/docs/dcat-compatibility-layer-audit.md)，确保保留原因、旧入口清单和删除条件可验收
3. 继续把后台业务动作固定在普通控制器、页面服务和动作服务中
4. 为后续替换登录认证、中间件和权限白名单做实验分支准备

### P1：支付生态中的历史 SDK

重点对象：

- 已退役并移出主锁文件的 `paypal/rest-api-sdk-php`
- 已退役并移出主锁文件的 `stripe/stripe-php`
- 仍保留支付通道中的旧式控制器遗留实现

影响：

- PayPal / Stripe 不再作为当前升级阻塞，但历史通道退场必须保持不可回流
- 保留通道仍需继续约束回调安全和配置边界

建议动作：

1. 继续压缩保留支付通道中的旧控制器逻辑
2. 保持退役网关不恢复到路由、样例种子或依赖锁文件

### P1：Laravel 6 时代写法耦合

典型表现：

- 控制器内嵌大量流程编排
- 旧式 facade / helper 使用较多
- 旧配置和老式安装假设残留

影响：

- 版本升级时兼容改造面会被放大

建议动作：

1. 保持新改动优先走 service / DTO 路线
2. 继续减少“控制器即业务层”的写法

---

## 推荐执行顺序

### 第一组：依赖清障

- 先做阻塞包矩阵
- 再做可替换链路的最小替换

### 第二组：后台降耦合

- 不急着替换后台框架
- 先替换 Dcat 登录认证、中间件和权限白名单这些最后保留的框架职责

### 第三组：支付剩余高风险点收口

- 尤其是官方支付宝、官方微信、易支付和 Epusdt 的回调异常路径

### 第四组：升级路径设计

- 在上面三组稳定后，再正式制定：
  - PHP 目标版本
  - Laravel 分步升级路径
  - 后台替换时机

---

## 当前结论

项目已经从“完全没有升级准备”推进到“Laravel 7 桥接实验已通过，正在做可合并候选验收”。

但目前仍不适合：

- 直接切 PHP 8.5
- 直接跳 Laravel 8/10
- 直接整体替后台

当前最正确的姿势仍然是：

- 先验收 Laravel 7 实验分支
- 再压缩 Dcat 兼容层和支付保留链风险
- 最后单独规划 Laravel 8+ 跨版本迁移
