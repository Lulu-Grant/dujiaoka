# Avatar 前台 Bootstrap CSS 依赖矩阵

更新时间：2026-06-02

## 结论

`avatar` 前台已经停止加载旧大 JS、图标字体和 Bootstrap JS，但仍在 `default` 与 `seo` 布局中加载 `bootstrap.min.css`。当前 Bootstrap CSS 的真实依赖不在首页、购买页和确认订单页主视觉，而集中在通用布局、tab/modal 基础显示规则、表单控件和少数旧页面。

下一批可以开始用 `avatar.css` 接管这些样式，但不应一次性删除 Bootstrap CSS；应先替换残余页面类，再停止前台新页面引用。

## 可直接由 avatar.css 接管

| 依赖类别 | 当前类 | 影响页面 | 替代策略 |
| --- | --- | --- | --- |
| 页面宽度容器 | `container` | 通用布局、导航、页脚 | 新增或复用 `.avatar-container`，保留最大宽度、左右 padding 和响应式收缩 |
| 表单输入 | `form-control` | 首页搜索、购买页、订单查询页、订单详情卡密文本框 | 已有 `.avatar-form .form-control` 和 `.avatar-search .form-control`，下一批补全裸 `textarea.form-control` |
| 分段 tab | `nav`、`nav-item`、`nav-link`、`tab-content`、`tab-pane` | 首页分类、订单查询页 | `hyper.js` 已接管行为，下一批补 `.tab-content/.tab-pane` 通用显示规则 |
| Modal 外壳 | `modal`、`fade`、`modal-dialog`、`modal-dialog-centered`、`modal-content`、`modal-header`、`modal-title`、`modal-body`、`close`、`modal-backdrop`、`modal-open` | 首页公告、购买提示、商品图预览 | `hyper.js` 已接管行为，下一批补 modal 结构、过渡、backdrop、居中和关闭按钮样式 |
| 轻量间距/文本 | `mt-3`、`mt-4`、`mb-1`、`text-center`、`text-danger`、`text-uppercase`、`text-primary` | 错误页、订单详情、二维码支付 | 用 `avatar.css` 增加最小兼容工具类或替换模板为 `avatar-*` 类 |
| 按钮 | `btn`、`btn-info`、`btn-outline-primary` | 错误页、订单详情复制按钮 | 替换为 `.avatar-button` 系列，保留触控高度和复制按钮状态 |

## 暂时保留，需先重构模板

| 依赖类别 | 当前类 | 影响页面 | 保留原因 |
| --- | --- | --- | --- |
| 栅格布局 | `row`、`col-12`、`col-md-12`、`col-lg-4`、`col-lg-6`、`justify-content-center` | 错误页、订单详情、二维码支付 | 这些旧页仍用 Bootstrap 栅格承载主体宽度，下一批应改成 `avatar-panel` 和自有 grid |
| 卡片结构 | `card`、`card-body`、`card-title`、`card-text`、`border`、`border-primary` | 订单详情、二维码支付 | 旧页面还依赖 card padding、边框和标题间距，建议替换成 `avatar-panel` |
| 状态标签 | `badge`、`badge-outline-primary` | 订单详情 | 替换为 `.avatar-badge` 或新增订单详情专用状态条 |
| 内联显示 | `d-inline-block` | 二维码支付页 | 可直接替换为 `avatar-inline-block` 或目标元素样式 |
| 输入组合 | `input-group`、`input-group-append` | 购买页验证码 | 需先补自有验证码输入布局，避免验证码图与输入框错位 |

## 页面级处理顺序

1. 首页、购买页、订单查询页、确认订单页：只补通用 `container/tab/modal/form-control/input-group` 兼容样式，不大改模板。
2. 订单详情页：把 `row/col/card/badge/btn/mb-*` 替换为 `avatar-panel`、`avatar-badge`、`avatar-button` 和自有信息 grid。
3. 二维码支付页：把 `row/col/card/text-*` 替换为自有支付面板，并保留二维码插件脚本。
4. 错误页：改为自有错误面板和返回按钮，不再依赖 Bootstrap 工具类。
5. 替换完成并通过浏览器检查后，停止 `avatar.layouts._header` 与 `avatar.layouts.seo` 引用 `bootstrap.min.css`。

## 验收标准

- `/`、`/buy/30`、`/buy/31`、`/buy/32`、`/buy/33`、`/order-search`、确认订单页、订单详情页、二维码支付页和错误页不再请求 `bootstrap.min.css`。
- 首页公告、购买提示、图片预览、订单查询 tab、验证码输入、复制卡密和二维码支付轮询无行为回归。
- 移动端 `430px` 和 `390px` 下无横向溢出，订单详情与二维码支付不出现旧红色危险按钮或旧卡片风格。
- 旧 `bootstrap.min.css` 文件保留，不删除历史资源。
