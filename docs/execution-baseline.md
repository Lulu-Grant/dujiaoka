# 执行基线

更新时间：2026-06-02

## 文件定位

这个文件是后续静默推进和常驻基线监控的默认执行锚点。

当 README、当前基线审计、整改总纲、进度总汇或历史日志之间出现口径差异时，以本文件作为“下一步做什么、先不做什么、做到什么算收口”的优先判断依据。

本文件不替代历史记录：

- [refactor-upgrade-log.md](/Users/apple/Documents/dujiaoshuka/docs/refactor-upgrade-log.md) 记录已经发生的事实
- [current-baseline-audit.md](/Users/apple/Documents/dujiaoshuka/docs/current-baseline-audit.md) 记录当前状态盘点
- [rectification-execution-plan.md](/Users/apple/Documents/dujiaoshuka/docs/rectification-execution-plan.md) 记录长期整改总纲
- [admin-shell-action-boundary-matrix.md](/Users/apple/Documents/dujiaoshuka/docs/admin-shell-action-boundary-matrix.md) 记录后台壳动作字段边界
- [dcat-compatibility-layer-audit.md](/Users/apple/Documents/dujiaoshuka/docs/dcat-compatibility-layer-audit.md) 记录 Dcat 最小兼容层保留原因、旧入口清单和删除条件
- [release-stabilization-roadmap.md](/Users/apple/Documents/dujiaoshuka/docs/release-stabilization-roadmap.md) 记录 beta.1、beta.2、RC 和稳定版路线
- [v3.3-payment-security-guardrail-matrix.md](/Users/apple/Documents/dujiaoshuka/docs/v3.3-payment-security-guardrail-matrix.md) 记录 v3.3 保留支付通道、退役通道、安全输入面和升级阻塞面的执行矩阵
- 本文件负责约束接下来连续执行的默认顺序、边界和退出标准

## 当前结论

项目已经完成“抢救可运行”和“后台壳主线建立”两大阶段，现在剩余工作不再按零散任务推进，而是默认按本执行基线里的收口批次推进。

当前最准确的阶段判断：

- 总体进度：约 `90%`
- 后台壳替换：v3.1.0 stable 冻结期，已经是主入口和高频资源主承载，后续只接受真实运营缺口、回归、安全和文档一致性修正
- 前台体验：v3.1.1 补丁和 v3.2 资源拆包已收口；首页、购买页、订单查询页和确认订单页已完成轻量视觉统一，后续只接受可验证的回归、可读性和性能修正
- 旧 Dcat 退场：后期，只剩最小兼容配置与旧路由跳转层
- 支付层现代化：维护期，当前维护范围已收敛为官方支付宝、官方微信、易支付和 Epusdt，其余非核心支付通道已进入退役范围
- 安全治理与升级前清障：进入 v3.3 护栏盘点期，同时保持 3.2 / 4.0 实验准备期边界；已有状态表、依赖阻塞矩阵、护栏矩阵和升级实验命令，后续升级实验不得混入稳定主线

最近一轮后台壳已经连续补齐 `goods / order / pay / coupon / carmis` 多组低风险批量动作，并补强支付回调幂等、签名失败、金额校验、已完成订单重复推进、上传与模板输入边界、后台支付入口防回流、后台壳动作边界矩阵、依赖阻塞矩阵、本地 smoke 凭据边界、CI 工作流护栏、RC.1 验收状态、v3.1.0 遗留边界、release 文档冻结护栏、当前进度文档护栏、当前规划文档护栏、退役支付通道运行时防回流护栏和 PHP 8.1 / Laravel 8 升级护栏；支付范围已裁剪到官方支付宝、官方微信、易支付和 Epusdt，当前 PHPUnit 基线为 `417 tests, 4313 assertions`。前台已追加 `v3.1.1` 体验补丁：移动端购买路径、会员卡物料、支付 tile、订单查询页和确认订单页统一为轻量前台视觉，且不修改下单、查询和支付接口语义。`v3.2` 资源拆包已停载 `vendor.min.js`、`app.min.js`、`bootstrap-input-spinner.js`、`icons.min.css`、`bootstrap.min.js` 和 `bootstrap.min.css`，并由 `hyper.js` 接管轻量通知和交互，由 `avatar.css` 接管前台基础样式，发布候选资源验收和体积记录已完成。`v3.3` 第一批已建立 [支付与安全护栏矩阵](/Users/apple/Documents/dujiaoshuka/docs/v3.3-payment-security-guardrail-matrix.md)，后续只按矩阵补测试缺口和代码护栏，不恢复退役支付通道，不新增支付 SDK。

兼容层本轮已建立 [Dcat 最小兼容层审计](/Users/apple/Documents/dujiaoshuka/docs/dcat-compatibility-layer-audit.md)，明确 `config/admin.php` 和 `routes/admin/routes.php` 仍因登录、认证、中间件、权限白名单、后台壳挂载和旧入口跳转保留；旧 `/admin/*` 入口必须保留 query string，且不得回写业务逻辑。

## 冻结后剩余主线

### 1. 后台壳主承载冻结维护

目标：

- 维持后台壳作为日常运营主承载
- 不再新增后台批量动作，除非是阻断级运营缺口且具备测试、smoke 和字段边界

剩余任务：

- 维护 `goods / order / pay / coupon / carmis` 已有动作的字段边界、测试和 smoke 覆盖
- 只修真实运营阻断、回归、安全问题和文档口径偏差
- 后台支付动作继续禁止批量触碰密钥、商户号、支付路由和回调地址

退出标准：

- `goods / order / pay / coupon / carmis` 的现有低风险运营动作字段边界可被测试和文档复核
- 后台壳 smoke 持续覆盖高频动作入口
- README 和基线文档中的动作页清单与真实代码一致

### 2. 旧 Dcat 最小兼容层收尾

目标：

- 将旧 Dcat 从业务承载层继续压缩为临时兼容壳
- 为后续 Laravel / PHP 升级减少锁定面

剩余任务：

- 审计 [config/admin.php](/Users/apple/Documents/dujiaoshuka/config/admin.php) 中仍需保留的 Dcat 配置项
- 审计 [routes/admin/routes.php](/Users/apple/Documents/dujiaoshuka/routes/admin/routes.php) 中旧 `/admin/*` 跳转是否还能继续瘦身
- 清理文档中已经过时的“旧 Dcat 页面仍是主入口”表述
- 确认 `app/Admin` 退场后没有测试、CI、README 或安装说明继续引用旧路径

退出标准：

- 旧后台兼容层职责明确为“跳转与兼容”
- 没有新业务逻辑回写旧兼容层
- 文档口径统一为“后台壳是主承载，Dcat 是过渡兼容”

### 3. 支付层现代化收口

目标：

- 支付层生命周期清晰，可保留、可观察、可退役的通道边界稳定
- 后续升级不再被旧支付 SDK 或历史通道反向拖住

剩余任务：

- 保留官方支付宝、官方微信、易支付和 Epusdt，继续做回调安全专项
- 对退役通道保持不可新增、不可恢复、不可进入样例种子的约束
- 覆盖签名失败、重复通知、金额不一致、订单状态重复推进等异常路径
- 更新支付通道文档，把非核心通道明确为新版本不维护通道

退出标准：

- 支付通道生命周期文档与代码一致
- 退役通道不再存在主线路由、控制器、服务、依赖或样例种子
- 支付回调关键异常路径有测试护栏
- [v3.3 支付与安全护栏矩阵](/Users/apple/Documents/dujiaoshuka/docs/v3.3-payment-security-guardrail-matrix.md) 中的状态标注与代码、测试和文档一致

### 4. 安全治理专项

目标：

- 把默认项、后台、支付回调、密钥和仓库卫生从“随手清理”升级成专项清单

剩余任务：

- 默认账号、默认密钥、`.env.example` 和安装默认值复查
- 后台权限边界复查，重点是批量动作、导入、配置编辑和账号设置
- 上传、模板输入、邮件发送、通知配置等输入面复查
- CI、GitHub secret scanning、历史敏感信息和文档示例值复查

退出标准：

- 有安全治理清单，并为每项标注已处理、保留、后置或需人工配置
- GitHub 不再持续报告已知敏感样例误报
- 高风险默认值不进入生产安装路径

### 5. 升级前清障

目标：

- 不是立刻跳 Laravel / PHP 大版本，而是先让升级路径可执行

剩余任务：

- 刷新依赖阻塞矩阵，标记哪些已处理、哪些仍阻塞
- 继续降低 Laravel 6 / Dcat / 老包对业务主链的绑定
- 明确第一阶段升级目标，是先 PHP 小步升级，还是先 Laravel 版本桥接
- 建立升级实验分支的最小验证命令集

退出标准：

- 有一条现实的升级路线，而不是笼统的“升级 Laravel”
- 关键阻塞依赖和业务风险已经列清
- 可以在实验分支开始第一轮升级验证

## 默认执行顺序

### 第一批：后台壳收口

1. 卡密线先收口 `batch-suffix`：视图、路由、列表入口、Feature / Unit / smoke 和日志全部补齐
2. 商品线补展示/描述类低风险批量动作
3. 卡密线继续补导入后维护动作和低风险批量入口
4. 订单线补人工维护/展示辅助动作
5. 优惠码和支付线只继续补文本展示类动作
6. 同步 README、当前基线审计和 smoke 覆盖清单

### 第二批：兼容层与文档收口

1. 清理旧 Dcat 兼容口径
2. 审计 `config/admin.php` 与 `routes/admin/routes.php`，维护 [Dcat 最小兼容层审计](/Users/apple/Documents/dujiaoshuka/docs/dcat-compatibility-layer-audit.md)
3. 更新当前基线审计、进度总汇、后台迁移优先级清单
4. 确认无旧 `app/Admin` 生产目录残留，历史文档引用必须标注为历史快照

### 第三批：支付与安全专项

1. 以 [v3.3 支付与安全护栏矩阵](/Users/apple/Documents/dujiaoshuka/docs/v3.3-payment-security-guardrail-matrix.md) 作为默认任务来源
2. 支付回调安全测试补强只覆盖矩阵中标记的缺口，不修改公开支付语义
3. 支付通道退役文档与代码、依赖、样例种子继续对齐
4. 默认项与密钥治理继续执行 secret-shape 搜索和示例值复核
5. 后台批量动作权限与输入边界复查优先覆盖上传、模板输入、邮件发送和通知配置

### 第四批：升级前清障

1. 刷新依赖阻塞矩阵
2. 设计 PHP / Laravel 分步升级路线
3. 建立升级实验分支验证清单
4. 进入第一轮升级试跑

### 第五批：v3.2 前台资源清理

1. 维护 `v3.1.1` 前台接口冻结边界，不再继续叠加纯视觉补丁
2. 第一批停载 `vendor.min.js / app.min.js / bootstrap-input-spinner.js`，由轻量脚本补齐通知、modal 和 tab 行为
3. 第二批停载 `icons.min.css` 并替换前台残余字体图标
4. 第三批停载 `bootstrap.min.js`，由轻量脚本补齐 modal 行为
5. 第四批评估 `Bootstrap CSS` 的真实依赖和可替代边界，输出 [Avatar 前台 Bootstrap CSS 依赖矩阵](/Users/apple/Documents/dujiaoshuka/docs/avatar-bootstrap-css-dependency-matrix.md)
6. 第五批用 `avatar.css` 接管前台可替代样式，并在页面验证后停止 `avatar` 前台新页面引用 `bootstrap.min.css`
7. 第六批执行发布候选资源验收，记录最终请求、体积和历史文件保留原因，输出 [v3.2.0 前台资源发布候选](/Users/apple/Documents/dujiaoshuka/docs/releases/v3.2.0.md)
8. 每批都确认购买页支付方式、订单查询 tab、首页公告、前台通知和页面渲染不回归
9. 发布候选前每批都跑页面渲染检查、浏览器交互检查、后台壳 smoke 和 `git diff --check`

## 近期 10 个可直接执行的任务

1. Release 线：保持 v3.1.1 文档的测试记录、支付范围、前台接口边界和冻结边界一致
2. CI 线：等待或复核远端 CI，确认本地验证与远端验证一致
3. 安全线：维护上传、模板输入、邮件发送、通知配置和后台配置编辑输入面审计记录
4. 支付线：保留通道只做回调安全维护，退役通道继续防回流
5. 升级线：3.2 / 4.0 升级实验必须单独分支执行，不混入 v3.1.1 收口
6. 兼容层线：继续保留 Dcat 最小兼容层，不在稳定版前删除关键入口
7. 文档线：发现旧进度百分比、beta.1 扩容或恢复退役支付通道口径时立即修正
8. 测试线：新增回归必须同步定向测试和全量 PHPUnit
9. 部署线：只修安装、部署、CI 文档和验证命令偏差
10. 发布线：最终发版前再次执行 diff check、全量 PHPUnit、后台 smoke 和 release 搜索复核

## 暂不建议做的任务

- 不建议立刻删除 `config/admin.php` 和 `routes/admin/routes.php`
- 不建议立刻整体替换后台权限体系
- 不建议立刻跳 Laravel 大版本
- 不建议新增高风险批量动作，例如批量改价格、库存、订单状态推进、支付密钥、支付路由
- 不建议再为已退役通道补新功能

## 每轮执行规则

- 优先选择低风险、可预览、可回滚、可测试的动作页
- 每轮只碰一个资源线或一个明确文档线
- 每轮都更新 [refactor-upgrade-log.md](/Users/apple/Documents/dujiaoshuka/docs/refactor-upgrade-log.md) 或相关基线文档
- 每轮至少跑定向 PHPUnit；稳定节点跑全量 PHPUnit 和后台壳 smoke
- 每轮结束让常驻基线监控子代理判断是否严重偏离
- 无严重偏离则提交并推送 `master`

## 常驻监控判断规则

常驻基线监控子代理只判断是否严重偏离本执行基线，不替代主线程做实现细节决策。

默认允许继续：

- 维护后台壳低风险动作页
- 继续移除旧 Dcat 的业务承载理由
- 继续补支付、安全、升级前清障中的测试和文档护栏
- 继续做 README、当前审计、进度总汇与本文件之间的口径同步

需要提醒但不一定停止：

- 单轮改动跨越多个资源线
- 一次性触及批量动作、权限、支付回调或配置写入等敏感面
- 文档计划与真实代码状态出现明显不一致

需要暂停并向用户说明：

- 准备删除旧兼容层关键入口
- 准备修改价格、库存、支付密钥、订单状态推进或履约链路
- 准备跳 Laravel / PHP 大版本
- 验证发现非平凡回归
- 需要外部密钥、账号、线上信息或不可自动推断的产品取舍
