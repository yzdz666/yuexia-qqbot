<?php
$pageTitle = '插件市场';
require_once('header.php');
?>
<style>
/* ==================== 插件市场 - 卡片布局 ==================== */
.marketplace-tabs {
    display: flex;
    gap: 4px;
    margin-bottom: 24px;
    padding: 4px;
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 10px;
    width: fit-content;
}
.tab-btn {
    padding: 8px 18px;
    border: none;
    background: transparent;
    border-radius: 7px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 500;
    color: var(--text-secondary);
    transition: var(--transition);
    position: relative;
}
.tab-btn:hover { color: var(--text); background: var(--bg); }
.tab-btn.active {
    background: var(--primary);
    color: #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.15);
}

/* 搜索框 */
.search-box input {
    width: 220px;
    padding: 8px 14px 8px 34px;
    border: 1px solid var(--border);
    border-radius: 8px;
    font-size: 13px;
    background: var(--card-bg) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%23adb5bd' stroke-width='2' stroke-linecap='round'%3E%3Ccircle cx='10' cy='10' r='7'/%3E%3Cline x1='21' y1='21' x2='15' y2='15'/%3E%3C/svg%3E") 12px center no-repeat;
    transition: var(--transition);
    outline: none;
}
.search-box input:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(51,51,51,0.08);
}

/* 插件网格 */
.plugin-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 16px;
}
.plugin-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    position: relative;
    overflow: hidden;
    cursor: pointer;
}
.plugin-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--border);
    transition: background 0.3s ease;
}
.plugin-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    border-color: var(--border-hover);
    transform: translateY(-1px);
}
.plugin-card:hover::before {
    background: var(--primary);
}
.plugin-card .card-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 10px;
}
.plugin-card .card-top h3 {
    font-size: 16px;
    font-weight: 600;
    margin: 0;
    line-height: 1.4;
}
.plugin-desc {
    color: var(--text-secondary);
    font-size: 13px;
    margin-bottom: 14px;
    line-height: 1.6;
    flex: 1;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.plugin-meta {
    display: flex;
    gap: 12px;
    font-size: 12px;
    color: var(--text-muted);
    margin-bottom: 10px;
    flex-wrap: wrap;
}
.plugin-meta span {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.submitter-avatar {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.avatar-mini {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    vertical-align: middle;
}
.builtin-badge {
    display: inline-block;
    padding: 1px 6px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 600;
    background: #e8f4fd;
    color: #0369a1;
    vertical-align: middle;
    margin-right: 4px;
}
.installed-badge {
    display: inline-block;
    padding: 1px 6px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 600;
    background: #dcfce7;
    color: #166534;
    vertical-align: middle;
    margin-right: 4px;
}
.plugin-card-installed {
    border-left: 3px solid var(--success) !important;
}
.plugin-card-builtin {
    border-left: 3px solid #93c5fd !important;
    cursor: default !important;
    opacity: 0.85;
}
.plugin-card-builtin:hover {
    transform: none !important;
    box-shadow: var(--shadow) !important;
}
.plugin-tags {
    display: flex;
    gap: 4px;
    flex-wrap: wrap;
    margin-bottom: 14px;
}
.plugin-tag {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 500;
}
.plugin-tag.cat-功能 { background: #e8f4e8; color: #2d6a4f; }
.plugin-tag.cat-娱乐 { background: #fef3e2; color: #b8860b; }
.plugin-tag.cat-管理 { background: #e3f0fa; color: #1a6ba0; }
.plugin-tag.cat-工具 { background: #f0e6fa; color: #6b3fa0; }
.plugin-tag.cat-社交 { background: #fce4ec; color: #b03a5e; }
.plugin-tag.cat-教育 { background: #e0f7fa; color: #00838f; }
.plugin-tag.cat-其他 { background: var(--bg); color: var(--text-muted); }
.plugin-tag.default { background: #f0f0f0; color: #555; }

/* 卡片底部动作 */
.plugin-actions {
    display: flex;
    gap: 8px;
    align-items: center;
    padding-top: 14px;
    border-top: 1px solid var(--border);
    flex-wrap: wrap;
}
.version-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 10px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
}
.version-badge.installed {
    background: #e8f5e9;
    color: #2e7d32;
}
.version-badge.latest {
    background: #e3f0fa;
    color: #1a6ba0;
}
.version-current {
    color: var(--success);
    font-size: 13px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-weight: 500;
}
.plugin-actions .btn {
    font-size: 12px;
    padding: 6px 14px;
    border-radius: 6px;
}

/* 同步横幅 */
.sync-banner {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
    color: #fff;
    border-radius: 12px;
    padding: 22px 26px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.1);
}
.sync-banner .sync-info { flex: 1; }
.sync-banner h3 { margin: 0 0 4px 0; font-size: 16px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
.sync-banner p { margin: 0; font-size: 13px; color: #94a3b8; }
.sync-banner .btn {
    font-size: 13px;
    padding: 8px 18px;
    border-radius: 8px;
    white-space: nowrap;
}
.sync-banner .btn-secondary {
    background: rgba(255,255,255,0.1);
    color: #e2e8f0;
    border: 1px solid rgba(255,255,255,0.15);
}
.sync-banner .btn-secondary:hover {
    background: rgba(255,255,255,0.18);
}
#syncMsg { font-size: 12px; margin-top: 6px; }

/* 加载与空状态 */
.plugin-grid .loading {
    grid-column: 1 / -1;
    padding: 40px 20px;
}
.loading-skeleton {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 16px;
    grid-column: 1 / -1;
}
.skeleton-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 20px;
    overflow: hidden;
}
.skeleton-line {
    height: 14px;
    border-radius: 4px;
    background: linear-gradient(90deg, var(--border) 25%, #e8e8e8 50%, var(--border) 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s ease-in-out infinite;
    margin-bottom: 10px;
}
.skeleton-line:first-child { width: 70%; height: 18px; }
.skeleton-line:nth-child(2) { width: 45%; }
.skeleton-line:nth-child(3) { width: 90%; }
.skeleton-line:nth-child(4) { width: 55%; }
.skeleton-line:last-child { width: 35%; height: 32px; border-radius: 6px; margin-bottom: 0; margin-top: 14px; }
@keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
@keyframes spin {
    to { transform: rotate(360deg); }
}
.plugin-grid .error {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
    color: var(--danger);
}
.plugin-grid .empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
    color: var(--text-muted);
}
.plugin-grid .empty-state::before {
    content: '\f002';
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    display: block;
    font-size: 32px;
    margin-bottom: 12px;
    opacity: 0.4;
}

/* 响应式 */
@media (max-width: 768px) {
    .plugin-grid {
        grid-template-columns: 1fr;
    }
    .loading-skeleton {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    .skeleton-card {
        padding: 16px;
    }
    .search-box input {
        width: 100%;
    }
    .sync-banner {
        flex-direction: column;
        text-align: center;
    }
    .marketplace-tabs {
        width: 100%;
        justify-content: center;
    }
}

/* 操作指南 */
.guide-box {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 10px;
    margin-bottom: 16px;
    overflow: hidden;
    transition: var(--transition);
}
.guide-box:hover {
    border-color: var(--border-hover);
}
.guide-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 20px;
    cursor: pointer;
    user-select: none;
    -webkit-tap-highlight-color: transparent;
}
.guide-title {
    font-weight: 600;
    font-size: 14px;
    color: var(--text);
    display: flex;
    align-items: center;
    gap: 8px;
}
.guide-title i {
    color: #f0b429;
    font-size: 16px;
}
.guide-hint {
    font-size: 12px;
    color: var(--text-muted);
    margin-right: 6px;
}
.guide-arrow {
    display: inline-block;
    transition: transform 0.3s ease;
    color: var(--text-muted);
}
.guide-arrow.open {
    transform: rotate(180deg);
}
.guide-body {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.35s ease, padding 0.3s ease;
}
.guide-body.open {
    max-height: 500px;
    padding: 0 20px 18px 20px;
}
.guide-steps {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 12px;
}
.guide-step {
    display: flex;
    gap: 12px;
    align-items: flex-start;
    padding: 14px;
    background: var(--bg);
    border-radius: 8px;
    transition: var(--transition);
}
.guide-step:hover {
    background: #f0f0f0;
}
.guide-step-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: var(--card-bg);
    border: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 15px;
    color: var(--primary);
}
.guide-step-content {
    flex: 1;
    min-width: 0;
}
.guide-step-content strong {
    display: block;
    font-size: 13px;
    margin-bottom: 3px;
    color: var(--text);
}
.guide-step-content span {
    font-size: 12px;
    color: var(--text-secondary);
    line-height: 1.5;
}
</style>

<div class="page-header">
  <div class="page-header-left">
    <h1>插件市场</h1>
    <p class="page-desc">从 GitHub 仓库安装和管理插件</p>
  </div>
  <div class="page-header-right">
    <div class="search-box">
      <input type="text" id="searchInput" placeholder="搜索插件..." oninput="filterPlugins()">
    </div>
    <button class="btn btn-primary" onclick="location.href='submit_plugin.php'"><i class="fas fa-upload"></i> 提交插件</button>
    <button class="btn btn-secondary" onclick="loadPlugins()"><i class="fas fa-sync-alt"></i> 刷新</button>
  </div>
</div>

<!-- 傻瓜式操作指南 -->
<div class="guide-box" id="guideBox">
  <div class="guide-header" onclick="toggleGuide()">
    <span class="guide-title"><i class="fas fa-lightbulb"></i> 操作指南</span>
    <div>
      <span class="guide-hint">点我展开</span>
      <span class="guide-arrow"><i class="fas fa-chevron-down"></i></span>
    </div>
  </div>
  <div class="guide-body" id="guideBody">
    <div class="guide-steps">
      <div class="guide-step">
        <div class="guide-step-icon"><i class="fas fa-search"></i></div>
        <div class="guide-step-content">
          <strong>浏览插件</strong>
          <span>在下面列表中查看所有可用插件，可以用搜索框按名称或描述查找</span>
        </div>
      </div>
      <div class="guide-step">
        <div class="guide-step-icon"><i class="fas fa-download"></i></div>
        <div class="guide-step-content">
          <strong>安装插件</strong>
          <span>找到想要的插件，点击卡片上的「安装」按钮，系统会自动从 GitHub 下载并安装</span>
        </div>
      </div>
      <div class="guide-step">
        <div class="guide-step-icon"><i class="fas fa-sync-alt"></i></div>
        <div class="guide-step-content">
          <strong>更新插件</strong>
          <span>有新版时可切换到「可更新」标签，点击「更新到 vX.X」一键升级</span>
        </div>
      </div>
      <div class="guide-step">
        <div class="guide-step-icon"><i class="fas fa-upload"></i></div>
        <div class="guide-step-content">
          <strong>发布自己的插件</strong>
          <span>点击右上角「提交插件」，按步骤 Fork 官方仓库、添加插件文件、提交 Pull Request 即可</span>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- 同步横幅 -->
<div id="syncBanner" class="sync-banner" style="display:none;">
  <div class="sync-info">
    <h3 id="syncRepoName"><i class="fas fa-box"></i> 官方插件仓库</h3>
    <p id="syncRepoDesc">插件来源于 GitHub 仓库，审核通过并合并后同步到市场</p>
    <div id="syncMsg"></div>
  </div>
  <div style="display:flex; gap:8px; flex-shrink:0;">
    <button class="btn btn-secondary" onclick="reviewPendingPRs()"><i class="fas fa-robot"></i> 审核待处理 PR</button>
    <button class="btn btn-primary" onclick="syncFromGithub()"><i class="fas fa-sync-alt"></i> 从 GitHub 同步</button>
  </div>
</div>

<div class="marketplace-tabs">
  <button class="tab-btn active" data-tab="all" onclick="switchTab('all')">全部插件</button>
  <button class="tab-btn" data-tab="installed" onclick="switchTab('installed')">已安装</button>
  <button class="tab-btn" data-tab="updatable" onclick="switchTab('updatable')">可更新</button>
</div>

<div id="pluginGrid" class="plugin-grid">
  <div class="loading-skeleton">
    <div class="skeleton-card"><div class="skeleton-line"></div><div class="skeleton-line"></div><div class="skeleton-line"></div><div class="skeleton-line"></div><div class="skeleton-line"></div></div>
    <div class="skeleton-card"><div class="skeleton-line"></div><div class="skeleton-line"></div><div class="skeleton-line"></div><div class="skeleton-line"></div><div class="skeleton-line"></div></div>
    <div class="skeleton-card"><div class="skeleton-line"></div><div class="skeleton-line"></div><div class="skeleton-line"></div><div class="skeleton-line"></div><div class="skeleton-line"></div></div>
  </div>
</div>

<div id="confirmModal" class="modal-overlay" style="display:none">
  <div class="modal">
    <div class="modal-header">
      <h3 id="modalTitle">确认操作</h3>
    </div>
    <div class="modal-body" id="modalBody">确定要执行此操作吗？</div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal()">取消</button>
      <button class="btn btn-danger" id="modalConfirmBtn" onclick="confirmAction()">确认</button>
    </div>
  </div>
</div>

<!-- 插件详情弹窗 -->
<div id="detailModal" class="modal-overlay" style="display:none" onclick="if(event.target===this)closeDetailModal()">
  <div class="modal" style="max-width:640px;">
    <div class="modal-header">
      <h3 id="detailTitle">插件详情</h3>
      <button class="btn btn-secondary btn-sm" onclick="closeDetailModal()" style="position:absolute;right:16px;top:16px;">✕</button>
    </div>
    <div class="modal-body" id="detailBody" style="min-height:100px;font-size:14px;line-height:1.7;color:var(--text-secondary);">
      加载中...
    </div>
    <div class="modal-footer" id="detailFooter" style="display:flex;gap:8px;flex-wrap:wrap;">
      <button class="btn btn-primary" id="detailInstallBtn" style="display:none;"><i class="fas fa-download"></i> 安装</button>
      <button class="btn btn-secondary" onclick="closeDetailModal()">关闭</button>
    </div>
  </div>
</div>

<script>
var API = 'api/marketplace_api.php';
var allPlugins = [];
var currentTab = 'all';

function loadPlugins() {
    fetch(API + '?type=list')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.code === 200) {
                allPlugins = data.plugins || [];
                renderPlugins();
            } else {
                document.getElementById('pluginGrid').innerHTML = '<div class="error">加载失败</div>';
            }
        })
        .catch(function(e) {
            document.getElementById('pluginGrid').innerHTML = '<div class="error">网络错误: ' + e.message + '</div>';
        });
}

function renderPlugins() {
    var searchVal = document.getElementById('searchInput').value.toLowerCase();
    var filtered = allPlugins.filter(function(p) {
        if (searchVal && p.title.toLowerCase().indexOf(searchVal) === -1 && 
            p.name.toLowerCase().indexOf(searchVal) === -1 &&
            p.description.toLowerCase().indexOf(searchVal) === -1) {
            return false;
        }
        if (currentTab === 'installed' && !p.installed) return false;
        if (currentTab === 'updatable' && !p.has_update) return false;
        return true;
    });

    var grid = document.getElementById('pluginGrid');
    if (filtered.length === 0) {
        grid.innerHTML = '<div class="empty-state">没有找到匹配的插件</div>';
        return;
    }

    grid.innerHTML = filtered.map(function(p) { return createPluginCard(p); }).join('');
}

function createPluginCard(p) {
    var tagsHtml = (p.tags || []).map(function(t) {
        var catClass = 'default';
        if (p.category) {
            catClass = 'cat-' + p.category;
        }
        return '<span class="plugin-tag ' + catClass + '">' + t + '</span>';
    }).join('');
    var actionBtn = '';
    var versionBadge = '';
    var builtinBadge = p.builtin ? '<span class="builtin-badge"><i class="fas fa-star"></i> 系统内置</span>' : '';
    var installedBadge = p.installed && !p.builtin ? '<span class="installed-badge"><i class="fas fa-check"></i> 已安装</span>' : '';
    var nameWithBadge = (builtinBadge ? builtinBadge + ' ' : '') + (installedBadge ? installedBadge + ' ' : '') + (p.title || p.name);
    var cardClass = 'plugin-card' + (p.installed ? ' plugin-card-installed' : '') + (p.builtin ? ' plugin-card-builtin' : '');

    if (p.installed) {
        versionBadge = '<span class="version-badge installed">v' + p.installed_version + '</span>';
        if (p.has_update) {
            actionBtn = '<button class="btn btn-primary btn-sm" onclick="event.stopPropagation();updatePlugin(\'' + p.name + '\')"><i class="fas fa-sync-alt"></i> 更新到 v' + p.version + '</button>';
            if (!p.builtin) actionBtn += '<button class="btn btn-secondary btn-sm" onclick="event.stopPropagation();uninstallPlugin(\'' + p.name + '\')"><i class="fas fa-trash-alt"></i> 卸载</button>';
        } else {
            actionBtn = '<span class="version-current"><i class="fas fa-check-circle"></i> 已安装</span>';
            if (!p.builtin) actionBtn += '<button class="btn btn-secondary btn-sm" onclick="event.stopPropagation();uninstallPlugin(\'' + p.name + '\')"><i class="fas fa-trash-alt"></i> 卸载</button>';
        }
    } else {
        versionBadge = '<span class="version-badge latest">v' + p.version + '</span>';
        actionBtn = '<button class="btn btn-primary btn-sm" onclick="event.stopPropagation();installPlugin(\'' + p.name + '\')"><i class="fas fa-download"></i> 安装</button>';
    }

    var hasLink = !p.builtin && (p.homepage || (p.repository && p.repository.url));
    var detailBtn = hasLink ? '<button class="btn btn-outline btn-sm" onclick="event.stopPropagation();openPluginUrl(\'' + p.name + '\')"><i class="fas fa-external-link-alt"></i></button>' : '';
    var titleLink = hasLink ? '<a href="javascript:void(0)" onclick="event.stopPropagation();openPluginUrl(\'' + p.name + '\')" style="text-decoration:none;color:inherit;" title="查看项目主页">' + nameWithBadge + '</a>' : nameWithBadge;
    var cardClick = p.builtin ? '' : ' onclick="showPluginDetail(\'' + p.name + '\')"';
    var cardTitle = p.builtin ? '系统内置插件' : (p.description || '');

    return '<div class="' + cardClass + '" title="' + cardTitle + '"' + cardClick + '>' +
        '<div class="card-top">' +
            '<h3>' + titleLink + '</h3>' +
            versionBadge +
        '</div>' +
        '<p class="plugin-desc">' + (p.description || '暂无描述') + '</p>' +
        '<div class="plugin-meta">' +
            (p.submitter ? '<span class="submitter-avatar"><img src="' + p.submitter.avatar + '" class="avatar-mini"> ' + p.submitter.login + '</span>' : '<span><i class="fas fa-user"></i> ' + (p.author || '未知') + '</span>') +
            '<span><i class="fas fa-folder"></i> ' + (p.category || '未分类') + '</span>' +
        '</div>' +
        '<div class="plugin-tags">' + tagsHtml + '</div>' +
        '<div class="plugin-actions">' + actionBtn + detailBtn + '</div>' +
    '</div>';
}

function installPlugin(name) {
    var btn = event.target;
    btn.disabled = true;
    btn.textContent = '安装中...';

    fetch(API, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'type=install&plugin_name=' + encodeURIComponent(name) + '&csrf_token=' + getCsrfToken()
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.code === 200) {
            alert('安装成功！版本: ' + data.version);
        } else {
            alert('安装失败: ' + data.msg);
        }
        loadPlugins();
    })
    .catch(function() {
        alert('网络错误');
        loadPlugins();
    });
}

function updatePlugin(name) {
    var btn = event.target;
    btn.disabled = true;
    btn.textContent = '更新中...';

    fetch(API, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'type=update&plugin_name=' + encodeURIComponent(name) + '&csrf_token=' + getCsrfToken()
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.code === 200) {
            alert('更新成功！');
        } else {
            alert('更新失败: ' + data.msg);
        }
        loadPlugins();
    })
    .catch(function() {
        alert('网络错误');
        loadPlugins();
    });
}

function uninstallPlugin(name) {
    if (!confirm('确定要卸载 "' + name + '" 插件吗？\n此操作不可撤销！')) return;

    fetch(API, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'type=uninstall&plugin_name=' + encodeURIComponent(name) + '&csrf_token=' + getCsrfToken()
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.code === 200) {
            alert('卸载成功');
        } else {
            alert('卸载失败: ' + data.msg);
        }
        loadPlugins();
    });
}

function switchTab(tab) {
    currentTab = tab;
    document.querySelectorAll('.tab-btn').forEach(function(b) {
        b.classList.toggle('active', b.dataset.tab === tab);
    });
    renderPlugins();
}

function filterPlugins() {
    renderPlugins();
}

function toggleGuide() {
    var body = document.getElementById('guideBody');
    var arrow = document.querySelector('.guide-arrow');
    var hint = document.querySelector('.guide-hint');
    body.classList.toggle('open');
    arrow.classList.toggle('open');
    if (body.classList.contains('open')) {
        hint.textContent = '收起';
    } else {
        hint.textContent = '点我展开';
    }
}

function getCsrfToken() {
    var meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : '';
}

function openPluginUrl(name) {
    var plugin = allPlugins.find(function(p) { return p.name === name; });
    if (!plugin) return;
    var url = plugin.homepage || (plugin.repository && plugin.repository.url) || plugin.downloadUrl || '';
    if (url) window.open(url, '_blank');
}

function showPluginDetail(name) {
    var plugin = allPlugins.find(function(p) { return p.name === name; });
    if (!plugin) return;
    
    document.getElementById('detailTitle').textContent = plugin.title || plugin.name;
    
    var body = document.getElementById('detailBody');
    var html = '';
    
    html += '<div style="display:flex;gap:12px;align-items:center;margin-bottom:16px;">';
    html += '<div style="width:48px;height:48px;border-radius:8px;background:var(--bg);display:flex;align-items:center;justify-content:center;font-size:20px;color:var(--text-muted);">';
    html += (plugin.name || '?')[0].toUpperCase();
    html += '</div>';
    html += '<div><div style="font-weight:600;font-size:16px;color:var(--text);">' + (plugin.title || plugin.name) + '</div>';
    html += '<div style="font-size:12px;color:var(--text-muted);">v' + plugin.version + ' · ' + (plugin.author || '未知') + '</div></div></div>';
    
    html += '<div style="margin-bottom:12px;">' + (plugin.description || '暂无描述') + '</div>';
    
    if (plugin.tags && plugin.tags.length) {
        html += '<div style="margin-bottom:12px;display:flex;gap:4px;flex-wrap:wrap;">';
        plugin.tags.forEach(function(t) { html += '<span style="padding:2px 8px;background:var(--bg);border-radius:4px;font-size:11px;color:var(--text-secondary);">' + t + '</span>'; });
        html += '</div>';
    }
    
    html += '<table style="width:100%;font-size:13px;">';
    html += '<tr><td style="padding:4px 8px;color:var(--text-muted);">作者</td><td>' + (plugin.author || '未知') + '</td></tr>';
    html += '<tr><td style="padding:4px 8px;color:var(--text-muted);">分类</td><td>' + (plugin.category || '未分类') + '</td></tr>';
    html += '<tr><td style="padding:4px 8px;color:var(--text-muted);">版本</td><td>v' + plugin.version + '</td></tr>';
    html += '<tr><td style="padding:4px 8px;color:var(--text-muted);">协议</td><td>' + (plugin.license || 'MIT') + '</td></tr>';
    
    var projUrl = plugin.homepage || (plugin.repository && plugin.repository.url) || '';
    if (projUrl) {
        html += '<tr><td style="padding:4px 8px;color:var(--text-muted);">项目</td><td><a href="' + projUrl + '" target="_blank" style="color:var(--primary);">' + projUrl.replace(/^https?:\/\//, '').substring(0, 40) + '</a></td></tr>';
    }
    if (plugin.downloadUrl) {
        html += '<tr><td style="padding:4px 8px;color:var(--text-muted);">下载</td><td style="font-size:11px;color:var(--text-muted);word-break:break-all;">' + plugin.downloadUrl.replace(/^https?:\/\//, '').substring(0, 50) + '</td></tr>';
    }
    html += '</table>';
    
    body.innerHTML = html;
    
    // 底部按钮
    var footer = document.getElementById('detailFooter');
    footer.innerHTML = '';
    if (projUrl) {
        var projBtn = document.createElement('a');
        projBtn.href = projUrl;
        projBtn.target = '_blank';
        projBtn.className = 'btn btn-outline btn-sm';
        projBtn.innerHTML = '<i class="fas fa-external-link-alt"></i> 查看项目';
        footer.appendChild(projBtn);
    }
    if (!plugin.installed) {
        var installBtn = document.createElement('button');
        installBtn.className = 'btn btn-primary btn-sm';
        installBtn.innerHTML = '<i class="fas fa-download"></i> 安装';
        installBtn.onclick = function() { installPlugin(plugin.name); closeDetailModal(); };
        footer.appendChild(installBtn);
    } else if (!plugin.builtin) {
        var uninstallBtn = document.createElement('button');
        uninstallBtn.className = 'btn btn-secondary btn-sm';
        uninstallBtn.innerHTML = '<i class="fas fa-trash-alt"></i> 卸载';
        uninstallBtn.onclick = function() { uninstallPlugin(plugin.name); closeDetailModal(); };
        footer.appendChild(uninstallBtn);
    }
    var closeBtn = document.createElement('button');
    closeBtn.className = 'btn btn-secondary btn-sm';
    closeBtn.textContent = '关闭';
    closeBtn.onclick = closeDetailModal;
    footer.appendChild(closeBtn);
    
    document.getElementById('detailModal').style.display = 'flex';
}

function closeDetailModal() {
    document.getElementById('detailModal').style.display = 'none';
}

function loadRepoInfo() {
    fetch('api/github_auth.php?type=repo_info')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.code === 200) {
                document.getElementById('syncBanner').style.display = 'flex';
                document.getElementById('syncRepoName').innerHTML = '<i class="fas fa-box"></i> ' + data.owner + '/' + data.repo;
            }
        })
        .catch(function() {
            document.getElementById('syncBanner').style.display = 'none';
        });
}

function syncFromGithub() {
    var btn = event.target;
    btn.disabled = true;
    btn.textContent = '同步中...';
    document.getElementById('syncMsg').innerHTML = '正在从GitHub同步...';

    fetch('api/github_auth.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'type=sync_from_github&csrf_token=' + getCsrfToken()
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var msg = document.getElementById('syncMsg');
        if (data.code === 200) {
            msg.innerHTML = '<span style="color: var(--success);"><i class="fas fa-check-circle"></i> ' + data.msg + '</span>';
            loadPlugins();
        } else {
            msg.innerHTML = '<span style="color: var(--danger);"><i class="fas fa-times-circle"></i> ' + data.msg + '</span>';
        }
    })
    .catch(function() {
        document.getElementById('syncMsg').innerHTML = '<span style="color: var(--danger);"><i class="fas fa-times-circle"></i> 同步失败</span>';
    })
    .finally(function() {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-sync-alt"></i> 从 GitHub 同步';
    });
}

function reviewPendingPRs() {
    var btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 审核中...';
    document.getElementById('syncMsg').innerHTML = '正在审核待处理的 PR...';

    fetch('api/github_auth.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'type=review_pending_prs&csrf_token=' + getCsrfToken()
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var msg = document.getElementById('syncMsg');
        if (data.code === 200) {
            msg.innerHTML = '<span style="color: var(--success);"><i class="fas fa-check-circle"></i> ' + data.msg + '</span>';
        } else {
            msg.innerHTML = '<span style="color: var(--danger);"><i class="fas fa-times-circle"></i> ' + data.msg + '</span>';
        }
    })
    .catch(function() {
        document.getElementById('syncMsg').innerHTML = '<span style="color: var(--danger);"><i class="fas fa-times-circle"></i> 审核请求失败</span>';
    })
    .finally(function() {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-robot"></i> 审核待处理 PR';
    });
}

document.addEventListener('DOMContentLoaded', function() {
    loadPlugins();
    loadRepoInfo();
});
</script>

<?php require_once('footer.php'); ?>