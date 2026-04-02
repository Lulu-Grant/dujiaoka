<p align="center"><img src="public/assets/avatar/images/dujiaoka-xigua.png" width="150" alt="独角数卡西瓜版"></p>
<p align="center">
<a href="https://opensource.org/licenses/MIT"><img src="https://img.shields.io/badge/license-MIT-blue" alt="license MIT"></a>
<a href="https://github.com/Lulu-Grant/dujiaoka"><img src="https://img.shields.io/badge/fork-Lulu--Grant%2Fdujiaoka-green" alt="fork Lulu-Grant/dujiaoka"></a>
<a href="https://www.php.net/releases/7_4_0.php"><img src="https://img.shields.io/badge/legacy_runtime-PHP%207.4-lightgrey" alt="legacy runtime php74"></a>
<a href="https://www.php.net/supported-versions.php"><img src="https://img.shields.io/badge/modernization-in%20progress-orange" alt="modernization in progress"></a>
</p>

# 独角数卡西瓜版


`Lulu-Grant/dujiaoka` 是基于原始 `assimon/dujiaoka` 停更项目继续维护的分叉版本，当前品牌名为“独角数卡西瓜版”。

当前仓库的目标不是做“原样存档”，而是在保留原有业务能力的前提下，把它整理成一个更适合继续维护、继续升级、继续运营的版本：

- 恢复遗留运行时基线
- 建立核心业务测试护栏
- 拆分订单与支付主链服务
- 去除对守护进程的硬依赖
- 完成安装流程现代化
- 为后续 Laravel / PHP 升级清障

## 当前状态

- 原项目已停止维护，本仓库仍在持续推进现代化改造。
- 当前主线默认品牌已统一为“独角数卡西瓜版”。
- 当前已完成第一轮架构评估、测试护栏建设、订单主链拆分、去守护进程改造，以及安装流程现代化切换。
- 当前主线以“遗留基线可运行 + 渐进式重构”为原则推进。

如果你想了解截至目前的改造记录，请先看：

- [重构升级日志](docs/refactor-upgrade-log.md)
- [现代化路线图](docs/modernization-roadmap.md)
- [无守护进程改造清单](docs/no-daemon-migration-checklist.md)
- [安装流程现代化状态](docs/installer-modernization-status.md)

## 已完成阶段

- 恢复 PHP 7.4 遗留运行时基线，明确旧版本可验证路径
- 建立订单主链 PHPUnit 回归测试
- 将订单创建、支付完成、履约、通知拆成独立服务
- 移除对常驻 `queue:work` / `supervisord` 的硬依赖
- 更新 Docker / Debian / compose 部署说明
- 将创建订单改造成明确的 DTO 输入模型
- 将安装主路径切换为 `migrate + bootstrap seed + 显式创建首个管理员`
- 将 `install.sql` 降级为历史兼容参考文件
- 将前台、后台、安装页和默认通知品牌统一为“独角数卡西瓜版”

## 当前品牌与定位

- 品牌名：`独角数卡西瓜版`
- 仓库定位：遗留单体的持续维护分支
- 当前目标：稳定运行、逐步重构、为后续大版本升级做准备
- 默认前台主题：`avatar`

## 运行与验证

当前仓库保留了一套遗留基线脚本，便于在本地验证旧运行时行为：

```bash
./scripts/php74 vendor/bin/phpunit
```

当前主线测试结果基线：

```bash
OK (70 tests, 198 assertions)
```

更多环境说明请查看：

- [遗留运行时基线说明](docs/legacy-runtime-baseline.md)
- [运行时兼容阻塞点](docs/runtime-compatibility-blockers.md)

## 文档索引

- [重构升级日志](docs/refactor-upgrade-log.md)
- [现代化路线图](docs/modernization-roadmap.md)
- [项目审计记录](docs/project-audit-notes.md)
- [无守护进程改造清单](docs/no-daemon-migration-checklist.md)
- [遗留运行时基线说明](docs/legacy-runtime-baseline.md)
- [运行时兼容阻塞点](docs/runtime-compatibility-blockers.md)
- [安装流程现代化状态](docs/installer-modernization-status.md)
- [数据库现代化拆解计划](docs/database-modernization-plan.md)

## 说明

- 当前仓库仍是 Laravel 6 遗留系统的渐进式改造阶段，不是最终现代化完成态。
- 后续每一个重要节点都会持续记录到 [重构升级日志](docs/refactor-upgrade-log.md)。
