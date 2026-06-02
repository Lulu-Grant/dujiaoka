# Avatar 前台 Bootstrap CSS 依赖矩阵

更新时间：2026-06-02

## 结论

`avatar` 前台已经停止加载旧大 JS、图标字体、Bootstrap JS 和 Bootstrap CSS。当前前台新页面由 `avatar.css` 接管容器、表单、tab、modal、订单详情、二维码支付和错误页基础样式。

旧 `bootstrap.min.css` 文件保留在静态目录中，不删除历史资源；后续只继续评估 `base.css / common.css / index.css` 是否还能进一步瘦身。

## 可直接由 avatar.css 接管

| 依赖类别 | 当前类 | 影响页面 | 替代策略 |
| --- | --- | --- | --- |
| 页面宽度容器 | `container` | 通用布局、导航、页脚 | 已由 `avatar.css` 接管 |
| 表单输入 | `form-control` | 首页搜索、购买页、订单查询页 | 已由 `avatar.css` 接管，订单详情改为 `avatar-input` |
| 分段 tab | `nav`、`nav-item`、`nav-link`、`tab-content`、`tab-pane` | 首页分类、订单查询页 | 行为由 `hyper.js` 接管，显示规则由 `avatar.css` 接管 |
| Modal 外壳 | `modal`、`fade`、`modal-dialog`、`modal-dialog-centered`、`modal-content`、`modal-header`、`modal-title`、`modal-body`、`close`、`modal-backdrop`、`modal-open` | 首页公告、购买提示、商品图预览 | 行为由 `hyper.js` 接管，样式由 `avatar.css` 接管 |
| 旧页模板样式 | `row/col/card/btn/badge/text-*` | 错误页、订单详情、二维码支付 | 已替换为 `avatar-panel`、`avatar-badge`、`avatar-button` 和自有 grid |
| 验证码输入组合 | `input-group`、`input-group-append` | 购买页验证码 | 已替换为 `avatar-captcha-group` 和 `avatar-captcha-addon` |

## 页面级处理状态

1. 首页、购买页、订单查询页、确认订单页：已由 `avatar.css` 接管通用容器、表单、tab 和 modal 样式。
2. 订单详情页：已替换为 `avatar-panel`、`avatar-badge`、`avatar-button` 和自有信息 grid。
3. 二维码支付页：已替换为自有支付面板，并保留二维码插件脚本。
4. 错误页：已替换为自有错误面板和返回按钮。
5. `avatar.layouts._header` 与 `avatar.layouts.seo` 已停止引用 `bootstrap.min.css`。

## 验收标准

- `/`、`/buy/30`、`/buy/31`、`/buy/32`、`/buy/33`、`/order-search`、确认订单页、订单详情页、二维码支付页和错误页不再请求 `bootstrap.min.css`。
- 首页公告、购买提示、图片预览、订单查询 tab、验证码输入、复制卡密和二维码支付轮询无行为回归。
- 移动端 `430px` 和 `390px` 下无横向溢出，订单详情与二维码支付不出现旧红色危险按钮或旧卡片风格。
- 旧 `bootstrap.min.css` 文件保留，不删除历史资源。
