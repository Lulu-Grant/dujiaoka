# install.sql 覆盖矩阵

## 目的

这份矩阵用于回答两个问题：

1. 原始 `install.sql` 里的哪些结构已经被 migration 接管
2. 哪些默认数据已经被 bootstrap / sample / forbidden 策略接管，哪些还只是历史快照

## 表级覆盖

| 表名 | 结构状态 | 默认数据状态 | 当前定位 |
| --- | --- | --- | --- |
| `admin_menu` | 已迁移 | 已迁入 `AdminBootstrapSeeder` | bootstrap |
| `admin_permission_menu` | 已迁移 | 当前为空，结构已接管 | bootstrap candidate |
| `admin_permissions` | 已迁移 | 已迁入 `AdminBootstrapSeeder` | bootstrap |
| `admin_role_menu` | 已迁移 | 当前为空，结构已接管 | bootstrap candidate |
| `admin_role_permissions` | 已迁移 | 当前为空，结构已接管 | bootstrap candidate |
| `admin_role_users` | 已迁移 | 默认绑定已禁入，安装时显式创建 | forbidden |
| `admin_roles` | 已迁移 | 已迁入 `AdminBootstrapSeeder` | bootstrap |
| `admin_settings` | 已迁移 | 当前为空，结构已接管 | bootstrap candidate |
| `admin_users` | 已迁移 | 默认账号已禁入，安装时显式创建 | forbidden |
| `carmis` | 已迁移 | 无默认数据 | structure only |
| `coupons` | 已迁移 | 无默认数据 | structure only |
| `coupons_goods` | 已迁移 | 无默认数据 | structure only |
| `emailtpls` | 已迁移 | 已迁入 `EmailTemplateSeeder` | bootstrap |
| `failed_jobs` | 已迁移 | 无默认数据 | structure only |
| `goods` | 已迁移 | 无默认数据 | structure only |
| `goods_group` | 已迁移 | 无默认数据 | structure only |
| `migrations` | Laravel 自管 | 不需要 seed | framework managed |
| `orders` | 已迁移 | 示例数据已迁入 `SampleDataSeeder` | sample |
| `pays` | 已迁移 | 样例配置已迁入 `PaySampleSeeder` | sample |

## 当前结论

- `install.sql` 中所有业务与后台表结构，都已经有对应 migration 覆盖。
- 当前剩余未完全“退场”的价值，主要是：
  - 历史结构快照
  - 默认数据拆分的审计对照
  - 后台空关系表的历史来源参考

## 剩余差距

- `admin_permission_menu`
- `admin_role_menu`
- `admin_role_permissions`
- `admin_settings`

这些表虽然结构已迁移，但还没有单独的“是否需要 bootstrap 数据”的最终结论。
当前 `install.sql` 中它们本来也没有有效默认记录，所以它们并不阻塞 `install.sql` 退场。

状态更新：

- 当前已决定这些表维持为空结构，不额外补伪造 bootstrap 关系数据。
- 这意味着它们不再构成安装现代化阶段的未决阻塞项。

## 建议的移除条件

下面这些条件满足后，`install.sql` 已具备从仓库主路径移除的条件：

- 安装与部署文档全部统一到 migrations + seeders
- 本地/测试/生产 bootstrap 都不再依赖 `install.sql`
- 审计文档已保留必要的历史说明
- 团队确认不再需要用它做历史对照

## 当前判断

从功能上说，`install.sql` 已经退出安装主路径。  
从仓库治理上说，它也已经完成退场，并从仓库主路径移除。
