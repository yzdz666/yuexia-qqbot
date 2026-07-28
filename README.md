# 月下PHP QQ机器人框架 (Yuexia PHP QQ Bot)

基于 PHP 的多协议 QQ 机器人框架，支持 OneBot 协议、插件系统、插件市场、AI 写插件等功能。

---

## 特性

- **多协议支持** — 兼容 OneBot v11 标准，支持正向/反向 WebSocket 连接
- **插件系统** — 插件即文件，放入 `plugin/` 目录即可加载，支持热更新
- **插件市场** — 基于 GitHub Fork + PR 的插件发布流程，一键安装/更新/卸载
- **AI 写插件** — 集成 AI 接口，通过自然语言描述即可生成插件代码
- **管理后台** — 功能完整的管理面板，支持机器人管理、消息日志、指令测试、用户管理
- **安全加固** — CSRF 保护、SQL 注入防护、XSS 过滤、CSP 头、输入验证
- **SQLite 存储** — 零配置数据库，无需安装额外服务

## 目录结构

```
├── index.php             # 入口文件
├── auth.php              # 认证模块
├── bot.php               # 机器人核心
├── router.php            # 路由模块
├── db.php                # 数据库层
├── function.php          # 公共函数
├── install.php           # 安装向导
├── ws_client.php         # WebSocket 客户端
├── ws_event_handler.php  # WS 事件处理
├── marketplace.json      # 插件市场注册表
├── PLUGIN_SPEC.md        # 插件发布规范
├── admin/                # 管理后台
│   ├── index.php         # 仪表盘
│   ├── bots.php          # 机器人管理
│   ├── messages.php      # 消息日志
│   ├── plugins.php       # 插件管理
│   ├── marketplace.php   # 插件市场
│   ├── submit_plugin.php # 提交插件
│   ├── chat.php          # 聊天记录
│   ├── simulate.php      # 指令测试
│   ├── aidev.php         # AI 写插件
│   ├── settings.php      # 系统设置
│   ├── github_settings.php # GitHub 配置
│   ├── api/              # API 接口
│   └── style.css         # 样式
├── plugin/               # 插件目录
│   ├── 名言.php
│   ├── 音乐.php
│   ├── 发送测试.php
│   ├── Ark卡片.php
│   └── */plugin.json     # 插件清单文件
└── function/             # 功能模块
    ├── GD.php            # 图片处理
    ├── qrcode.php        # 二维码
    ├── tuwen.php         # 图文处理
    ├── Parsedown.php     # Markdown 解析
    └── Mail/             # 邮件发送
```

## 快速开始

### 环境要求

- PHP 7.4+
- SQLite3 扩展
- 支持的文件系统权限

### 安装

1. 将源码部署到服务器或本地 PHP 环境
2. 访问 `install.php` 运行安装向导
3. 设置管理员账号和密码
4. 在后台添加机器人（AppID + Secret）

### 机器人配置

支持 OneBot 协议标准的机器人实现：

1. 获取 QQ 机器人的 AppID 和 Secret
2. 在后台「机器人管理」添加机器人
3. 配置 WebSocket 连接地址
4. 启动后即可接收和发送消息

## 插件系统

### 插件结构

一个插件由两个文件组成：

```
plugin/
├── 插件名.php              # 插件主文件
└── 插件名/                 # 插件资源目录（可选）
    └── plugin.json         # 插件清单文件
```

### 插件清单 (plugin.json)

```json
{
  "name": "plugin-name",
  "title": "插件标题",
  "description": "功能描述",
  "version": "1.0.0",
  "author": "作者名",
  "main": "插件名.php",
  "license": "MIT",
  "tags": ["标签1", "标签2"],
  "category": "功能",
  "min_framework_version": "1.0.0"
}
```

### 开发插件

在 `plugin/` 目录下创建 PHP 文件，框架会自动加载：

```php
<?php
/**
 * 插件名: 我的插件
 * 版本: 1.0.0
 * 描述: 插件功能描述
 */

// 注册指令
plugin_register('hello', function ($params, $event) {
    reply_message('Hello, World!');
});
```

详细开发文档请参考后台「开发文档」页面。

## 插件市场

插件市场基于 GitHub 的 Fork + Pull Request 工作流：

1. **Fork** — 将官方插件仓库 fork 到你的 GitHub 账号
2. **开发** — 在 fork 中按规范创建插件目录和 `plugin.json`
3. **PR** — 提交 Pull Request 到官方仓库
4. **审核** — 仓库管理员在 GitHub 上审核代码
5. **发布** — 审核通过并合并后，管理员同步更新市场列表
6. **安装** — 用户在后台一键安装市场中的插件

### 配置 OAuth

1. 在 [GitHub Developer Settings](https://github.com/settings/developers) 创建 OAuth App
2. 在后台「GitHub 设置」页面配置 Client ID 和 Client Secret
3. 设置官方插件仓库地址（默认 `yzdz666/yuexia-plugins`）

## AI 写插件

后台「AI 写插件」功能可以通过自然语言描述自动生成插件代码：

1. 在系统设置中配置 AI API Key
2. 进入 AI 写插件页面
3. 用中文描述你想要的插件功能
4. AI 自动生成插件代码并可直接安装使用

## 管理后台

| 功能 | 说明 |
|---|---|
| 仪表盘 | 系统概览、运行状态 |
| 机器人管理 | 添加/编辑/删除机器人，管理协议端点 |
| 消息日志 | 查看和搜索消息记录 |
| 插件管理 | 启用/禁用/上传/编辑插件 |
| 插件市场 | 浏览、安装、更新、卸载市场插件 |
| 提交插件 | 通过 GitHub Fork + PR 发布插件 |
| 聊天记录 | 群聊和私聊消息历史 |
| 指令测试 | 模拟发送指令测试插件响应 |
| 用户管理 | 查看用户和群组信息 |
| AI 写插件 | 用 AI 生成插件代码 |
| 系统设置 | 基本配置、AI 配置、安全设置 |

## 安全

- 所有管理页面需要登录认证
- 写操作受 CSRF Token 保护
- 输出经过 XSS 过滤
- 使用参数化查询防止 SQL 注入
- CSP 头限制资源加载来源
- 文件上传类型和大小校验

## 开源协议

MIT License

Copyright (c) 2024-2026 月下PHP

---

> 项目地址：https://github.com/yzdz666/yuexia-qqbot  
> 插件市场：https://github.com/yzdz666/yuexia-plugins