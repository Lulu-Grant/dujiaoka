# 安装现代化阶段结项说明

## 结项结论

本阶段以“安装主路径现代化完成”结项。

当前主线已经满足：

- 新安装不再依赖 `install.sql`
- 数据库结构由 migrations 驱动
- 默认安装数据由 bootstrap seed 驱动
- 样例数据由 sample seed 驱动
- 首个管理员账号由安装表单显式创建
- 默认管理员账号与默认角色绑定不再作为安装默认值存在

## 关于后台空关系表的最终判断

下面这些表在原始 `install.sql` 中本来就没有有效默认记录：

- `admin_permission_menu`
- `admin_role_menu`
- `admin_role_permissions`
- `admin_settings`

本阶段的最终决策是：

- 保持它们为“空结构表”
- 不额外补伪造的 bootstrap 关系数据

原因：

- 当前后台最小可用骨架已经由菜单、权限、角色和安装时显式创建的管理员账号构成
- 这些空关系表没有真实默认数据来源，强行补关系反而会引入不必要的历史包袱
- 后续如果后台框架迁移或权限模型调整，这些表更适合按真实需求生成，而不是为了兼容旧 SQL 人为灌入数据

## install.sql 的当前定位

`install.sql` 在移出仓库主路径前，保留的意义只有三项：

- 历史结构快照
- 审计与迁移对照
- 短期兼容窗口内的人工参考

它不再承担：

- 安装入口
- 默认数据来源
- 默认管理员来源

## 后续建议

- 当前 `install.sql` 已完成退场并从仓库主路径移除
- 后续如果需要历史对照，以现有迁移拆解文档为准
- 如果要删，优先保留当前文档链路：
  - `install-sql-coverage-matrix.md`
  - `installer-modernization-status.md`
  - `refactor-upgrade-log.md`

## 阶段产出

- 安装入口服务化
- migration 覆盖 `install.sql` 全部表结构
- bootstrap / sample / forbidden 三层默认数据治理
- 安装文档与部署手册更新
- 安装现代化测试护栏建立
