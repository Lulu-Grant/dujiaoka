# 安装流程现代化状态

## 当前状态

当前安装主路径已经不再依赖 `database/sql/install.sql` 整包导入。

现在安装流程使用的是：

- Laravel migrations
- `DatabaseSeeder` 负责 bootstrap 默认数据
- 安装表单显式创建首个管理员账号
- `install.lock` 继续作为安装完成标记

核心入口：

- [HomeController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Home/HomeController.php)
- [InstallationService.php](/Users/apple/Documents/dujiaoshuka/app/Service/InstallationService.php)

## 仍然保留的旧内容

`database/sql/install.sql` 目前还保留在仓库里，但已经降级为：

- 历史结构快照
- 数据迁移盘点参考
- 审计对照依据

它不再是推荐安装入口，也不应再被新的部署文档作为主流程使用。

当前覆盖盘点可参考：

- [install-sql-coverage-matrix.md](/Users/apple/Documents/dujiaoshuka/docs/install-sql-coverage-matrix.md)

## 新旧职责对比

旧模型：

- 安装器写 `.env`
- 安装器直接导入 `install.sql`
- `install.sql` 同时承载结构、默认数据、样例配置、默认管理员账号

新模型：

- 安装器写 `.env`
- 安装器执行 migrations
- 安装器执行 bootstrap seed
- 安装器显式创建首个管理员
- 样例数据改为 sample seed，按需使用

## 已完成

- 核心业务表 migration 起步
- 第二批业务支撑表 migration 起步
- 邮件模板迁入 bootstrap seed
- 支付方式样例迁入 sample seed
- 后台菜单/权限/角色骨架迁入安全 bootstrap seed
- 默认管理员账号从安装默认数据中移除
- 安装表单改为显式输入管理员账号密码

## 下一步

- 继续把 `install.sql` 里剩余未迁出的后台关系数据做成更明确的迁移/seed 方案
- 更新部署文档，统一以新安装流程为准
- 在确认历史兼容窗口结束后，再评估是否彻底移除 `install.sql`
