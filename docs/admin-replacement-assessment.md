# 后台替换评估

## 背景

当前项目后台依赖 `Dcat Admin 2.x`，并与 Laravel 6 遗留栈深度耦合。

在安装现代化阶段收尾后，后台已经成为下一阶段框架升级前最重要的结构性阻塞之一。

---

## 当前耦合现状

截至 2026-04-02，本仓库 `app/Admin` 下共有 `29` 个文件，主要分布如下：

- Controllers：9 个
- Repositories：7 个
- Forms：3 个
- Charts：4 个
- Actions：2 个
- 其他入口与引导：4 个

主要 Dcat 依赖形态：

- `Dcat\Admin\Http\Controllers\AdminController`
- `Dcat\Admin\Form`
- `Dcat\Admin\Grid`
- `Dcat\Admin\Show`
- `Dcat\Admin\Widgets\Form`
- `Dcat\Admin\Widgets\Card`
- `Dcat\Admin\Widgets\Metrics\*`
- `Dcat\Admin\Repositories\EloquentRepository`
- `Dcat\Admin\Admin`

这说明当前后台不是“少量页面依赖 Dcat”，而是：

- 路由层依赖 Dcat
- 控制器层依赖 Dcat
- 表单与列表构建依赖 Dcat
- 后台数据访问层也依赖 Dcat Repository 抽象
- 仪表盘组件依赖 Dcat Widgets / Metrics

---

## 现阶段判断

### 1. Dcat 是升级阻塞，而不是普通依赖

当前后台几乎整层建立在 Dcat 组件模型之上，这会直接影响：

- Laravel 升级路径
- PHP 大版本兼容性
- 后台权限模型演进
- 页面层替换成本

如果不先把后台耦合面看清楚，后续升级容易出现：

- 业务代码没问题，但后台先被卡死
- 框架能升，管理端不能跑
- 权限、菜单、表单、列表一起返工

### 2. 目前不适合“直接整体替后台”

虽然 Dcat 是阻塞点，但当前仓库刚完成：

- 安装现代化
- 订单与支付主链第一轮服务化
- 品牌统一

这意味着现在最合适的动作不是立刻重写后台，而是：

- 先冻结后台新增复杂功能
- 继续把后台控制器背后的业务逻辑往普通服务层抽
- 让后台逐步从“业务承载层”退化成“后台展示入口”

### 3. 后台替换应分两层看

第一层：替换成本  
第二层：替换时机

当前真正需要先做的是降低替换成本，而不是立刻决定最终后台框架。

---

## 后台能力盘点

从现有文件来看，后台当前承担的能力主要包括：

- 商品管理
- 商品分类管理
- 卡密管理与导入
- 订单管理
- 优惠码管理
- 支付通道管理
- 邮件模板管理
- 系统设置管理
- 邮件测试
- 数据看板与统计图表

这些能力中，替换难度并不相同。

### 低风险能力

- 商品分类
- 商品
- 优惠码
- 邮件模板
- 支付通道基础信息

原因：

- 以 CRUD 为主
- 业务流程相对清晰
- 可较容易迁到普通控制器 + 表单/表格层

### 中风险能力

- 卡密导入
- 系统设置
- 订单列表与状态操作

原因：

- 含批量动作
- 含导入/导出
- 含较多格式化逻辑和权限控制

补充说明：

- 其中“系统设置”已经开始第一轮降耦合：
  - [SystemSettingService.php](/Users/apple/Documents/dujiaoshuka/app/Service/SystemSettingService.php) 负责系统设置默认值、字段白名单、缓存读写
  - [MailConfigService.php](/Users/apple/Documents/dujiaoshuka/app/Service/MailConfigService.php) 负责从系统设置派生运行时邮件配置
  - [SystemSetting.php](/Users/apple/Documents/dujiaoshuka/app/Admin/Forms/SystemSetting.php) 现在主要保留表单结构，不再直接承担设置持久化规则

### 高风险能力

- 仪表盘图表
- 自定义 Action
- Dcat Widget 组合页面
- 后台认证和会话流程

原因：

- 强依赖 Dcat 组件模型
- 替换时往往不是平移，而是重做

---

## 建议路线

### 路线 A：短期维持 Dcat，先降耦合

适合当前阶段。

执行重点：

- 不再往 `app/Admin` 增加复杂业务逻辑
- 后台控制器只保留展示和调用服务
- 将可复用逻辑抽到普通 `app/Service`
- 把后台页面的核心输入输出整理清楚

优点：

- 风险最低
- 不打断主线重构
- 为后续后台替换创造条件

缺点：

- Dcat 仍然是升级阻塞
- 后台仍需在遗留栈中维持一段时间

### 路线 B：中期替换后台壳层

适合在升级前清障进一步完成后启动。

执行重点：

- 保留现有数据模型和服务层
- 重建后台路由、表单、列表、仪表盘壳层
- 逐个模块迁移 CRUD 与批量操作

优点：

- 可以真正降低 Dcat 锁定
- 后续 Laravel 升级空间更大

缺点：

- 需要一段双轨或过渡期
- 迁移期间测试与验收成本明显上升

### 路线 C：直接整体重写后台

当前不推荐。

原因：

- 支付与升级前清障还未完全收口
- 现在整体重写会拉高并发风险
- 业务与框架问题会混在一起，难以定位回归

---

## 推荐结论

当前建议采用：

- 近期：路线 A
- 中期：准备路线 B
- 暂不采用路线 C

也就是说：

1. 先把后台从 Dcat 业务承载层，降级成薄展示层
2. 再决定用什么新后台壳去接这些服务
3. 最后再把 Dcat 从主线路径中退场

---

## 下一步默认动作

1. 为升级前清障建立明确的依赖阻塞矩阵
2. 识别哪些后台页面仍直接操作业务模型或内嵌业务规则
3. 优先把高频后台模块背后的业务逻辑继续抽到普通服务层
4. 暂停新增 Dcat 绑定型后台能力

当前已完成的第一组后台降耦合样板：

- 系统设置读写与默认值已从 Dcat 表单抽出到普通服务层
- 邮件测试与邮件发送已改为读取 [MailConfigService.php](/Users/apple/Documents/dujiaoshuka/app/Service/MailConfigService.php)，不再在 Dcat 表单/Job 中各自拼装配置
- 数据看板统计口径已抽到 [AdminDashboardMetricsService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminDashboardMetricsService.php)，图表组件开始只保留展示拼装
- 卡密导入已抽到 [CarmiImportService.php](/Users/apple/Documents/dujiaoshuka/app/Service/CarmiImportService.php)，Dcat 表单不再直接解析文本、去重和批量入库
- 通用恢复动作已抽到 [SoftDeleteRestoreService.php](/Users/apple/Documents/dujiaoshuka/app/Service/SoftDeleteRestoreService.php)，Dcat RowAction / BatchAction 不再直接操作模型恢复
- 商品列表的实时库存计算已抽到 [GoodsInventoryService.php](/Users/apple/Documents/dujiaoshuka/app/Service/GoodsInventoryService.php)，控制器不再直接在 Grid 闭包中查卡密库存
- 支付后台页与订单/商品详情页的展示格式化已抽到 [PayAdminPresenterService.php](/Users/apple/Documents/dujiaoshuka/app/Service/PayAdminPresenterService.php) 与 [AdminTextareaPresenterService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminTextareaPresenterService.php)
- 后台高频 CRUD 页的下拉选项来源已抽到 [AdminSelectOptionService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminSelectOptionService.php)，优惠码多选格式化已抽到 [CouponAdminPresenterService.php](/Users/apple/Documents/dujiaoshuka/app/Service/CouponAdminPresenterService.php)
- 商品与卡密详情页的类型/状态/循环展示已抽到 [CatalogAdminPresenterService.php](/Users/apple/Documents/dujiaoshuka/app/Service/CatalogAdminPresenterService.php)
- 各后台控制器重复的“回收站作用域判断”已抽到 [AdminTrashScopeService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminTrashScopeService.php)
- 后台表单重复的 footer 行为与邮件模板 token 字段策略已抽到 [AdminFormBehaviorService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminFormBehaviorService.php)
- 后台各 CRUD 页面重复的“恢复动作挂载”已抽到 [AdminGridRestoreActionService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminGridRestoreActionService.php)
- 后台各 CRUD 页面重复的“回收站筛选挂载”和订单列表日期区间过滤已抽到 [AdminFilterService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminFilterService.php)
- 后台高频 CRUD 页详情页/只读表单的机械字段挂载已抽到 [AdminDetailFieldService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminDetailFieldService.php)，并且大量简单展示闭包已替换成明确的 presenter callable
- 后台“标题 + Card 表单页”壳已抽到 [AdminPageCardService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminPageCardService.php)，后台首页看板布局已抽到 [AdminDashboardLayoutService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminDashboardLayoutService.php)
- 后台恢复动作挂载已进一步收口到 [AdminGridRestoreActionService.php](/Users/apple/Documents/dujiaoshuka/app/Service/AdminGridRestoreActionService.php) 的完整接线方法；目前后台控制器里仅剩一个明显依赖 Dcat 行上下文的库存展示闭包
