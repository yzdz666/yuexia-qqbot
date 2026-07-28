<?php
$pageTitle = '提交插件';
require_once('header.php');
?>
<style>
.step-guide { max-width: 800px; margin: 0 auto; }
.step-card { background: var(--card-bg); border: 1px solid var(--border); border-radius: var(--radius); padding: 24px; margin-bottom: 20px; }
.step-card .step-num { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 50%; background: var(--primary); color: #fff; font-weight: 700; font-size: 14px; margin-right: 12px; }
.step-card h2 { display: flex; align-items: center; font-size: 18px; margin-bottom: 12px; }
.step-card p { color: var(--text-secondary); font-size: 14px; line-height: 1.7; margin-bottom: 8px; }
.step-card code { background: var(--bg); padding: 2px 6px; border-radius: 4px; font-size: 13px; }
.step-card .code-block { background: #1e1e2e; color: #cdd6f4; padding: 16px; border-radius: var(--radius-sm); font-size: 13px; line-height: 1.6; overflow-x: auto; margin: 12px 0; white-space: pre; }
.repo-banner { background: linear-gradient(135deg, #1a1a2e, #16213e); color: #fff; border-radius: var(--radius); padding: 24px; margin-bottom: 24px; text-align: center; }
.repo-banner h3 { font-size: 20px; margin-bottom: 8px; }
.repo-banner p { color: #a0aec0; font-size: 14px; margin-bottom: 16px; }
.repo-banner .btn { margin: 0 6px; }
</style>

<div class="page-header">
  <div class="page-header-left">
    <h1>提交插件</h1>
    <p class="page-desc">通过 GitHub Fork + Pull Request 发布你的插件</p>
  </div>
</div>

<div class="step-guide">

  <div class="repo-banner">
    <h3><i class="fas fa-file-alt"></i> yzdz666/yuexia-plugins</h3>
    <p>官方插件仓库 -- Fork 后提交你的插件</p>
    <a class="btn btn-primary" href="https://github.com/yzdz666/yuexia-plugins/fork" target="_blank"><i class="fas fa-code-branch"></i> Fork 仓库</a>
    <a class="btn btn-secondary" href="https://github.com/yzdz666/yuexia-plugins" target="_blank"><i class="fas fa-link"></i> 查看仓库</a>
    <a class="btn btn-secondary" href="https://github.com/yzdz666/yuexia-plugins/pulls" target="_blank"><i class="fas fa-code-pull-request"></i> 查看PR</a>
  </div>

  <div class="step-card">
    <h2><span class="step-num">1</span> Fork 官方插件仓库</h2>
    <p>打开上方链接将官方插件仓库 Fork 到你的 GitHub 账号下，作为你的插件工作副本。</p>
  </div>

  <div class="step-card">
    <h2><span class="step-num">2</span> 在你的 Fork 中创建插件</h2>
    <p>在你 Fork 后的仓库中，按以下结构添加你的插件：</p>
    <div class="code-block">your-fork/
├── plugins/
│   └── your-plugin-name/
│       ├── plugin.json    &lt;-- 插件清单文件（必填）
│       └── your-plugin.php &lt;-- 插件主文件（必填）</div>
    <p><strong>plugin.json</strong> 格式要求：</p>
    <div class="code-block">{
  "name": "your-plugin-name",
  "title": "你的插件标题",
  "description": "插件功能描述",
  "version": "1.0.0",
  "author": "你的名字",
  "main": "your-plugin.php",
  "license": "MIT",
  "tags": ["标签1", "标签2"],
  "category": "功能|娱乐|管理|工具",
  "min_framework_version": "1.0.0"
}</div>
    <p>详细规范请参考 <a href="../PLUGIN_SPEC.md" target="_blank">PLUGIN_SPEC.md</a></p>
  </div>

  <div class="step-card">
    <h2><span class="step-num">3</span> 提交 Pull Request</h2>
    <p>完成插件开发后，从你的 Fork 仓库向官方仓库提交 Pull Request。PR 提交后，管理员会在 GitHub 上审核你的代码，审核通过并合并后即发布到插件市场。</p>
    <p>
      <a class="btn btn-primary" href="https://github.com/yzdz666/yuexia-plugins/pulls" target="_blank"><i class="fas fa-code-pull-request"></i> 创建 Pull Request</a>
    </p>
  </div>

</div>

<?php require_once('footer.php'); ?>