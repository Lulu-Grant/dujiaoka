# 安全基线审计

更新时间：2026-05-21

## 本轮结论

本轮复查范围覆盖默认账号、默认密钥、`.env.example`、本地 `.env` 模板、安装表单、安装服务、bootstrap seed、本地准备脚本和测试库准备脚本。

当前生产安装主路径结论：

- 不再导入 `install.sql`
- 不再 seed 默认管理员账号
- 不再 seed 支付商户密钥样例
- `APP_KEY` 在安装时由 `InstallationService` 随机生成
- 首个管理员账号和密码由安装表单显式创建
- 管理员密码使用 `Hash::make()` 落库

## 审计清单

| 项目 | 当前状态 | 结论 | 后续动作 |
| --- | --- | --- | --- |
| `.env.example` | `APP_KEY={app_key}`，数据库、Redis 和后台路径均为占位符 | 可保留 | 不提交真实 `base64:` key |
| `.env.local.example` | `APP_KEY=` 为空，本地脚本会生成随机 key | 可保留 | 仅用于本地开发 |
| `.env` | 已被 `.gitignore` 排除 | 可保留在本机 | 不纳入版本控制 |
| `install.lock` | 已被 `.gitignore` 排除 | 可保留在本机 | 不纳入版本控制 |
| 安装表单管理员账号 | 已取消预填 `admin` | 已处理 | 用户安装时必须显式填写 |
| 安装表单管理员密码 | 必填、至少 8 位、需确认 | 已处理 | 后续可增强复杂度规则 |
| `InstallationService` | 随机生成 `APP_KEY`，显式创建管理员 | 已处理 | 保持 `Hash::make()` |
| `AdminBootstrapSeeder` | 只 seed 菜单、权限、角色 | 已处理 | 不加入 `admin_users` |
| `BootstrapSeeder` | 只调用后台骨架和邮件模板 seed | 已处理 | 不加入支付样例和管理员账号 |
| `PaySampleSeeder` | 只在 sample/dev/test 场景使用 | 可保留 | 不进入安装 bootstrap |
| `scripts/prepare-local-dev` | 本地生成 `.env`、随机 `APP_KEY` 和 `install.lock` | 可保留 | 仅本地开发 |
| `scripts/prepare-test-db` | 使用本地测试库默认账号口径 | 可保留 | 仅测试库准备，不作为生产安装文档 |

## 风险边界

- 测试中的 `secret123`、`stripe-secret-key`、`server-token` 等值仅作为 fixture 或 mock 使用，不是生产默认值。
- 文档中的示例账号应明确写成“安装时自行创建”，不要再恢复 `admin/admin` 口径。
- 本地 smoke 默认账号仅服务于开发环境，真实安装不应依赖它。
- 如果 GitHub secret scanning 再次报告 Laravel `APP_KEY`，优先检查是否有硬编码 `base64:` 形态进入 `.github`、`phpunit.xml`、文档或示例文件。

## 下一步

- 复查后台批量动作、导入、配置编辑和账号设置的权限边界。
- 复查上传、模板输入、邮件发送和通知配置等输入面。
- 将测试脚本中的本地默认账号说明继续限制为开发用途，避免误读为生产默认凭据。
