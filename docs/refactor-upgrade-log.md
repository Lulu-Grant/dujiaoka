# 重构升级日志

## 记录规则

- 本文档用于记录本仓库现代化改造过程中的重要节点。
- 重要节点包括：评估结论、运行时基线恢复、测试体系建立、架构拆分、部署模型变更、安全治理、升级阻塞点确认、重要回归修复。
- 每次进入新的重要节点时，都应在本文档追加一条记录。
- 每条记录尽量包含：日期、阶段、变更摘要、影响范围、验证结果、下一步。

---

## 2026-04-02 阶段日志

### 0. 建立大整改执行方案并切换为按阶段连续推进

摘要：

- 新增 [rectification-execution-plan.md](/Users/apple/Documents/dujiaoshuka/docs/rectification-execution-plan.md)，把整改目标、阶段、退出标准、同步规则和默认执行顺序固定下来。
- 明确后续默认按阶段连续执行，不再在每个小节点停下来等待确认。
- 继续保留“重要节点必须记日志、稳定节点应同步 GitHub `master`”的节奏，避免改造失去可追踪性。

影响范围：

- 整体整改节奏
- 任务执行方式
- 文档化治理规则

验证：

- 方案文档已落库，并已接入 [modernization-roadmap.md](/Users/apple/Documents/dujiaoshuka/docs/modernization-roadmap.md)。

下一步：

- 按方案继续执行支付层整治收口，优先清理剩余的支付控制器。

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

### 11. 完成订单创建控制器到应用服务的第一轮收口

摘要：

- 新增 [OrderCheckoutService.php](/Users/apple/Documents/dujiaoshuka/app/Service/OrderCheckoutService.php)，承接“请求校验 -> 组装 CreateOrderData -> 创建订单”的应用层流程。
- [OrderController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Home/OrderController.php) 的创建订单动作已改为直接调用结账应用服务。
- 控制器厚度进一步降低，订单创建的请求到领域服务入口之间有了更明确的应用服务边界。

影响范围：

- 创建订单控制器逻辑
- 请求校验与 DTO 组装职责划分
- 控制器到服务层的调用路径

验证：

- 新增 [OrderCheckoutServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/OrderCheckoutServiceTest.php)
- 当前全量回归结果：`OK (24 tests, 95 assertions)`

下一步：

- 继续梳理控制器层的重复查询与展示逻辑，评估是否需要为订单查询侧也引入更明确的应用服务。

### 12. 完成订单查询控制器到应用服务的第一轮收口

摘要：

- 新增 [OrderQueryService.php](/Users/apple/Documents/dujiaoshuka/app/Service/OrderQueryService.php)，承接结账页订单获取、订单详情查询、状态轮询响应、邮箱查询和浏览器缓存查询。
- [OrderController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Home/OrderController.php) 的查询侧逻辑已大幅收缩，控制器主要保留响应渲染和错误输出。
- 查询侧的异常消息与状态码逻辑开始从控制器分离，控制器职责进一步清晰。

影响范围：

- 订单详情展示
- 订单状态轮询
- 按邮箱查询
- 按浏览器缓存查询

验证：

- 新增 [OrderQueryServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/OrderQueryServiceTest.php)
- 当前全量回归结果：`OK (27 tests, 100 assertions)`

下一步：

- 继续盘点首页下单控制器之外的旧式控制器，优先评估支付入口和安装流程哪些适合先抽应用服务层。

### 13. 完成支付入口控制器到应用服务的第一轮收口

摘要：

- 新增 [PayEntryService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PayEntryService.php)，承接支付前订单校验、支付网关加载和零元订单直达完成逻辑。
- [PayController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/PayController.php) 已改为主要委托支付入口应用服务。
- 为支付入口补充了零元订单直达完成的测试覆盖。

影响范围：

- 支付前订单校验
- 支付网关加载
- 0 元订单直接完成路径

验证：

- 扩展 [PayControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/PayControllerTest.php)
- 当前全量回归结果：`OK (28 tests, 102 assertions)`

下一步：

- 继续评估各支付网关控制器，优先寻找可抽出的公共回调校验 / 完成支付入口，逐步为支付层建立更明确的网关抽象。

### 14. 完成 PayPal 回调样板服务拆分并修复取消支付跳转问题

摘要：

- 新增 [PaypalReturnService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaypalReturnService.php)，承接 PayPal 同步回调中的订单/网关校验与 API context 构建。
- [PaypalPayController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Pay/PaypalPayController.php) 已开始把回调校验逻辑委托给应用服务。
- 修复了 PayPal 取消支付时跳转详情页使用错误参数的问题，原先错误使用 `PayerID`，现已改为正确使用 `orderSN`。

影响范围：

- PayPal 同步回调入口
- PayPal 回调上下文校验
- PayPal 取消支付后的回跳行为

验证：

- 新增 [PaypalPayControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/PaypalPayControllerTest.php)
- 当前全量回归结果：`OK (30 tests, 104 assertions)`

下一步：

- 继续挑选下一个典型支付控制器，优先抽出更通用的“回调上下文校验 + 完成支付入口”模式，逐步逼近统一网关抽象。

### 15. 完成支付回调公共上下文服务抽取，并以 Paysapi 作为第二个网关样板

摘要：

- 新增 [PaymentCallbackService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaymentCallbackService.php)，统一承接“按订单号解析订单与支付网关，并校验 handleroute”的回调上下文逻辑。
- [PaypalReturnService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaypalReturnService.php) 已改为复用回调公共服务。
- [PaysapiController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Pay/PaysapiController.php) 已开始把回调上下文解析和完成支付入口委托给公共服务，自身只保留 Paysapi 特有的签名校验。

影响范围：

- 支付回调公共上下文校验
- Paysapi 异步通知处理
- PayPal 回调服务的公共化依赖

验证：

- 新增 [PaysapiControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/PaysapiControllerTest.php)
- 当前全量回归结果：`OK (32 tests, 108 assertions)`

下一步：

- 继续选择下一个异步通知型网关，把更多“签名校验之外的通用流程”抽到支付层公共服务中，逐步逼近统一网关适配模型。

### 16. 完成 Yipay 第三个异步通知型网关样板接入

摘要：

- [YipayController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Pay/YipayController.php) 已接入 [PaymentCallbackService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaymentCallbackService.php)。
- 现在 Yipay 回调也开始复用统一的订单/网关上下文解析与完成支付入口。
- 控制器自身继续保留易支付特有的签名规则，公共流程不再重复手写。

影响范围：

- 易支付异步通知回调
- 支付回调公共服务的复用范围
- 支付控制器的重复逻辑收敛

验证：

- 新增 [YipayControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/YipayControllerTest.php)
- 当前全量回归结果：`OK (34 tests, 112 assertions)`

下一步：

- 继续选择下一个通知型网关，或开始抽取更上层的“签名计算策略 / 网关适配接口”，让支付层从公共流程抽取逐步进入统一抽象阶段。

### 17. 完成异步通知型网关的统一通知骨架抽取

摘要：

- [PaymentCallbackService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaymentCallbackService.php) 新增 `handleSignedNotification()`，统一承接“回调上下文解析 -> 验签回调 -> 完成订单 -> 返回结果”的公共流程。
- [PaysapiController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Pay/PaysapiController.php) 与 [YipayController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Pay/YipayController.php) 已改为复用这一统一入口。
- 新增 [PaymentCallbackServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/PaymentCallbackServiceTest.php)，给支付回调公共服务本身补上了独立护栏。

影响范围：

- 支付回调公共服务抽象层
- Paysapi 异步通知
- Yipay 异步通知
- 支付完成入口的统一复用

验证：

- 新增 [PaymentCallbackServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/PaymentCallbackServiceTest.php)
- 当前全量回归结果：`OK (36 tests, 116 assertions)`

下一步：

- 继续评估是否引入更明确的网关适配接口，或者继续将更多通知型控制器接入统一通知骨架。

### 18. 完成 Vpay 第四个通知型网关样板接入并修正 handleroute 校验

摘要：

- [VpayController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Pay/VpayController.php) 已接入 [PaymentCallbackService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaymentCallbackService.php) 的统一通知骨架。
- 修复了 Vpay 回调里 `pay_handleroute` 校验缺少前导 `/` 的问题，避免因路由字符串不一致导致合法通知被错误拒绝。
- 支付回调统一骨架的复用范围进一步扩大到第四个典型网关。

影响范围：

- Vpay 异步通知回调
- 支付回调统一骨架的覆盖范围
- Vpay handleroute 校验一致性

验证：

- 新增 [VpayControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/VpayControllerTest.php)
- 当前全量回归结果：`OK (38 tests, 120 assertions)`

下一步：

- 继续决定是扩更多网关到统一骨架，还是开始抽象更明确的网关适配协议层。

### 19. 完成 TokenPay 与 Epusdt 通知型网关接入统一回调骨架

摘要：

- [TokenPayController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Pay/TokenPayController.php) 与 [EpusdtController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Pay/EpusdtController.php) 已改为复用 [PaymentCallbackService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaymentCallbackService.php) 的 `handleSignedNotification()`。
- 两个控制器现在都只保留各自网关特有的签名算法，回调上下文解析、handleroute 校验与支付完成入口不再重复实现。
- 顺手修正了两个控制器里缺少前导 `/` 的 `pay_handleroute` 校验历史问题，继续收敛旧支付代码中的路由字符串不一致风险。

影响范围：

- TokenPay 异步通知回调
- Epusdt 异步通知回调
- 支付回调统一骨架的覆盖范围
- handleroute 校验一致性

验证：

- 新增 [TokenPayControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/TokenPayControllerTest.php)
- 新增 [EpusdtControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/EpusdtControllerTest.php)
- 当前全量回归结果：`OK (42 tests, 128 assertions)`

下一步：

- 继续评估其余支付控制器，决定是继续扩大统一骨架的覆盖范围，还是开始提取更显式的支付网关适配接口。

### 20. 完成 Mapay 通知型网关接入统一回调骨架

摘要：

- [MapayController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Pay/MapayController.php) 已改为复用 [PaymentCallbackService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaymentCallbackService.php) 的 `handleSignedNotification()`。
- Mapay 控制器现在只保留自身的签名串计算规则，订单解析、网关校验和支付完成入口都统一收敛到公共服务层。
- 这样支付层统一回调骨架已经覆盖到第七个典型通知型网关，控制器重复逻辑继续下降。

影响范围：

- Mapay 异步通知回调
- 支付回调统一骨架的覆盖范围
- 支付控制器重复逻辑收敛

验证：

- 新增 [MapayControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/MapayControllerTest.php)
- 当前全量回归结果：`OK (44 tests, 132 assertions)`

下一步：

- 继续挑选剩余支付控制器，评估是否进一步抽出更明确的支付网关适配接口。

### 21. 完成 Coinbase Webhook 服务化改造

摘要：

- 新增 [CoinbaseWebhookService.php](/Users/apple/Documents/dujiaoshuka/app/Service/CoinbaseWebhookService.php)，把 Coinbase 回调中的 payload 解析、签名校验、币种校验、金额校验和完成支付逻辑从控制器中抽出。
- [CoinbaseController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Pay/CoinbaseController.php) 不再直接读取 `php://input` 和 `$_SERVER`，改为使用 Request 内容与 header，便于测试和后续统一接入。
- 顺手修正了 Coinbase 回调里 `pay_handleroute` 历史缺少前导 `/` 的问题，继续减少路由字符串不一致带来的误判。

影响范围：

- Coinbase webhook 回调
- 支付层服务化边界
- Request 驱动的可测试性

验证：

- 新增 [CoinbaseWebhookServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/CoinbaseWebhookServiceTest.php)
- 当前全量回归结果：`OK (46 tests, 136 assertions)`

下一步：

- 继续评估 Payjs / Alipay 这类第三方 SDK 型回调，决定是继续抽 webhook 服务，还是进一步提炼统一网关适配层。

### 22. 完成 Payjs 回调服务化改造

摘要：

- 新增 [PayjsNotificationService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PayjsNotificationService.php)，将 Payjs 回调里的上下文解析、配置注入和完成支付编排从控制器中抽离。
- [PayjsController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Pay/PayjsController.php) 现在只保留入口职责，第三方 SDK 回调开始进入“控制器薄壳 + 服务编排”的统一模式。
- 这一步同时打通了第三方 facade 型支付回调的可测试路径，给后续 `Alipay` 等网关改造提供样板。

影响范围：

- Payjs 回调
- 第三方 SDK 型支付回调的服务化模式
- 支付层可测试性

验证：

- 新增 [PayjsNotificationServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/PayjsNotificationServiceTest.php)
- 新增 [PayjsControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/PayjsControllerTest.php)
- 当前全量回归结果：`OK (49 tests, 141 assertions)`

下一步：

- 沿用这套模式继续处理 Alipay / Wepay 这类 SDK 型回调，逐步统一支付层编排。

### 23. 完成 Alipay 回调服务化改造

摘要：

- 新增 [AlipayNotificationService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AlipayNotificationService.php)，把 Alipay 回调中的上下文解析、SDK 客户端创建、签名验证和支付完成编排从控制器中抽离。
- [AlipayController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Pay/AlipayController.php) 现在只保留回调入口职责，继续向“控制器薄壳 + 服务编排”的统一支付模式收敛。
- 这一步为后续处理 `Wepay` 提供了几乎同构的样板。

影响范围：

- Alipay 回调
- SDK 型支付通知服务化模式
- 支付层可测试性

验证：

- 新增 [AlipayNotificationServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AlipayNotificationServiceTest.php)
- 新增 [AlipayControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AlipayControllerTest.php)
- 当前全量回归结果：`OK (52 tests, 146 assertions)`

下一步：

- 继续用同样模式处理 Wepay，并开始整理支付网关整改清单。

### 24. 完成 Wepay 回调服务化改造

摘要：

- 新增 [WepayNotificationService.php](/Users/apple/Documents/dujiaoshuka/app/Service/WepayNotificationService.php)，把 Wepay 回调中的上下文解析、SDK 客户端创建、签名验证和支付完成编排从控制器中抽离。
- [WepayController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Pay/WepayController.php) 进一步瘦身，现在只负责从 XML 中提取订单号并委托通知服务。
- 到这一步，Yansongda 这类 SDK 型支付回调已经建立起可复用的统一服务化模式。

影响范围：

- Wepay 回调
- 微信支付 SDK 型通知服务化模式
- 支付层服务边界清晰度

验证：

- 新增 [WepayNotificationServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/WepayNotificationServiceTest.php)
- 新增 [WepayControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/WepayControllerTest.php)
- 当前全量回归结果：待本轮测试更新

下一步：

- 开始整理支付网关整改清单，并评估 Stripe / Paypal 等更重网关的下一步抽象路线。

### 25. 建立支付网关整改清单

摘要：

- 新增 [payment-gateway-remediation-inventory.md](/Users/apple/Documents/dujiaoshuka/docs/payment-gateway-remediation-inventory.md)，把当前支付网关按“已接入统一通知骨架 / 已服务化 / 仍为旧式实现 / 高风险”分类整理。
- 明确 `Stripe` 和 `Paypal` 是当前支付层剩余的最高风险区域，后续应作为阶段 A 的重点清障对象。
- 阶段 A 从“持续重构中”进一步升级为“有执行面板、有状态盘点、有默认下一步”的可追踪状态。

影响范围：

- 支付层整改追踪方式
- 阶段 A 的优先级判断
- 后续 Stripe / Paypal 整改路线

验证：

- 新增 [payment-gateway-remediation-inventory.md](/Users/apple/Documents/dujiaoshuka/docs/payment-gateway-remediation-inventory.md)
- 当前全量回归结果：`OK (55 tests, 151 assertions)`

下一步：

- 继续处理 Paypal 主流程，并准备对 Stripe 进行更大颗粒度的拆解。

### 26. 完成 Paypal 同步返回完成支付服务化

摘要：

- [PaypalReturnService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaypalReturnService.php) 已从“只提供上下文和 ApiContext”升级为真正承接 Paypal 同步返回完成支付的应用服务。
- [PaypalPayController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Pay/PaypalPayController.php) 的 `returnUrl()` 不再自己持有支付执行细节，而是只负责取消分支、结果跳转和日志记录。
- 这样 Paypal 主流程已进一步向“控制器薄壳 + 服务编排”的模式收敛，为后续继续拆支付创建与异步通知打下基础。

影响范围：

- Paypal 同步返回支付完成路径
- Paypal 控制器职责边界
- 支付层服务化一致性

验证：

- 新增 [PaypalReturnServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/PaypalReturnServiceTest.php)
- 当前全量回归结果：`OK (58 tests, 158 assertions)`

下一步：

- 继续评估 Paypal 支付创建路径，并开始着手 Stripe 的分块拆解。

### 27. 完成 Paypal 支付创建服务化

摘要：

- 新增 [PaypalCheckoutService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaypalCheckoutService.php)，把 Paypal 支付创建中的汇率转换、交易对象构建和 approval link 生成从控制器中抽离。
- [PaypalPayController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Pay/PaypalPayController.php) 的 `gateway()` 已进一步瘦身，现在主要只负责入口校验、委托服务和错误响应。
- 到这一步，Paypal 的“支付创建 + 同步返回完成支付”主流程都已进入服务化轨道。

影响范围：

- Paypal 支付创建路径
- Paypal 控制器职责边界
- 支付层服务化一致性

验证：

- 新增 [PaypalCheckoutServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/PaypalCheckoutServiceTest.php)
- 当前全量回归结果：`OK (58 tests, 158 assertions)`

下一步：

- 开始进入 Stripe 的第一轮拆分，并继续收敛 Paypal 剩余通知路径。

### 28. 完成 Stripe 支付完成路径第一轮服务化

摘要：

- 新增 [StripePaymentService.php](/Users/apple/Documents/dujiaoshuka/app/Service/StripePaymentService.php)，将 Stripe 的 `returnUrl`、`check`、`charge` 三条完成支付路径中的上下文解析、Source/Charge 调用和完成订单编排从控制器中抽出。
- [StripeController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Pay/StripeController.php) 已不再直接承担核心状态流转责任，开始向“超长页面渲染 + 轻入口委托”的中间状态收敛。
- 这一步优先把最危险的支付完成逻辑从 540 行旧控制器中剥离出来，为后续继续拆 Stripe 的页面渲染与汇率/创建逻辑建立基础。

影响范围：

- Stripe 返回支付路径
- Stripe 轮询检查路径
- Stripe 卡支付路径
- Stripe 控制器职责边界

验证：

- 新增 [StripePaymentServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/StripePaymentServiceTest.php)
- 新增 [StripeControllerTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/StripeControllerTest.php)
- 当前全量回归结果：`OK (63 tests, 167 assertions)`

下一步：

- 继续拆 Stripe 的页面渲染与汇率/支付创建逻辑，并评估是否将内联 HTML 提取为视图模板。

### 29. 完成 Stripe 汇率与收银页视图第一轮拆分

摘要：

- 新增 [StripeCurrencyService.php](/Users/apple/Documents/dujiaoshuka/app/Service/StripeCurrencyService.php)，把美元汇率获取逻辑从控制器中抽离。
- 新增 [StripeCheckoutService.php](/Users/apple/Documents/dujiaoshuka/app/Service/StripeCheckoutService.php)，把 Stripe 收银页所需的数据组装从控制器中抽离。
- 新增 [stripe/checkout.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/stripe/checkout.blade.php)，将原先嵌在控制器里的超长内联 HTML 收银页提取为独立视图。
- [StripeController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Pay/StripeController.php) 已从“大字符串生成器”进一步退化为入口控制器，代码体积和职责复杂度明显下降。

影响范围：

- Stripe 汇率获取逻辑
- Stripe 收银页渲染方式
- Stripe 支付创建页面数据组装
- Stripe 控制器体积与可维护性

验证：

- 新增 [StripeCurrencyServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/StripeCurrencyServiceTest.php)
- 新增 [StripeCheckoutServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/StripeCheckoutServiceTest.php)
- 当前全量回归结果：`OK (65 tests, 172 assertions)`

下一步：

- 继续清理 Stripe 剩余耦合点，并开始评估阶段 A 是否可以准备收口转入下一阶段。

### 30. 启动数据库现代化并落下第一批核心表迁移骨架

摘要：

- 新增 [database-modernization-plan.md](/Users/apple/Documents/dujiaoshuka/docs/database-modernization-plan.md)，把 `install.sql -> migrations + seeders` 的拆解顺序和原则固定下来。
- 新建 `database/migrations`，并把核心商业主链表的第一批迁移骨架落地：
  - [2026_04_02_000001_create_goods_group_table.php](/Users/apple/Documents/dujiaoshuka/database/migrations/2026_04_02_000001_create_goods_group_table.php)
  - [2026_04_02_000002_create_goods_table.php](/Users/apple/Documents/dujiaoshuka/database/migrations/2026_04_02_000002_create_goods_table.php)
  - [2026_04_02_000003_create_carmis_table.php](/Users/apple/Documents/dujiaoshuka/database/migrations/2026_04_02_000003_create_carmis_table.php)
  - [2026_04_02_000004_create_coupons_tables.php](/Users/apple/Documents/dujiaoshuka/database/migrations/2026_04_02_000004_create_coupons_tables.php)
  - [2026_04_02_000005_create_pays_table.php](/Users/apple/Documents/dujiaoshuka/database/migrations/2026_04_02_000005_create_pays_table.php)
  - [2026_04_02_000006_create_orders_table.php](/Users/apple/Documents/dujiaoshuka/database/migrations/2026_04_02_000006_create_orders_table.php)
- 这一步标志着项目正式从“只有整包 SQL 初始化”迈向“可增量演进的 schema 管理”。

影响范围：

- 数据库演进模型
- 安装层现代化起步
- 核心商业主链表结构管理方式

验证：

- 第一批迁移骨架已落库，并与当前模型/测试覆盖的核心业务表域对齐。
- 当前全量回归结果：`OK (65 tests, 172 assertions)`

下一步：

- 继续补第二批业务支撑表迁移，并开始评估 seeders 与 install.sql 默认数据的拆分方案。

### 31. 接入 Avatar 主题并切换为新版默认模板

摘要：

- 将用户提供的主题资源包接入为新主题 `avatar`，资源已落到 [public/assets/avatar](/Users/apple/Documents/dujiaoshuka/public/assets/avatar)。
- 基于现有前台模板骨架创建了 [resources/views/avatar](/Users/apple/Documents/dujiaoshuka/resources/views/avatar)，并将视图命名空间与静态资源路径切换到 `avatar`。
- 在 [config/dujiaoka.php](/Users/apple/Documents/dujiaoshuka/config/dujiaoka.php) 中注册 `avatar` 模板选项，并将前台基础渲染 fallback 从旧默认主题切换到 `avatar`。
- 这一步采用兼容接入方式：以 avatar 视觉资源为主，同时补入现有页面运行所需的兼容资源，先保证新版默认主题可启用。

影响范围：

- 前台默认主题
- 模板注册列表
- 前台错误页与基础渲染 fallback
- 主题资源目录结构

验证：

- `avatar` 主题目录与资源目录已落库，并已接入模板选择配置。
- 当前全量回归结果：`OK (65 tests, 172 assertions)`

下一步：

- 验证 avatar 主题页面可正常渲染，并继续微调其兼容层样式与资源引用。

### 32. 完成 Avatar 主题首页与布局第一轮细化

摘要：

- 重新设计了 [avatar/layouts/_nav.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/avatar/layouts/_nav.blade.php)、[avatar/layouts/_footer.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/avatar/layouts/_footer.blade.php) 和 [avatar/static_pages/home.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/avatar/static_pages/home.blade.php) 的主题结构。
- 扩展了 [public/assets/avatar/css/avatar.css](/Users/apple/Documents/dujiaoshuka/public/assets/avatar/css/avatar.css)，为 avatar 主题补上导航、英雄区、分类标签、商品卡片和页脚的完整视觉层。
- 这一步让 avatar 从“兼容接入可运行”进一步提升到“具备明确前台默认主题观感”的状态。

影响范围：

- Avatar 首页
- Avatar 导航与页脚
- Avatar 主题视觉表达

验证：

- 当前全量回归结果：`OK (65 tests, 172 assertions)`

下一步：

- 继续细化 avatar 的购买页、订单详情页和搜索页，让新主题在主流程页面上形成更一致的视觉语言。

### 33. 切换为单主题模式并移除旧主题包袱

摘要：

- 将仓库前台主题策略收敛为只保留 `avatar`，不再继续维护 `unicorn`、`luna`、`hyper` 三套旧主题。
- [config/dujiaoka.php](/Users/apple/Documents/dujiaoshuka/config/dujiaoka.php) 的模板列表已缩减为仅保留 `avatar`。
- 这样后续前台重构、样式维护、页面细化和兼容修复都只需要围绕一套主题进行，明显减轻了本轮现代化改造负担。

影响范围：

- 前台模板选择策略
- 旧主题目录维护成本
- 后续主题相关重构范围

验证：

- 旧主题目录已计划从仓库中移除，仅保留 avatar 主线。
- 当前全量回归结果：`OK (65 tests, 172 assertions)`

下一步：

- 继续围绕 avatar 细化主流程页面，并逐步清理旧主题遗留的文档和资源引用。

### 34. 拆出第二批业务支撑表，并启动结构迁移与默认种子分离

摘要：

- 新增了 [create_emailtpls_table.php](/Users/apple/Documents/dujiaoshuka/database/migrations/2026_04_02_000007_create_emailtpls_table.php) 和 [create_failed_jobs_table.php](/Users/apple/Documents/dujiaoshuka/database/migrations/2026_04_02_000008_create_failed_jobs_table.php)，开始把第二批业务支撑表从 `install.sql` 里迁出。
- 引入了 [BootstrapSeeder.php](/Users/apple/Documents/dujiaoshuka/database/seeds/BootstrapSeeder.php) 和 [SampleDataSeeder.php](/Users/apple/Documents/dujiaoshuka/database/seeds/SampleDataSeeder.php)，把“安装必需默认数据”和“本地开发示例数据”分成两条 seed 路径。
- 新增 [EmailTemplateSeeder.php](/Users/apple/Documents/dujiaoshuka/database/seeds/EmailTemplateSeeder.php)，由它负责默认邮件模板 bootstrap 数据，不再让 `DatabaseSeeder` 默认灌入示例订单。
- 补了 [EmailTemplateSeederTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/EmailTemplateSeederTest.php) 作为护栏，确保默认模板 token 集不会在后续拆分中被破坏。

影响范围：

- 第二批业务支撑表迁移
- 数据库默认初始化职责划分
- `DatabaseSeeder` 入口语义
- 默认邮件模板来源

验证：

- 默认邮件模板已可通过专用 seeder 完成 bootstrap 写入。
- 当前全量回归结果：`OK (66 tests, 178 assertions)`

下一步：

- 继续把 `install.sql` 中剩余的默认数据拆成更明确的 bootstrap seed 和 sample seed。

### 35. 将支付方式样例从安装默认值拆入 sample seed

摘要：

- 新增了 [PaySampleSeeder.php](/Users/apple/Documents/dujiaoshuka/database/seeds/PaySampleSeeder.php)，把 `install.sql` 里的支付方式样例配置迁移为开发样例 seed。
- [SampleDataSeeder.php](/Users/apple/Documents/dujiaoshuka/database/seeds/SampleDataSeeder.php) 现在会显式加载支付方式样例和示例订单，而不是让安装 bootstrap 默认带上一组伪商户配置。
- 这样新安装环境的 bootstrap 数据会更干净，支付网关示例只在本地演示、测试和开发初始化时按需提供。

影响范围：

- 默认支付方式样例来源
- sample seed 语义
- `install.sql` 后续职责收缩方向

验证：

- 已补充 [PaySampleSeederTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/PaySampleSeederTest.php) 作为样例支付配置护栏。
- 当前全量回归结果：`OK (67 tests, 184 assertions)`

下一步：

- 继续识别 `install.sql` 里剩余的后台/系统默认数据，进一步划分为 bootstrap、sample 和高风险禁入三类。

### 36. 固化安装数据分类边界，并为 bootstrap seed 加边界护栏

摘要：

- 新增了 [install-data-classification.md](/Users/apple/Documents/dujiaoshuka/docs/install-data-classification.md)，把 `install.sql` 里的后台/系统默认数据分成了 `bootstrap`、`sample`、`forbidden` 三类。
- 这一步明确将 `admin_users` 和 `admin_role_users` 归为高风险禁入项，不再把“默认管理员账号 + 角色绑定”视为可接受的安装默认值。
- 同时补了 [BootstrapSeederBoundaryTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/BootstrapSeederBoundaryTest.php)，确保 bootstrap seed 只恢复邮件模板这类安全默认值，不会把支付样例重新带回默认安装路径。

影响范围：

- 安装默认数据治理边界
- 后台骨架数据后续迁移策略
- bootstrap / sample seed 责任边界

验证：

- 当前全量回归结果：`OK (68 tests, 186 assertions)`

下一步：

- 继续把后台骨架数据从 `install.sql` 拆成独立迁移/seed，并在安装流程层面开始摆脱对整包 SQL 导入的依赖。

### 37. 启动第三批后台骨架结构迁移，并继续隔离高风险默认账号

摘要：

- 新增了后台骨架相关 migration：
  - [create_admin_menu_table.php](/Users/apple/Documents/dujiaoshuka/database/migrations/2026_04_02_000009_create_admin_menu_table.php)
  - [create_admin_permissions_table.php](/Users/apple/Documents/dujiaoshuka/database/migrations/2026_04_02_000010_create_admin_permissions_table.php)
  - [create_admin_roles_table.php](/Users/apple/Documents/dujiaoshuka/database/migrations/2026_04_02_000011_create_admin_roles_table.php)
  - [create_admin_permission_menu_table.php](/Users/apple/Documents/dujiaoshuka/database/migrations/2026_04_02_000012_create_admin_permission_menu_table.php)
  - [create_admin_role_menu_table.php](/Users/apple/Documents/dujiaoshuka/database/migrations/2026_04_02_000013_create_admin_role_menu_table.php)
  - [create_admin_role_permissions_table.php](/Users/apple/Documents/dujiaoshuka/database/migrations/2026_04_02_000014_create_admin_role_permissions_table.php)
  - [create_admin_settings_table.php](/Users/apple/Documents/dujiaoshuka/database/migrations/2026_04_02_000015_create_admin_settings_table.php)
- 这一轮只迁后台骨架结构，不迁 `admin_users` 和 `admin_role_users`，继续把默认管理员账号及其角色绑定隔离在高风险禁入区。
- 这样第三批已经从“只做策略盘点”进入“结构先行、账号后拆”的实作阶段。

影响范围：

- 第三批后台与系统表迁移进度
- 后台骨架与默认凭据分离策略
- `install.sql` 后续退场路径

验证：

- 当前全量回归结果：`OK (68 tests, 186 assertions)`

下一步：

- 继续补后台骨架的安全 seed 方案，并开始从安装流程中移除默认管理员账号导入。

### 38. 为后台骨架补上安全 bootstrap seed，继续排除默认管理员账号

摘要：

- 新增了 [AdminBootstrapSeeder.php](/Users/apple/Documents/dujiaoshuka/database/seeds/AdminBootstrapSeeder.php)，将 `admin_menu`、`admin_permissions`、`admin_roles` 的非敏感骨架数据从 `install.sql` 迁入 bootstrap seed。
- [BootstrapSeeder.php](/Users/apple/Documents/dujiaoshuka/database/seeds/BootstrapSeeder.php) 现在除了默认邮件模板，还会恢复后台菜单、权限和角色骨架。
- 整个过程仍然明确排除了 `admin_users` 和 `admin_role_users`，避免默认管理员账号和角色绑定再次回流到安装默认路径。
- 同时补了 [AdminBootstrapSeederTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/AdminBootstrapSeederTest.php) 和对 [BootstrapSeederBoundaryTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/BootstrapSeederBoundaryTest.php) 的增强护栏。

影响范围：

- 后台骨架默认数据来源
- bootstrap seed 的可用性
- 默认管理员账号禁入策略

验证：

- 当前全量回归结果：`OK (69 tests, 195 assertions)`

下一步：

- 开始改安装流程，从整包 `install.sql` 导入切换到“迁移 + bootstrap seed”的新入口。

### 39. 将安装主路径切换到 migrate + bootstrap seed，并改为显式创建首个管理员

摘要：

- 新增了 [InstallationService.php](/Users/apple/Documents/dujiaoshuka/app/Service/InstallationService.php)，安装流程现在会执行运行时连接配置、迁移、bootstrap seed、安装锁写入，以及首个管理员账号创建。
- [HomeController.php](/Users/apple/Documents/dujiaoshuka/app/Http/Controllers/Home/HomeController.php) 的 `doInstall()` 已经不再直接执行 `DB::unprepared(file_get_contents($installSql))`，而是切到新的安装服务入口。
- 补上了后台用户结构 migration：
  - [create_admin_users_table.php](/Users/apple/Documents/dujiaoshuka/database/migrations/2026_04_02_000016_create_admin_users_table.php)
  - [create_admin_role_users_table.php](/Users/apple/Documents/dujiaoshuka/database/migrations/2026_04_02_000017_create_admin_role_users_table.php)
- 安装页 [install.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/common/install.blade.php) 新增了管理员账号、密码、确认密码字段，安装完成后也不再提示固定默认账号。
- 同时补了 [InstallationServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/InstallationServiceTest.php)，守住“安装时显式创建首个管理员并绑定管理员角色”这条行为。

影响范围：

- 安装入口主流程
- 后台首个管理员创建方式
- `install.sql` 在安装路径中的职责

验证：

- 当前全量回归结果：`OK (70 tests, 198 assertions)`

下一步：

- 继续收缩安装流程中对 `install.sql` 的剩余依赖，并准备最终移除整包 SQL 安装主路径。

### 40. 将 install.sql 正式降级为历史参考文件

摘要：

- 给 [install.sql](/Users/apple/Documents/dujiaoshuka/database/sql/install.sql) 增加了明确的废弃说明，声明它仅作为历史结构/数据快照保留，不再作为新安装主入口。
- 新增了 [installer-modernization-status.md](/Users/apple/Documents/dujiaoshuka/docs/installer-modernization-status.md)，把当前安装主路径、新旧职责对比、已完成项和后续退场条件集中整理出来。
- 这一步的目标不是立刻删除 `install.sql`，而是先从代码和文档层面彻底去掉“它仍然是主安装方式”的暗示。

影响范围：

- 安装文档认知边界
- `install.sql` 的仓库定位
- 后续彻底移除旧 SQL 安装路径的准备工作

验证：

- 代码层安装主路径仍保持 `migrate + bootstrap seed + 显式创建首个管理员`。
- 当前全量回归结果：`OK (70 tests, 198 assertions)`

下一步：

- 继续把 `install.sql` 中剩余的后台关系数据和历史默认值拆解干净，为最终移除它做准备。

### 41. 同步 README 与 Debian 手册到新的安装现实

摘要：

- 更新了 [README.md](/Users/apple/Documents/dujiaoshuka/README.md)，补入当前安装流程现代化状态、最新测试基线以及相关文档索引。
- 更新了 [debian_manual.md](/Users/apple/Documents/dujiaoshuka/debian_manual.md)，将克隆地址切换到当前维护分支仓库，并明确安装时需要显式设置首个管理员账号密码。
- 这一轮的目标是把仓库表层文档和我们已经完成的代码现实对齐，避免后续用户仍按“默认 admin/admin + install.sql 安装”的旧认知操作。

影响范围：

- README 现状说明
- Debian 安装手册
- 新旧安装模式认知一致性

验证：

- 文档已与当前主线安装模式保持一致。
- 当前全量回归结果：`OK (70 tests, 198 assertions)`

下一步：

- 继续识别 `install.sql` 中剩余未迁出的历史数据，并评估最终移除条件。

### 42. 建立 install.sql 覆盖矩阵，并修正早期审计文档的历史时态

摘要：

- 新增了 [install-sql-coverage-matrix.md](/Users/apple/Documents/dujiaoshuka/docs/install-sql-coverage-matrix.md)，按表列出了 `install.sql` 中每一项结构和默认数据目前是否已被 migration / bootstrap / sample / forbidden 策略接管。
- 这份矩阵确认了一个关键结论：`install.sql` 中的表结构已经全部有对应迁移覆盖，剩余价值主要是历史快照和审计对照，而不再是安装功能依赖。
- 同时修正了 [project-audit-notes.md](/Users/apple/Documents/dujiaoshuka/docs/project-audit-notes.md) 和 [modernization-roadmap.md](/Users/apple/Documents/dujiaoshuka/docs/modernization-roadmap.md) 中部分已经过时的“现在时”描述，改成更准确的“原始项目曾如此，当前维护分支已部分修复”。

影响范围：

- `install.sql` 退场评估依据
- 审计文档准确性
- 现代化路线文档与当前事实的一致性

验证：

- 当前文档已能清晰区分“历史问题”和“当前主线状态”。
- 当前全量回归结果：`OK (70 tests, 198 assertions)`

下一步：

- 继续识别是否还需要为空关系表补最小 bootstrap 关系数据，或者直接将其维持为空结构。

### 43. 完成安装现代化阶段收尾，并确认空关系表维持为空结构

摘要：

- 新增了 [installation-modernization-closure.md](/Users/apple/Documents/dujiaoshuka/docs/installation-modernization-closure.md)，作为本阶段的正式结项说明。
- 这一轮明确做出最终判断：`admin_permission_menu`、`admin_role_menu`、`admin_role_permissions`、`admin_settings` 继续保持为空结构，不人为补伪造 bootstrap 关系数据。
- 这样安装现代化阶段的未决问题已经收口，`install.sql` 也从“仍待拆解”转为“短期保留的历史参考文件”。

影响范围：

- 安装现代化阶段边界
- `install.sql` 退场判断
- 后续阶段的工作焦点

验证：

- 当前全量回归结果：`OK (70 tests, 198 assertions)`

下一步：

- 后续重点转向更高层的框架升级、后台替换评估和剩余遗留兼容清障。

### 44. 统一品牌视觉与默认 Logo 为「独角数卡西瓜版」

摘要：

- 新增并接入了新的品牌主图与缩略图资源：
  [dujiaoka-xigua.png](/Users/apple/Documents/dujiaoshuka/public/assets/avatar/images/dujiaoka-xigua.png)、
  [favicon.png](/Users/apple/Documents/dujiaoshuka/public/assets/avatar/images/favicon.png)、
  [public/favicon.png](/Users/apple/Documents/dujiaoshuka/public/favicon.png)。
- 前台 `avatar` 主题导航、页脚、favicon 和品牌默认文案已统一切换为“独角数卡西瓜版”。
- 后台 `dcat-admin` 的名称、标题、Logo 和欢迎页标题已统一为“独角数卡西瓜版”。
- 安装页不再使用旧的超大内联 Logo，而是改为直接引用新的西瓜版品牌图，并同步更新安装程序标题和站点默认名称。
- 订单邮件模板数据与 Bark 推送的默认站点名，也统一切换为“独角数卡西瓜版”。

影响范围：

- 前台主题视觉识别
- 后台控制台品牌识别
- 安装流程品牌呈现
- 通知与推送默认品牌名

验证：

- 关键品牌文案和 favicon 引用已统一完成。
- 当前全量回归结果：`OK (70 tests, 198 assertions)`

下一步：

- 后续可继续细化 `avatar` 主题的下单、查询与订单详情页视觉一致性。

### 45. 同步仓库 README 与项目默认介绍口径

摘要：

- 更新了 [README.md](/Users/apple/Documents/dujiaoshuka/README.md)，将仓库首页说明统一为“独角数卡西瓜版”的当前定位、完成阶段与运行基线。
- 调整了 [composer.json](/Users/apple/Documents/dujiaoshuka/composer.json) 与 [package.json](/Users/apple/Documents/dujiaoshuka/package.json) 的项目描述，使仓库元信息不再停留在 Laravel 默认模板。
- 同步更新了 `avatar` 首页眉标、主题自述文件，以及中后台默认介绍文案，让仓库内外的项目介绍保持一致。

影响范围：

- GitHub 仓库首页展示
- 项目包元信息
- 默认主题首页介绍
- 中后台默认说明文案

验证：

- README、Composer、Package 与首页默认文案已统一到“独角数卡西瓜版”口径。
- 当前全量回归结果：`OK (70 tests, 198 assertions)`

下一步：

- 继续回到主线整改，推进升级前清障与后台替换评估。

### 46. 启动升级前清障与后台替换评估

摘要：

- 新增了 [upgrade-readiness-checklist.md](/Users/apple/Documents/dujiaoshuka/docs/upgrade-readiness-checklist.md)，把下一阶段的主要阻塞分成运行时依赖、后台框架、支付 SDK 和 Laravel 6 时代写法四类。
- 新增了 [admin-replacement-assessment.md](/Users/apple/Documents/dujiaoshuka/docs/admin-replacement-assessment.md)，对当前 `app/Admin` 与 Dcat 的耦合程度做了盘点，并给出“先降耦合、后替后台壳”的建议结论。
- 同步更新了 [modernization-roadmap.md](/Users/apple/Documents/dujiaoshuka/docs/modernization-roadmap.md) 与 [rectification-execution-plan.md](/Users/apple/Documents/dujiaoshuka/docs/rectification-execution-plan.md)，让主线计划进入升级前清障阶段时有明确依据。

影响范围：

- 下一阶段整改优先级
- 后台替换时机判断
- Laravel / PHP 升级前的准备路径

验证：

- 阻塞项、后台耦合面与建议执行顺序已形成书面依据。
- 当前全量回归结果：`OK (70 tests, 198 assertions)`

下一步：

- 开始为阻塞依赖建立“保留 / 替换 / 移除”矩阵，并优先处理 QRCode、Geetest 与测试依赖链。

### 47. 建立阻塞依赖执行矩阵

摘要：

- 新增了 [dependency-blocker-matrix.md](/Users/apple/Documents/dujiaoshuka/docs/dependency-blocker-matrix.md)，将当前关键阻塞依赖按用途、阻塞级别、建议动作和执行顺序整理成矩阵。
- 这份矩阵明确区分了三类问题：现代 PHP 直接阻塞链、支付 SDK 阻塞链、后台生态阻塞链。
- 当前建议的默认优先顺序已收敛为：`phpspec/prophecy`、`germey/geetest`、`simple-qrcode` / `bacon`。

影响范围：

- 依赖治理优先级
- 升级前清障执行顺序
- 后续包替换决策依据

验证：

- 阻塞包已有明确的“保留 / 替换 / 移除”判断依据。
- 当前全量回归结果：`OK (70 tests, 198 assertions)`

下一步：

- 开始处理第一条非业务阻塞链，优先确认 `phpspec/prophecy` 的移除路径。

### 48. 完成第一条依赖阻塞链：移除 Prophecy 主依赖

摘要：

- 通过升级 `phpunit/phpunit` 从 `9.5.4` 到 `9.6.34`，将 `phpspec/prophecy` 从当前项目主锁文件依赖链中移除。
- 同步更新了 [composer.lock](/Users/apple/Documents/dujiaoshuka/composer.lock)，并完成当前 PHPUnit 配置迁移，消除了旧 schema 警告。
- 当前测试代码中并未直接使用 `Prophecy`，因此这次清障没有触碰业务测试语义，只是清掉了历史测试依赖阻塞。

影响范围：

- 开发依赖锁文件
- PHPUnit 运行时
- 升级前测试链稳定性

验证：

- `./scripts/composer74 why phpspec/prophecy` 已确认当前项目中找不到该包。
- 当前全量回归结果：`OK (70 tests, 198 assertions)`

下一步：

- 继续处理下一条 P0 阻塞链，优先评估 `germey/geetest` 的移除或替换路径。

### 49. 完成第二条依赖阻塞链：移除 Geetest 主路径依赖

摘要：

- 将 `Geetest` 从前台下单主路径中移除，不再参与订单创建校验、前台购买页脚本、初始化路由和中间件启动流程。
- 清理了 [composer.json](/Users/apple/Documents/dujiaoshuka/composer.json)、[composer.lock](/Users/apple/Documents/dujiaoshuka/composer.lock)、[config/app.php](/Users/apple/Documents/dujiaoshuka/config/app.php) 中的 Geetest 包级依赖与注册入口。
- 删除了旧的 [config/geetest.php](/Users/apple/Documents/dujiaoshuka/config/geetest.php) 和 [resources/views/vendor/geetest/geetest.blade.php](/Users/apple/Documents/dujiaoshuka/resources/views/vendor/geetest/geetest.blade.php)，并移除了后台系统设置里的 Geetest 配置页。
- 新增了 [tests/Unit/GeetestRemovalTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/GeetestRemovalTest.php)，确保旧缓存开关即使仍为开启状态，也不会再阻塞订单创建校验。

影响范围：

- 前台下单验证链
- 历史行为验证依赖
- Composer 主依赖
- 后台系统设置入口

验证：

- `./scripts/composer74 why germey/geetest` 已确认当前项目中找不到该包。
- 当前全量回归结果：`OK (71 tests, 199 assertions)`

下一步：

- 继续处理下一条 P0 阻塞链，开始替换 `simple-qrcode` / `bacon` 这一组二维码依赖。

### 50. 完成第三条依赖阻塞链：移除二维码生成旧依赖

摘要：

- 将二维码支付页从后端 `QrCode::format('png')` 生成方式切换为前端本地 `jquery-qrcode` 渲染，不再依赖 `simple-qrcode` 和 `bacon`。
- 清理了 [composer.json](/Users/apple/Documents/dujiaoshuka/composer.json) 与 [composer.lock](/Users/apple/Documents/dujiaoshuka/composer.lock) 中的二维码依赖。
- 新增了 [tests/Unit/QrPayViewTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/QrPayViewTest.php)，确认二维码支付页使用本地前端二维码容器与脚本，而不是后端 PNG 生成器。

影响范围：

- 二维码支付页渲染方式
- Composer 主依赖
- 现代 PHP 阻塞链

验证：

- `./scripts/composer74 why simplesoftwareio/simple-qrcode` 与 `./scripts/composer74 why bacon/bacon-qr-code` 已确认当前项目中找不到这两个包。
- 当前全量回归结果：`OK (72 tests, 203 assertions)`

下一步：

- 继续进入下一组高优先级阻塞，开始评估 `paypal/rest-api-sdk-php` 与 `stripe/stripe-php` 的替换顺序。

### 51. 退役 Paysapi / Vpay / PayJS，并收紧 PayPal SDK 边界

摘要：

- 将 `Paysapi`、`Vpay`、`PayJS` 三个已落后的支付通道正式定义为新版本不再维护，并从支付路由、控制器、服务与测试基线中移除。
- 同步从 [composer.json](/Users/apple/Documents/dujiaoshuka/composer.json) 与 [composer.lock](/Users/apple/Documents/dujiaoshuka/composer.lock) 中移除了 `xhat/payjs-laravel`。
- 在 [Pay.php](/Users/apple/Documents/dujiaoshuka/app/Models/Pay.php)、[PayService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PayService.php) 与 [PayEntryService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PayEntryService.php) 中加入退役通道规则，使数据库中残留的旧网关配置也不会再进入前台主路径。
- 新增了 [PaypalSdkService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaypalSdkService.php)，将旧 PayPal REST SDK 访问收敛为单点服务，`PaypalCheckoutService` 与 `PaypalReturnService` 现在只通过它触达旧 SDK。
- 新增了 [RetiredGatewayTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/RetiredGatewayTest.php)，确保退役通道不会再出现在支付列表里，并会向用户返回明确的“已停止维护”提示。

影响范围：

- 新版本支付通道维护范围
- PayJS 依赖链
- 支付入口过滤规则
- PayPal 历史 SDK 隔离边界

验证：

- `./scripts/composer74 why xhat/payjs-laravel` 已确认当前项目中找不到该包。
- 当前全量回归结果：`OK (67 tests, 196 assertions)`

下一步：

- 继续聚焦仍保留的新版本支付通道，开始制定 `paypal/rest-api-sdk-php` 与 `stripe/stripe-php` 的替换顺序与单点清障方案。

### 52. 收紧 Stripe SDK 边界，进入保留支付通道单点替换阶段

摘要：

- 新增了 [StripeSdkService.php](/Users/apple/Documents/dujiaoshuka/app/Service/StripeSdkService.php)，将 `Stripe::setApiKey`、`Source::retrieve`、`Charge::create` 这组旧 SDK 访问收敛为单点服务。
- [StripePaymentService.php](/Users/apple/Documents/dujiaoshuka/app/Service/StripePaymentService.php) 现在不再直接静态调用 Stripe SDK，而是统一通过 `StripeSdkService` 访问。
- 同步更新了 [StripePaymentServiceTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/StripePaymentServiceTest.php)，让测试围绕 SDK 边界 mock，而不是继续依赖内部受保护方法覆写。

影响范围：

- Stripe 支付完成路径
- Stripe SDK 调用边界
- 后续 Stripe SDK 升级切口

验证：

- 定向测试：`OK (2 tests, 10 assertions)`
- 当前全量回归结果：`OK (67 tests, 200 assertions)`

下一步：

- 开始形成 `PayPal` 与 `Stripe` 两条保留支付通道的替换顺序与实施方案，优先处理更老的 `paypal/rest-api-sdk-php`。

### 53. 为 PayPal 与 Stripe 建立可替换的接口绑定边界

摘要：

- 新增了 [PaypalGatewayClientInterface.php](/Users/apple/Documents/dujiaoshuka/app/Service/Contracts/PaypalGatewayClientInterface.php) 与 [StripeGatewayClientInterface.php](/Users/apple/Documents/dujiaoshuka/app/Service/Contracts/StripeGatewayClientInterface.php)。
- 现有 [PaypalSdkService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaypalSdkService.php) 与 [StripeSdkService.php](/Users/apple/Documents/dujiaoshuka/app/Service/StripeSdkService.php) 已分别实现这些接口。
- [PaypalCheckoutService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaypalCheckoutService.php)、[PaypalReturnService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PaypalReturnService.php)、[StripePaymentService.php](/Users/apple/Documents/dujiaoshuka/app/Service/StripePaymentService.php) 现在都通过接口从容器取网关客户端，不再直接依赖具体 SDK 服务类。
- [AppServiceProvider.php](/Users/apple/Documents/dujiaoshuka/app/Providers/AppServiceProvider.php) 已加入接口到实现类的绑定，测试也同步切到基于接口的 mock。

影响范围：

- PayPal 与 Stripe 的 SDK 调用边界
- 后续 SDK 替换切口
- 支付服务的容器依赖关系

验证：

- 当前全量回归结果：`OK (67 tests, 200 assertions)`

下一步：

- 开始形成 `paypal/rest-api-sdk-php` 与 `stripe/stripe-php` 的替换顺序文档，并优先设计 PayPal 的退场方案。

### 54. 固化支付通道生命周期状态并接入后台管理面板

摘要：

- 在 [Pay.php](/Users/apple/Documents/dujiaoshuka/app/Models/Pay.php) 中正式定义支付通道生命周期，区分 `维护中`、`遗留待替换`、`已退役` 三种状态。
- 将 `PayPal` 与 `Stripe` 标记为遗留待替换通道，将 `Paysapi`、`Vpay`、`Payjs` 标记为已退役通道。
- 在后台 [PayController.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Controllers/PayController.php) 的列表页、详情页和编辑页接入生命周期展示与提示，避免继续把退役通道当作普通可维护支付方式使用。
- 新增 [PayLifecycleTest.php](/Users/apple/Documents/dujiaoshuka/tests/Unit/PayLifecycleTest.php)，直接守住生命周期分类规则。

影响范围：

- 支付通道后台管理
- 支付通道模型语义
- 后续支付替换与退场治理

验证：

- 当前全量回归结果：`OK (70 tests, 207 assertions)`

下一步：

- 开始整理 `PayPal` 退场方案，把旧 REST SDK 的替代接入方式和迁移策略固定下来。
