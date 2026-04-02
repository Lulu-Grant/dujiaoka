# 重构升级日志

## 记录规则

- 本文档用于记录本仓库现代化改造过程中的重要节点。
- 重要节点包括：评估结论、运行时基线恢复、测试体系建立、架构拆分、部署模型变更、安全治理、升级阻塞点确认、重要回归修复。
- 每次进入新的重要节点时，都应在本文档追加一条记录。
- 每条记录尽量包含：日期、阶段、变更摘要、影响范围、验证结果、下一步。

---

## 2026-04-02 阶段日志

### 1. 完成第一轮重大评估

摘要：

- 确认项目属于高业务价值但高技术债的遗留 Laravel 单体。
- 确认核心问题集中在旧运行时、旧依赖、支付耦合、安装方式、测试缺失和部署模式。
- 明确不适合直接跳升到现代 Laravel，应先做基线恢复、测试护栏和核心服务拆分。

影响范围：

- 架构路线
- 风险分级
- 后续重构顺序

产出：

- [modernization-roadmap.md](/Users/apple/Documents/dujiaoshuka/docs/modernization-roadmap.md)
- [project-audit-notes.md](/Users/apple/Documents/dujiaoshuka/docs/project-audit-notes.md)
- [runtime-compatibility-blockers.md](/Users/apple/Documents/dujiaoshuka/docs/runtime-compatibility-blockers.md)

验证：

- 基于仓库结构、依赖清单、安装流程、服务实现完成静态审计。

下一步：

- 恢复可运行的遗留运行时基线。

### 2. 恢复遗留运行时基线

摘要：

- 安装 Composer 与 PHP 7.4，避免直接用本机 PHP 8.5 硬跑 Laravel 6。
- 建立 `scripts/php74` 和 `scripts/composer74`，固定遗留运行时入口。
- 启动 MariaDB 与 Redis，导入 `dujiaoka` / `dujiaoka_test` 数据库。
- 建立 `install.lock`，让项目进入已安装状态。

影响范围：

- 本地开发基线
- 测试执行基线
- 后续升级验证路径

产出：

- [legacy-runtime-baseline.md](/Users/apple/Documents/dujiaoshuka/docs/legacy-runtime-baseline.md)
- [scripts/php74](/Users/apple/Documents/dujiaoshuka/scripts/php74)
- [scripts/composer74](/Users/apple/Documents/dujiaoshuka/scripts/composer74)

验证：

- PHP 7.4 下可执行 Artisan 与 PHPUnit。

下一步：

- 建立真实业务测试护栏。

### 3. 完成仓库与敏感配置基础治理

摘要：

- 将已跟踪的 [`.env`](/Users/apple/Documents/dujiaoshuka/.env) 从 git 跟踪中移除，但保留本地文件。
- 明确当前仓库不应继续把真实环境文件当作版本内容管理。
- 确认安装 SQL 内含默认后台数据，后续需继续推进密钥和默认凭据治理。

影响范围：

- 仓库卫生
- 安全治理起点

验证：

- `git status` 中 `.env` 已从版本跟踪中移除。

下一步：

- 持续推进安装与默认数据的安全治理。

### 4. 建立第一批核心业务测试护栏

摘要：

- 从只有示例测试，推进到覆盖订单主链的真实单元测试。
- 覆盖场景包括：下单创建、自动发货成功、自动发货异常、优惠码使用、优惠码回退、订单查询、订单状态查询、支付前校验、订单过期。
- 后续继续补入批发价计算与订单状态邮件监听器测试。

影响范围：

- 订单主链
- 支付前校验
- 优惠码生命周期
- 订单过期与查询

主要测试文件：

- [OrderProcessServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/OrderProcessServiceTest.php)
- [OrderServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/OrderServiceTest.php)
- [OrderStatusControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/OrderStatusControllerTest.php)
- [PayControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/PayControllerTest.php)
- [OrderExpiredJobTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/OrderExpiredJobTest.php)
- [ExpireOrdersCommandTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/ExpireOrdersCommandTest.php)
- [OrderCreationServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/OrderCreationServiceTest.php)
- [OrderUpdatedListenerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/OrderUpdatedListenerTest.php)

验证：

- 当前全量回归结果为：
  `./scripts/php74 vendor/bin/phpunit`
- 最新结果：`OK (19 tests, 67 assertions)`

下一步：

- 继续把大服务拆成可演进的领域服务。

### 5. 完成“去守护进程”第一阶段改造

摘要：

- 移除“订单超时依赖延迟队列”的设计，改为命令扫描过期订单。
- 新增 [ExpireOrdersCommand.php](/Users/apple/Documents/dujiaoshuka/app/Console/Commands/ExpireOrdersCommand.php)，并接入 Laravel scheduler。
- 新增同步优先的副作用分发机制，默认不需要常驻 `queue:work`。
- 更新 Dockerfile、Debian 手册和 docker-compose，使部署不再依赖项目自带的 `supervisord` / 后台队列 worker。

影响范围：

- 订单过期机制
- 通知与回调执行模型
- Docker 部署方式
- Linux 部署文档

产出：

- [no-daemon-migration-checklist.md](/Users/apple/Documents/dujiaoshuka/docs/no-daemon-migration-checklist.md)
- [SideEffectDispatcherService.php](/Users/apple/Documents/dujiaoshuka/app/Service/SideEffectDispatcherService.php)
- [ExpireOrdersCommand.php](/Users/apple/Documents/dujiaoshuka/app/Console/Commands/ExpireOrdersCommand.php)

验证：

- 订单过期与通知相关测试通过。
- 当前部署说明已改为 `schedule:run` / `orders:expire` 模式。

下一步：

- 继续消减代码与文档里的队列中心化假设。

### 6. 完成订单创建与通知层的第一轮服务拆分

摘要：

- 将订单创建中的价格计算和待支付订单组装提取到 [OrderCreationService.php](/Users/apple/Documents/dujiaoshuka/app/Service/OrderCreationService.php)。
- 将订单完成后的通知、状态邮件和模板发送提取到 [OrderNotificationService.php](/Users/apple/Documents/dujiaoshuka/app/Service/OrderNotificationService.php)。
- [OrderProcessService.php](/Users/apple/Documents/dujiaoshuka/app/Service/OrderProcessService.php) 进一步收敛为订单流程编排器。
- [OrderUpdated.php](/Users/apple/Documents/dujiaoshuka/app/Listeners/OrderUpdated.php) 不再直接拼模板和派邮件，统一走通知服务。

影响范围：

- 订单创建逻辑
- 订单通知逻辑
- 订单状态更新监听器

验证：

- 新增独立服务测试与监听器测试。
- 当前全量回归结果：`OK (19 tests, 67 assertions)`

下一步：

- 继续拆出履约层，把 `processAuto()` / `processManual()` 提升为独立 fulfillment service。

### 7. 完成订单履约层第一轮服务拆分

摘要：

- 将手动履约与自动发货逻辑提取到 [OrderFulfillmentService.php](/Users/apple/Documents/dujiaoshuka/app/Service/OrderFulfillmentService.php)。
- [OrderProcessService.php](/Users/apple/Documents/dujiaoshuka/app/Service/OrderProcessService.php) 不再直接操作卡密与库存履约细节，而是统一委托履约服务。
- 为履约服务新增独立测试，直接守住手动履约与自动履约行为。

影响范围：

- 订单履约逻辑
- 自动发货逻辑
- 手动处理逻辑

验证：

- 新增 [OrderFulfillmentServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/OrderFulfillmentServiceTest.php)
- 当前全量回归结果：`OK (21 tests, 78 assertions)`

下一步：

- 继续压缩 `OrderProcessService`，考虑把支付完成编排也抽成更明确的支付确认 / 状态流转层。

### 8. 完成支付完成编排第一轮服务拆分

摘要：

- 将支付成功后的核心状态流转提取到 [OrderPaymentService.php](/Users/apple/Documents/dujiaoshuka/app/Service/OrderPaymentService.php)。
- 新服务负责：校验订单是否可完成支付、应用支付结果、调用履约服务、增加销量。
- [OrderProcessService.php](/Users/apple/Documents/dujiaoshuka/app/Service/OrderProcessService.php) 进一步退化为更薄的门面，只保留事务边界和支付完成后的统一通知派发。

影响范围：

- 支付完成主链
- 订单状态流转
- 支付完成后的履约入口

验证：

- 新增 [OrderPaymentServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/OrderPaymentServiceTest.php)
- 当前全量回归结果：`OK (22 tests, 82 assertions)`

下一步：

- 继续收敛 `OrderProcessService` 的可变输入状态，评估是否引入明确的 order command / DTO 来替代多组 setter。

### 9. 完成创建订单输入对象化改造

摘要：

- 新增 [CreateOrderData.php](/Users/apple/Documents/dujiaoshuka/app/Service/DataTransferObjects/CreateOrderData.php)，作为创建订单的明确输入对象。
- [OrderProcessService.php](/Users/apple/Documents/dujiaoshuka/app/Service/OrderProcessService.php) 新增 `createOrderFromData()`，将 DTO 作为主路径。
- [OrderController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Home/OrderController.php) 已切换到 DTO 调用链，不再依赖多组 setter 组装下单上下文。
- 旧 setter 入口仍暂时保留，仅用于兼容过渡。

影响范围：

- 订单创建入口
- 控制器到服务层的数据传递方式
- `OrderProcessService` 的可变状态模型

验证：

- 新增 [CreateOrderDataFlowTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/CreateOrderDataFlowTest.php)
- 当前全量回归结果：`OK (23 tests, 90 assertions)`

下一步：

- 评估是否可以彻底删除旧 setter 接口，并继续把控制器验证输出也收敛为更明确的应用层命令对象。

### 10. 完成旧 setter 兼容层下线

摘要：

- 已从 [OrderProcessService.php](/Users/apple/Documents/dujiaoshuka/app/Service/OrderProcessService.php) 删除旧的 setter 接口与无参 `createOrder()` 兼容路径。
- 订单创建已完全切换为 `CreateOrderData` 输入对象模型。
- `OrderProcessService` 不再持有创建订单所需的可变内部状态，职责边界进一步清晰。

影响范围：

- 订单创建服务接口
- 过渡兼容层
- 旧可变状态 API

验证：

- 搜索确认仓库内已无生产代码调用旧 setter 下单接口
- 当前全量回归结果：`OK (23 tests, 90 assertions)`

下一步：

- 继续评估控制器验证与应用层命令建模，逐步把 legacy request-to-service 粘连再往外拆。
