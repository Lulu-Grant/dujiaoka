<p align="center"><img src="https://i.loli.net/2020/04/07/nAzjDJlX7oc5qEw.png" width="100"></p>
<p align="center">
<a href="https://opensource.org/licenses/MIT"><img src="https://img.shields.io/badge/license-MIT-blue" alt="license MIT"></a>
<a href="https://github.com/Lulu-Grant/dujiaoka"><img src="https://img.shields.io/badge/fork-Lulu--Grant%2Fdujiaoka-green" alt="fork Lulu-Grant/dujiaoka"></a>
<a href="https://www.php.net/releases/7_4_0.php"><img src="https://img.shields.io/badge/legacy_runtime-PHP%207.4-lightgrey" alt="legacy runtime php74"></a>
<a href="https://www.php.net/supported-versions.php"><img src="https://img.shields.io/badge/modernization-in%20progress-orange" alt="modernization in progress"></a>
</p>

# 🦄Dujiaoka🍉西瓜超级维护版本

`Lulu-Grant/dujiaoka` 是基于原始 `assimon/dujiaoka` 停更项目继续维护的分叉版本。

当前仓库的目标不是做“原样存档”，而是在保留原有业务能力的前提下，逐步完成：

- 遗留运行时基线恢复
- 核心业务测试补齐
- 订单主链服务拆分
- 去守护进程部署改造
- 后续 Laravel / PHP 现代化升级准备

## 当前状态

- 原项目已停止维护，本仓库继续推进现代化改造。
- 当前已完成第一轮架构评估、测试护栏建设和订单主链拆分。
- 当前主线默认以“遗留基线可运行 + 渐进式重构”为原则推进。

如果你想了解截至目前的改造记录，请先看：

- [重构升级日志](docs/refactor-upgrade-log.md)
- [现代化路线图](docs/modernization-roadmap.md)
- [无守护进程改造清单](docs/no-daemon-migration-checklist.md)

## 目前已经完成的工作

- 恢复 PHP 7.4 遗留运行时基线，明确旧版本可验证路径
- 建立订单主链 PHPUnit 回归测试
- 将订单创建、支付完成、履约、通知拆成独立服务
- 移除对常驻 `queue:work` / `supervisord` 的硬依赖
- 更新 Docker / Debian / compose 部署说明
- 将创建订单改造成明确的 DTO 输入模型

## 运行与验证

当前仓库保留了一套遗留基线脚本，便于在本地验证旧运行时行为：

```bash
./scripts/php74 vendor/bin/phpunit
```

当前主线测试结果基线：

```bash
OK (23 tests, 90 assertions)
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

## 说明

- 当前仓库仍是 Laravel 6 遗留系统的渐进式改造阶段，不是最终现代化完成态。
- 后续每一个重要节点都会持续记录到 [重构升级日志](docs/refactor-upgrade-log.md)。
