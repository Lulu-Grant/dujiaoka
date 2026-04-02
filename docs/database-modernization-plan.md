# 数据库现代化拆解计划

## 目标

将当前仓库从“依赖 `database/sql/install.sql` 整包导入”的安装方式，逐步迁移为：

- schema 由 Laravel migrations 驱动
- 默认数据由 seeders 驱动
- 安装器不再承担数据库结构初始化职责

## 当前现状

当前仓库：

- 没有 `database/migrations`
- 只有 [install.sql](/Users/apple/Documents/dujiaoshuka/database/sql/install.sql)
- `install.sql` 同时承担：
  - 建表
  - 默认后台数据
  - 默认邮件模板
  - 默认支付方式样例
  - 初始管理员记录

这会导致：

- 本地和生产安装不可组合
- 灰度迁移困难
- 安全默认值容易混入真实环境
- 表结构变更无法被增量管理

## 分批迁移策略

### 第一批：核心商业主链表

优先迁移这些表：

- `goods_group`
- `goods`
- `carmis`
- `coupons`
- `coupons_goods`
- `pays`
- `orders`

原因：

- 这些表直接承载下单、支付、履约、库存、优惠码主链
- 也是当前测试体系覆盖最多的业务域

### 第二批：业务支撑表

- `emailtpls`
- `failed_jobs`
- 后续如有业务扩展表也归入本批

### 第三批：后台与系统表

- `admin_*`
- `admin_settings`

原因：

- 后台框架未来可能替换
- 不应让后台表迁移阻塞核心业务表迁移
- 但其中的“结构”和“默认高风险账号”必须拆开推进

## 默认数据拆分原则

后续数据拆分按下面原则执行：

- schema migration 只负责结构
- sample / bootstrap data 进 seeder
- 高风险默认值单独清理，不直接复刻进生产默认 seed

特别注意：

- 默认管理员账号不能作为长期 seed 保留
- 默认支付方式样例可以作为开发 seed，但不应强制进入生产
- 邮件模板更适合独立 seeder
- 后台骨架数据需要单独评估，避免和默认管理员账号一起被继续打包导入

## 已完成起步

当前已开始的迁移起步工作：

- 新建 `database/migrations`
- 已将核心商业主链表作为第一批迁移对象
- 已开始第二批业务支撑表迁移：
  - `emailtpls`
  - `failed_jobs`
- 已开始把默认数据从结构迁移中拆出去：
  - `DatabaseSeeder` 仅保留 bootstrap 安装数据入口
  - 默认邮件模板改由 `EmailTemplateSeeder` 提供
  - 示例订单数据改由 `SampleDataSeeder` 单独承载
  - 默认支付方式样例改由 `PaySampleSeeder` 单独承载
- 已补充安装数据分类清单：
  - [install-data-classification.md](/Users/apple/Documents/dujiaoshuka/docs/install-data-classification.md)
- 已开始第三批后台骨架结构迁移：
  - `admin_menu`
  - `admin_permissions`
  - `admin_roles`
  - `admin_permission_menu`
  - `admin_role_menu`
  - `admin_role_permissions`
  - `admin_settings`
- 已开始第三批后台骨架安全 seed：
  - `AdminBootstrapSeeder` 负责后台菜单、权限、角色骨架
  - 不包含 `admin_users` / `admin_role_users`
- 已开始改造安装主路径：
  - 安装服务改为 `migrate + bootstrap seed`
  - 首个管理员账号由安装表单显式创建，不再使用默认账号

## 下一步

1. 收口安装流程改造，继续减少对 `install.sql` 的直接依赖
2. 在安装流程中彻底移除默认管理员账号导入
3. 继续清理 `install.sql` 里的默认数据职责，补 bootstrap / sample seed 分层
4. 校对 migration 与当前模型 / 测试依赖的一致性
