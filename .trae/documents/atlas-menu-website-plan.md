# Atlas Menu 企业官网实施计划

## 项目概述
为 Atlas Menu 创建一个现代化的游戏菜单企业官网，采用PHP架构，包含首页和关于我们页面。

## 技术栈
- **后端**: 原生PHP（轻量级MVC结构）
- **前端**: Tailwind CSS + 自定义CSS动画
- **JavaScript**: 原生JS（滚动动画、交互效果）

## 目录结构
```
E:\VibeCoding-2\
├── index.php                    # 首页入口
├── about.php                    # 关于我们页面
├── config/
│   └── config.php              # 全局配置
├── includes/
│   ├── header.php              # 页面头部模板
│   ├── footer.php              # 页面底部模板
│   ├── nav.php                 # 导航栏组件
│   └── sections/               # 首页区块组件
│       ├── hero.php            # 英雄区
│       ├── stats.php           # 统计计数器
│       ├── features.php        # 功能特性
│       ├── products.php        # 产品展示
│       ├── pricing.php         # 定价方案
│       ├── faq.php             # 常见问题
│       └── cta.php             # 行动号召
├── assets/
│   ├── css/
│   │   ├── main.css            # 自定义样式
│   │   └── animations.css      # 动画效果
│   └── js/
│       ├── main.js             # 主脚本
│       └── animations.js       # 滚动动画
└── pages/                      # 未来扩展
```

## 设计系统

### 色彩方案
- **主背景**: #0a0a0f（深黑）
- **卡片背景**: #12121a（深灰）
- **主紫色**: #8b5cf6
- **蓝色**: #3b82f6
- **青色**: #06b6d4
- **渐变色**: 紫→蓝→青

### 字体
- **主字体**: Inter（正文）
- **展示字体**: Rajdhani（标题）

### 动画效果
- 滚动淡入（fade-in-up）
- 数字计数器动画
- 卡片悬停效果
- FAQ手风琴展开/收起

## 页面结构

### 首页 (index.php)
1. **导航栏** - 固定顶部，毛玻璃效果
2. **Hero区** - 大标题 + CTA按钮 + 背景光效
3. **统计计数器** - 4个关键数据展示
4. **功能特性** - 6个功能卡片网格
5. **产品展示** - 3个产品卡片（带状态标签）
6. **定价方案** - 3个定价层级
7. **常见问题** - FAQ手风琴
8. **CTA区** - 行动号召
9. **页脚** - 链接和版权信息

### 关于我们页面 (about.php)
1. **导航栏** - 同首页
2. **页面标题** - 关于我们
3. **公司介绍** - 公司使命和愿景
4. **团队介绍** - 团队成员展示
5. **发展历程** - 时间线展示
6. **核心价值观** - 公司理念
7. **页脚** - 同首页

## 实施步骤

### 阶段1：基础架构搭建
1. 创建目录结构
2. 创建配置文件 (config.php)
3. 创建头部和底部模板 (header.php, footer.php)
4. 创建导航组件 (nav.php)

### 阶段2：样式系统
1. 创建主样式文件 (main.css)
2. 创建动画样式 (animations.css)
3. 配置Tailwind自定义主题

### 阶段3：首页开发
1. 创建Hero区块
2. 创建统计计数器区块
3. 创建功能特性区块
4. 创建产品展示区块
5. 创建定价方案区块
6. 创建FAQ区块
7. 创建CTA区块
8. 组装首页 (index.php)

### 阶段4：关于我们页面
1. 创建about.php页面
2. 添加页面专属内容区块

### 阶段5：JavaScript交互
1. 创建滚动动画脚本 (animations.js)
2. 创建主交互脚本 (main.js)
3. 实现移动端菜单
4. 实现FAQ手风琴
5. 实现计数器动画

## 验证方法
1. 在本地PHP服务器运行项目
2. 访问首页检查所有区块显示
3. 访问关于我们页面检查内容
4. 测试响应式布局（移动端/平板/桌面）
5. 测试所有交互效果（滚动动画、FAQ展开、移动端菜单）

## 关键文件清单
- `config/config.php` - 站点配置
- `includes/header.php` - 头部模板
- `includes/footer.php` - 底部模板
- `includes/nav.php` - 导航组件
- `includes/sections/*.php` - 首页7个区块
- `index.php` - 首页
- `about.php` - 关于我们页面
- `assets/css/main.css` - 主样式
- `assets/css/animations.css` - 动画样式
- `assets/js/main.js` - 主脚本
- `assets/js/animations.js` - 动画脚本
