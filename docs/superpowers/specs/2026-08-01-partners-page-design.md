# Partners 经销商展示页设计文档

## 1. 概述

新建一个独立的公开展示页面 `partners.php`，用于展示授权经销商信息。页面采用响应式卡片网格布局，每个卡片展示经销商头像、名称、状态、简介、网站链接、联系方式、支持支付方式及评分。

页面不加入顶部导航，但使用与首页一致的 GridScan 全局网格动画背景，保持视觉统一。

## 2. 页面结构

```
partners.php
├── 全局背景：GridScan 3D 网格动画 (#grid-scan-bg)
├── 页面头部 (Page Header)
│   ├── 标题：Authorized Resellers
│   └── 副标题：Trusted partners around the world
└── 经销商网格 (partners-grid.php)
    └── 3 个测试经销商卡片
```

## 3. 组件设计

### 3.1 partners.php

- 入口文件，遵循现有页面模式（`about.php`、`auth.php` 等）
- 引入 `config.php`、`header.php`、`nav.php`
- 定义页面标题：`Partners - SITE_NAME`
- 注入 GridScan 背景容器
- 包含 `includes/sections/partners-grid.php`
- 引入 `footer.php`

### 3.2 includes/sections/partners-grid.php

- 顶部定义 PHP 数据数组 `$partners`，每个元素包含：
  - `name`：经销商名称
  - `status`：状态（verified / official / trusted）
  - `avatar`：头像路径
  - `description`：简短描述
  - `website`：网站链接
  - `discord`：Discord 联系方式
  - `telegram`：Telegram 联系方式
  - `payments`：支付方式数组
  - `rating`：评分（1-5）
- 循环渲染卡片网格
- 每个卡片结构：
  - 头像（圆形，使用 `hero-bg.jpg`）
  - 名称 + 状态标签
  - 评分星级
  - 简介
  - 网站链接（外链按钮）
  - 联系方式图标
  - 支付方式标签

## 4. 样式方案

- 复用现有 Tailwind 工具类：`glass-card`、`hover-lift`、`fade-in-up`、`btn-primary`、`btn-secondary` 等
- 卡片使用 `glass-card` 玻璃态效果
- 网格响应式：`grid-cols-1 md:grid-cols-2 lg:grid-cols-3`
- 卡片内边距：`p-6` 或 `p-8`
- 头像尺寸：`w-20 h-20 rounded-full object-cover`
- 状态标签使用不同颜色区分：
  - verified：青色
  - official：紫色
  - trusted：绿色
- 支付方式标签：小圆角胶囊样式

## 5. 测试经销商数据

准备 3 个测试经销商，头像统一使用 `assets/images/hero-bg.jpg`，其他信息如下：

1. **Nova Mods**
   - status: official
   - description: Premium GTA V mod menu reseller with instant delivery.
   - website: https://nova-mods.example.com
   - discord: NovaSupport
   - telegram: @novamods
   - payments: PayPal, Credit Card, Crypto
   - rating: 5

2. **Global Keys**
   - status: verified
   - description: Reliable keys and licenses for Atlas Menu products.
   - website: https://globalkeys.example.com
   - discord: GlobalKeys
   - telegram: @globalkeys
   - payments: PayPal, Alipay, WeChat Pay
   - rating: 4

3. **Elite Gaming**
   - status: trusted
   - description: Trusted reseller offering 24/7 support and discounts.
   - website: https://elitegaming.example.com
   - discord: EliteGaming
   - telegram: @elitegaming
   - payments: Crypto, Credit Card
   - rating: 5

## 6. 国际化

- 当前阶段页面文案使用英文硬编码
- 后续可根据需要接入 `_e()` 翻译系统

## 7. 导航

- 页面不加入顶部导航栏链接
- 通过直接访问 `partners.php` 打开

## 8. 依赖文件

- `assets/js/animations.js`：GridScan 背景动画（首页已引入，由 header/footer 统一加载）
- `assets/css/main.css`：全局样式和按钮组件
- `assets/css/animations.css`：动画辅助类
- `assets/images/hero-bg.jpg`：测试头像

## 9. 验收标准

- [ ] `partners.php` 可正常访问
- [ ] 页面展示 3 个经销商卡片
- [ ] 每个卡片包含头像、名称、状态、评分、简介、网站链接、联系方式、支付方式
- [ ] 背景使用 GridScan 3D 网格动画
- [ ] 页面在桌面和移动端显示正常
