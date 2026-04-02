# 安装默认数据分类清单

## 目标

把当前 `database/sql/install.sql` 中混杂的默认数据，明确拆成三类：

- `bootstrap`
  安装后立即可用、且不携带高风险默认凭据的数据
- `sample`
  仅用于本地开发、演示或测试的样例数据
- `forbidden`
  不允许继续作为默认安装数据保留的高风险内容

## 当前分类

### bootstrap

- `emailtpls`
  原因：邮件模板属于系统默认文案，不涉及真实凭据，且业务通知链依赖它。
- `failed_jobs`
  原因：这是结构表，不带初始化业务数据。
- `admin_menu`
  原因：在后台仍依赖 Dcat 的过渡期内，菜单骨架可以作为后台可用性 bootstrap 数据候选。
- `admin_permissions`
  原因：与后台菜单同属权限骨架，过渡期可视作后台运行所需基础数据候选。
- `admin_roles`
  原因：过渡期后台至少需要一个角色骨架，但不应再绑定默认用户。
- `admin_permission_menu`
  原因：属于后台权限骨架的空或关系数据候选。
- `admin_role_menu`
  原因：属于后台权限骨架的空或关系数据候选。
- `admin_role_permissions`
  原因：属于后台权限骨架的空或关系数据候选。
- `admin_settings`
  原因：属于系统设置结构容器，默认可为空，不应携带敏感值。

### sample

- `pays`
  原因：支付方式里的商户号、密钥、回调地址本质上都是演示配置，不应进入默认安装。
- `orders`
  原因：示例订单仅用于本地测试或开发演示。

### forbidden

- `admin_users`
  原因：内置默认管理员账号和固定密码哈希，属于明确的高风险默认凭据。
- `admin_role_users`
  原因：它把默认管理员账号和管理员角色直接绑定，和默认后台用户一起构成风险闭环。

## 当前迁移进度

- 已完成：
  - `emailtpls` 迁入 bootstrap seed
  - `pays` 迁入 sample seed
  - `admin_menu` / `admin_permissions` / `admin_roles` 迁入安全 bootstrap seed
- 下一步：
  - 为后台骨架数据建立单独的迁移/seed 迁出方案
  - 在安装流程中彻底禁止默认管理员账号导入
  - 最终让 `install.sql` 退出安装主路径
