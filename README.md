
<p align="center"><img src="public/assets/avatar/images/dujiaoka-xigua.png" width="150" alt="独角数卡西瓜版"></p>
<p align="center">
<a href="https://opensource.org/licenses/MIT"><img src="https://img.shields.io/badge/license-MIT-blue" alt="license MIT"></a>
<a href="https://github.com/Lulu-Grant/dujiaoka"><img src="https://img.shields.io/badge/fork-Lulu--Grant%2Fdujiaoka-green" alt="fork Lulu-Grant/dujiaoka"></a>
<a href="https://www.php.net/releases/7_4_0.php"><img src="https://img.shields.io/badge/legacy_runtime-PHP%207.4-lightgrey" alt="legacy runtime php74"></a>
<a href="https://www.php.net/supported-versions.php"><img src="https://img.shields.io/badge/modernization-in%20progress-orange" alt="modernization in progress"></a>
<a href="https://github.com/Lulu-Grant/dujiaoka/actions/workflows/ci.yml"><img src="https://github.com/Lulu-Grant/dujiaoka/actions/workflows/ci.yml/badge.svg" alt="ci"></a>
</p>

# 独角数卡西瓜版

`Lulu-Grant/dujiaoka` 是基于原始 `assimon/dujiaoka` 停更项目继续维护的分叉版本，当前品牌名为“独角数卡西瓜版”。

这个仓库现在已经不是“原样存档”，也不只是“能跑就行”的修补版，而是在保留原有业务能力前提下，持续推进现代化治理、后台替换和升级前清障的维护分支。

## 界面预览

### 前台首页

<img width="1516" height="824" alt="首页效果" src="https://github.com/user-attachments/assets/664d7870-8986-41c6-a27e-1ee917111a8f" />

当前主目标：

- 恢复遗留运行时基线
- 建立核心业务测试护栏
- 拆分订单与支付主链服务
- 去除对守护进程的硬依赖
- 完成安装流程现代化
- 建立 GitHub Actions 与本地快速拉站路径
- 持续降低后台 `Dcat Admin` 业务承载
- 为后续 Laravel / PHP 升级清障

## 当前状态

- 原项目已停止维护，本仓库仍在持续推进现代化改造。
- 当前主线默认品牌已统一为“独角数卡西瓜版”。
- 当前主线已经完成：遗留运行时基线恢复、测试护栏建设、订单与支付主链第一轮服务化、去守护进程改造、安装流程现代化切换，以及后台壳并行迁移。
- 当前主线以“遗留基线可运行 + 渐进式重构 + 后台并行替换”为原则推进。

如果只用一句话描述现在的位置：

- 我们已经从“停更遗留项目”推进到了“可持续重构中的遗留系统”阶段。

如果你想了解截至目前的改造记录，请先看：

- [重构升级日志](docs/refactor-upgrade-log.md)
- [现代化路线图](docs/modernization-roadmap.md)
- [无守护进程改造清单](docs/no-daemon-migration-checklist.md)
- [安装流程现代化状态](docs/installer-modernization-status.md)

## 已完成阶段

- 恢复 PHP 7.4 遗留运行时基线，明确旧版本可验证路径
- 建立订单、支付、安装、后台解耦相关 PHPUnit 回归测试
- 将订单创建、支付完成、履约、通知拆成独立服务
- 清理多条现代 PHP 阻塞依赖链，并将 `stripe/stripe-php` 升到 `^20.0`
- 移除对常驻 `queue:work` / `supervisord` 的硬依赖
- 更新 Docker / Debian / compose 部署说明
- 将创建订单改造成明确的 DTO 输入模型
- 将安装主路径切换为 `migrate + bootstrap seed + 显式创建首个管理员`
- 将 `install.sql` 降级为历史兼容参考文件
- 补齐 GitHub Actions `CI` 工作流
- 补齐本地快速拉站模板与准备脚本
- 将后台高频 CRUD 页中的配置、选项源、状态映射、展示逻辑持续迁出 `app/Admin`
- 将前台、后台、安装页和默认通知品牌统一为“独角数卡西瓜版”
- 将后台壳首页、商品分类、商品、订单、邮件模板、支付通道、优惠码、卡密、系统设置、邮件测试等页面逐步接入新后台壳

## 当前重点进度

### 后台壳

### 后台登录

<img width="1647" height="833" alt="后台登录界面" src="https://github.com/user-attachments/assets/9f0fe4d5-94d7-4909-83b2-e9ea680e30c1" />

当前已落地后台壳资源：

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

当前后台壳已落地动作页包括：

- 商品分类 `create / edit`
- 商品 `create / edit`
- 优惠码 `create / edit`
- 支付通道 `create / edit`
- 卡密 `create / edit / import`
- 邮件模板 `create / edit`
- 邮件测试发送
- 系统设置 `base / branding / mail / push / experience`

### 后台 UI

<img width="1675" height="868" alt="后台UI" src="https://github.com/user-attachments/assets/f3083155-1697-41fb-a1a4-7225db8e5d10" />

### 当前仍在推进的重点

- 持续扩大后台壳对中复杂度后台页面的承载范围
- 持续压缩旧 `Dcat Admin` 在高频后台页上的业务承载
- 继续收口支付层保留通道
- 为后续 PHP / Laravel 升级继续清障

## 当前品牌与定位

- 品牌名：`独角数卡西瓜版`
- 仓库定位：遗留单体的持续维护分支
- 当前目标：稳定运行、逐步重构、为后续大版本升级做准备
- 默认前台主题：`avatar`
- 当前后台状态：保留 `Dcat Admin` 过渡，但持续向“薄展示层”收缩

## 运行与验证

当前仓库保留了一套遗留基线脚本，便于在本地验证旧运行时行为：

```bash
./scripts/php74 vendor/bin/phpunit
```

当前主线测试结果基线：

```bash
OK (230 tests, 1049 assertions)
```

当前仓库也已经补上 GitHub Actions 基线工作流：

- `CI`：在 GitHub Actions 中使用 PHP `7.4` + MariaDB 运行 PHPUnit
- 支持 `push`、`pull_request`、手动 `workflow_dispatch`
- 当前已连续通过多次主线推送验证

如果你想在本机快速把站点拉起来，可以先走这条本地开发路径：

```bash
./scripts/prepare-local-dev
./scripts/php74 artisan --version
./scripts/php74 artisan route:list
./scripts/php74 -S 127.0.0.1:8020 -t public
./scripts/smoke-admin-shell
```

说明：

- 本地开发模板在 [/.env.local.example](/Users/apple/Documents/dujiaoshuka/.env.local.example)
- 准备脚本在 [/scripts/prepare-local-dev](/Users/apple/Documents/dujiaoshuka/scripts/prepare-local-dev)
- 烟雾脚本在 [/scripts/smoke-admin-shell](/Users/apple/Documents/dujiaoshuka/scripts/smoke-admin-shell)
- 默认会使用本机 `127.0.0.1:3306` 的 `dujiaoka_test` 数据库和本机 Redis
- 如果检测到 Homebrew MariaDB 的 `/private/tmp/mysql.sock`，准备脚本会自动切到 socket 模式并使用当前系统用户
- `.env` 不会进入版本控制
- 当前这条本地启动路径已经完成真实 HTTP 验证，首页可返回 `200 OK`
- 烟雾脚本会登录后台并巡检 `dashboard`、`auth/setting`、`goods/create`、`emailtpl/create`、`goods`、`order`

更多环境说明请查看：

- [遗留运行时基线说明](docs/legacy-runtime-baseline.md)
- [运行时兼容阻塞点](docs/runtime-compatibility-blockers.md)
- [本地快速拉站](docs/local-dev-quickstart.md)

## 当前审计

如果你想快速看清“已经做到哪里、还剩多少工作”，建议先看：

- [当前基线审计](docs/current-baseline-audit.md)
- [重构升级日志](docs/refactor-upgrade-log.md)
- [大整改执行方案](docs/rectification-execution-plan.md)

## 文档索引

- [重构升级日志](docs/refactor-upgrade-log.md)
- [现代化路线图](docs/modernization-roadmap.md)
- [项目审计记录](docs/project-audit-notes.md)
- [无守护进程改造清单](docs/no-daemon-migration-checklist.md)
- [遗留运行时基线说明](docs/legacy-runtime-baseline.md)
- [运行时兼容阻塞点](docs/runtime-compatibility-blockers.md)
- [安装流程现代化状态](docs/installer-modernization-status.md)
- [数据库现代化拆解计划](docs/database-modernization-plan.md)
- [后台替换评估](docs/admin-replacement-assessment.md)
- [升级前清障清单](docs/upgrade-readiness-checklist.md)
- [支付迁移计划](docs/paypal-stripe-transition-plan.md)
- [本地快速拉站](docs/local-dev-quickstart.md)
- [当前基线审计](docs/current-baseline-audit.md)

## 说明

- 当前仓库仍是 Laravel 6 遗留系统的渐进式改造阶段，不是最终现代化完成态。
- 当前 `master` 已具备：本地快速拉站、GitHub Actions 自动回归、安装现代化主路径，以及持续推进中的后台壳并行迁移基础。
- 当前 `/admin` 默认已经落到新的后台壳首页。
- 后续每一个重要节点都会持续记录到 [重构升级日志](docs/refactor-upgrade-log.md)。
