<?php
$pageTitle = 'GitHub设置';
require_once('header.php');
?>
<style>
.github-setup { max-width: 600px; margin: 0 auto; }
.form-group { margin-bottom: 20px; }
.form-group label { display: block; margin-bottom: 6px; font-weight: 500; color: var(--text); }
.form-group .help-text { font-size: 12px; color: var(--text-muted); margin-top: 4px; }
.form-group select, .form-group input { width: 100%; }
.github-status { display: flex; align-items: center; gap: 12px; padding: 16px; background: var(--card-bg); border: 1px solid var(--border); border-radius: var(--radius); margin-bottom: 20px; }
.github-status img { width: 40px; height: 40px; border-radius: 50%; }
.github-status .info { flex: 1; }
.github-status .info .name { font-weight: 600; }
.github-status .info .login { color: var(--text-muted); font-size: 12px; }
.mirror-options { display: flex; flex-direction: column; gap: 10px; }
.mirror-presets { display: flex; gap: 8px; flex-wrap: wrap; }
.mirror-preset-btn { padding: 6px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); background: var(--card-bg); cursor: pointer; font-size: 12px; transition: var(--transition); }
.mirror-preset-btn:hover { border-color: var(--primary); }
.mirror-preset-btn.active { background: var(--primary); color: white; border-color: var(--primary); }
.skeleton-line {
    height: 14px;
    border-radius: 4px;
    background: linear-gradient(90deg, var(--border) 25%, #e8e8e8 50%, var(--border) 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s ease-in-out infinite;
}
@keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
</style>

<div class="page-header">
  <div class="page-header-left">
    <h1>GitHub设置</h1>
    <p class="page-desc">配置GitHub OAuth和镜像站，用于插件市场的发布和下载</p>
  </div>
</div>

<div class="github-setup">
  <!-- GitHub连接状态 -->
  <div class="card" style="margin-bottom: 20px;">
    <div class="card-header"><h3>GitHub 连接状态</h3></div>
    <div class="card-body">
      <div id="githubStatus">
        <div class="skeleton-line" style="width:60%;height:16px;margin-bottom:8px;"></div>
        <div class="skeleton-line" style="width:40%;height:14px;"></div>
      </div>
    </div>
  </div>

  <!-- 镜像站设置 -->
  <div class="card" style="margin-bottom: 20px;">
    <div class="card-header"><h3>镜像站配置</h3></div>
    <div class="card-body">
      <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 16px;">
        选择GitHub镜像站可以加速国内服务器的插件下载：
      </p>
      <div class="mirror-options">
        <div class="mirror-presets" id="mirrorPresets">
          <button class="mirror-preset-btn" data-url="https://github.com"><i class="fas fa-globe"></i> GitHub官方</button>
          <button class="mirror-preset-btn" data-url="https://gitclone.com/github.com"><i class="fas fa-rocket"></i> GitClone</button>
          <button class="mirror-preset-btn" data-url="https://hub.gitfast.xyz"><i class="fas fa-bolt"></i> GitFast</button>
          <button class="mirror-preset-btn" data-url="https://ghproxy.net"><i class="fas fa-forward"></i> GhProxy</button>
          <button class="mirror-preset-btn" data-url="https://gh.api.cachefly.net"><i class="fas fa-cloud"></i> CacheFly</button>
          <button class="mirror-preset-btn" data-url="https://ghproxy.com"><i class="fas fa-files"></i> GhProxy.com</button>
          <button class="mirror-preset-btn" data-url="https://hub.nuaa.cf"><i class="fas fa-graduation-cap"></i> NUAA</button>
          <button class="mirror-preset-btn" data-url="https://github.moeyy.xyz"><i class="fas fa-microscope"></i> Moeyy</button>
          <button class="mirror-preset-btn" data-url="custom"><i class="fas fa-pen"></i> 自定义</button>
        </div>
        <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
          <div class="form-group" style="margin-bottom:0; flex:1; min-width:200px;">
            <input type="text" id="mirrorUrl" placeholder="https://github.com" value="https://github.com">
          </div>
          <button class="btn btn-secondary btn-sm" onclick="testMirrorSpeed()" id="speedTestBtn"><i class="fas fa-tachometer-alt"></i> 测速</button>
          <button class="btn btn-primary btn-sm" onclick="saveMirrorSetting()">保存</button>
        </div>
        <div id="speedTestResults" style="margin-top:8px; display:flex; gap:6px; flex-wrap:wrap;"></div>
        <div id="mirrorSaveMsg" style="font-size: 12px; margin-top: 4px;"></div>
      </div>
    </div>
  </div>

  <!-- 代理设置 -->
  <div class="card" style="margin-bottom: 20px;">
    <div class="card-header"><h3>代理配置</h3></div>
    <div class="card-body">
      <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 16px;">
        配置 HTTP 代理转发 API 请求，解决服务器无法直连 GitHub/AI 服务的问题
      </p>
      <div class="form-group">
        <label>代理地址</label>
        <input type="text" id="proxyUrl" placeholder="留空不使用代理" value="">
        <div class="help-text">格式: http://127.0.0.1:7890 或 http://user:pass@proxy:port</div>
      </div>
      <div class="form-group">
        <label>代理协议</label>
        <div style="display:flex; gap:12px;">
          <label style="font-weight:400;font-size:13px;"><input type="radio" name="proxyType" value="http" checked> HTTP</label>
          <label style="font-weight:400;font-size:13px;"><input type="radio" name="proxyType" value="socks5"> SOCKS5</label>
        </div>
      </div>
      <button class="btn btn-primary" onclick="saveProxySetting()">保存代理设置</button>
      <button class="btn btn-secondary btn-sm" onclick="testProxy()" id="testProxyBtn" style="margin-left:8px;">测试连接</button>
      <div id="proxySaveMsg" style="font-size: 12px; margin-top: 4px;"></div>
    </div>
  </div>

  <!-- OAuth设置 -->
  <div class="card" style="margin-bottom: 20px;">
    <div class="card-header"><h3>官方插件仓库</h3></div>
    <div class="card-body">
      <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 16px;">
        插件市场的官方 GitHub 仓库地址（owner/repo），用于 Fork 提交和同步
      </p>
      <div class="form-group">
        <label>仓库地址</label>
        <div style="display:flex; gap:8px;">
          <span style="line-height: 36px; color: var(--text-muted);">github.com/</span>
          <input type="text" id="officialRepo" placeholder="yuexia-php/plugins" style="flex:1;">
        </div>
        <div class="help-text">格式：owner/repo，例如 yuexia-php/plugins</div>
      </div>
      <button class="btn btn-primary" onclick="saveOfficialRepo()">保存仓库设置</button>
      <div id="repoSaveMsg" style="font-size: 12px; margin-top: 4px;"></div>
    </div>
  </div>
</div>

<script>
function loadGithubStatus() {
    fetch('api/github_auth.php?type=status')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var html = '';
            if (data.logged_in) {
                var u = data.user;
                html = '<div class="github-status">' +
                    '<img src="' + u.avatar + '" alt="avatar">' +
                    '<div class="info">' +
                        '<div class="name">' + (u.name || u.login) + '</div>' +
                        '<div class="login">@' + u.login + '</div>' +
                    '</div>' +
                '</div>';
            } else {
                html = '<div class="github-status">' +
                    '<div style="flex:1; color: var(--text-muted);">未连接 GitHub</div>' +
                '</div>';
            }
            document.getElementById('githubStatus').innerHTML = html;
        })
        .catch(function() {
            document.getElementById('githubStatus').innerHTML = '<div class="error">加载失败</div>';
        });
}

function loadSettings() {
    fetch('api/github_auth.php?type=get_settings')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.code === 200) {
                var s = data.settings;
                document.getElementById('mirrorUrl').value = s.mirror_url || 'https://github.com';
                highlightMirrorPreset(s.mirror_url || 'https://github.com');
                document.getElementById('officialRepo').value = s.official_repo || 'yuexia-php/plugins';
                document.getElementById('proxyUrl').value = s.proxy_url || '';
                if (s.proxy_type) {
                    var radios = document.querySelectorAll('input[name="proxyType"]');
                    for (var i = 0; i < radios.length; i++) {
                        radios[i].checked = radios[i].value === s.proxy_type;
                    }
                }
            }
        });
}

function saveMirrorSetting() {
    var url = document.getElementById('mirrorUrl').value.trim();
    if (!url) { alert('请输入镜像站地址'); return; }

    fetch('api/github_auth.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'type=save_settings&csrf_token=' + getCsrfToken() + '&mirror_url=' + encodeURIComponent(url)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var msg = document.getElementById('mirrorSaveMsg');
        if (data.code === 200) {
            msg.innerHTML = '<span style="color: var(--success);"><i class="fas fa-check-circle"></i> 镜像站已设置为: ' + url + '</span>';
            highlightMirrorPreset(url);
        } else {
            msg.innerHTML = '<span style="color: var(--danger);"><i class="fas fa-times-circle"></i> ' + data.msg + '</span>';
        }
        setTimeout(function() { msg.innerHTML = ''; }, 3000);
    });
}

function highlightMirrorPreset(url) {
    document.querySelectorAll('.mirror-preset-btn').forEach(function(b) {
        b.classList.toggle('active', b.dataset.url === url);
    });
}

function testMirrorSpeed() {
    var btn = document.getElementById('speedTestBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 测速中...';
    
    var mirrors = [
        {name:'GitHub官方', url:'https://github.com'},
        {name:'GitClone', url:'https://gitclone.com/github.com'},
        {name:'GitFast', url:'https://hub.gitfast.xyz'},
        {name:'GhProxy', url:'https://ghproxy.net'},
        {name:'CacheFly', url:'https://gh.api.cachefly.net'},
        {name:'GhProxy.com', url:'https://ghproxy.com'},
        {name:'NUAA', url:'https://hub.nuaa.cf'},
        {name:'Moeyy', url:'https://github.moeyy.xyz'}
    ];
    
    var resultsDiv = document.getElementById('speedTestResults');
    resultsDiv.innerHTML = '';
    
    mirrors.forEach(function(m) {
        var chip = document.createElement('span');
        chip.style.cssText = 'display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:6px;font-size:12px;border:1px solid var(--border);background:var(--card-bg);';
        chip.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + m.name;
        resultsDiv.appendChild(chip);
        
        var start = Date.now();
        var img = new Image();
        var timeout = setTimeout(function() {
            chip.innerHTML = '<i class="fas fa-times-circle" style="color:var(--danger);"></i> ' + m.name + ' 超时';
            chip.style.borderColor = '#fecaca';
            checkDone();
        }, 5000);
        
        img.onload = function() {
            clearTimeout(timeout);
            var ms = Date.now() - start;
            var color = ms < 500 ? 'var(--success)' : ms < 1500 ? 'var(--warning)' : 'var(--danger)';
            chip.innerHTML = '<i class="fas fa-circle" style="color:' + color + ';font-size:8px;"></i> ' + m.name + ' ' + ms + 'ms';
            chip.style.borderColor = ms < 500 ? '#bbf7d0' : ms < 1500 ? '#fef08a' : '#fecaca';
            checkDone();
        };
        img.onerror = function() {
            clearTimeout(timeout);
            chip.innerHTML = '<i class="fas fa-times-circle" style="color:var(--danger);"></i> ' + m.name + ' 不通';
            chip.style.borderColor = '#fecaca';
            checkDone();
        };
        img.src = m.url + '/favicon.ico?' + start;
    });
    
    var results = [];
    var tested = 0;
    function checkDone() {
        tested++;
        if (tested >= mirrors.length) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-tachometer-alt"></i> 重新测速';
            // 自动选择最快的可用镜像
            var chips = resultsDiv.querySelectorAll('span');
            var fastest = null;
            var fastestMs = Infinity;
            chips.forEach(function(c) {
                var match = c.textContent.match(/^(\S+)\s+(\d+)ms$/);
                if (match) {
                    var ms = parseInt(match[2]);
                    if (ms < fastestMs) { fastestMs = ms; fastest = match[1]; }
                }
            });
            if (fastest) {
                var fastestUrl = '';
                for (var i = 0; i < mirrors.length; i++) {
                    if (mirrors[i].name === fastest) { fastestUrl = mirrors[i].url; break; }
                }
                if (fastestUrl) {
                    document.getElementById('mirrorUrl').value = fastestUrl;
                    highlightMirrorPreset(fastestUrl);
                    // 自动保存
                    saveMirrorSettingSilent(fastestUrl);
                    resultsDiv.innerHTML += '<div style="width:100%;margin-top:4px;font-size:12px;color:var(--success);"><i class="fas fa-check-circle"></i> 已自动选择最快镜像: ' + fastest + ' (' + fastestMs + 'ms)</div>';
                }
            }
        }
    }
}

function saveMirrorSettingSilent(url) {
    fetch('api/github_auth.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'type=save_settings&csrf_token=' + getCsrfToken() + '&mirror_url=' + encodeURIComponent(url)
    });
}

function saveOfficialRepo() {
    var repo = document.getElementById('officialRepo').value.trim();
    if (!repo) { alert('请输入仓库地址'); return; }
    if (repo.split('/').length !== 2) { alert('格式错误，请输入 owner/repo 格式'); return; }

    var btn = event.target;
    btn.disabled = true;
    btn.textContent = '保存中...';

    fetch('api/github_auth.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'type=save_settings&csrf_token=' + getCsrfToken() + '&official_repo=' + encodeURIComponent(repo)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var msg = document.getElementById('repoSaveMsg');
        if (data.code === 200) {
            msg.innerHTML = '<span style="color: var(--success);"><i class="fas fa-check-circle"></i> 仓库已设置为: ' + repo + '</span>';
        } else {
            msg.innerHTML = '<span style="color: var(--danger);"><i class="fas fa-times-circle"></i> ' + data.msg + '</span>';
        }
        setTimeout(function() { msg.innerHTML = ''; }, 3000);
    })
    .catch(function() {
        document.getElementById('repoSaveMsg').innerHTML = '<span style="color: var(--danger);"><i class="fas fa-times-circle"></i> 保存失败</span>';
    })
    .finally(function() {
        btn.disabled = false;
        btn.textContent = '保存仓库设置';
    });
}

function createOAuthApp() {
    var btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 跳转中...';
    
    fetch('api/github_auth.php?type=create_oauth_manifest')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.code === 200 && data.url) {
                // 显示 callback URL 供用户确认
                document.getElementById('redirectUri').value = data.callback_url;
                // 跳转到 GitHub 创建页面
                window.location.href = data.url;
            } else {
                document.getElementById('autoSetupMsg').innerHTML = '<span class="error-msg">' + (data.msg || '获取链接失败') + '</span>';
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-magic"></i> 一键创建 OAuth App';
            }
        })
        .catch(function() {
            document.getElementById('autoSetupMsg').innerHTML = '<span class="error-msg">网络错误</span>';
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-magic"></i> 一键创建 OAuth App';
        });
}

document.getElementById('mirrorPresets').addEventListener('click', function(e) {
    if (e.target.classList.contains('mirror-preset-btn')) {
        var url = e.target.dataset.url;
        if (url === 'custom') {
            document.getElementById('mirrorUrl').focus();
            return;
        }
        document.getElementById('mirrorUrl').value = url;
        highlightMirrorPreset(url);
    }
});

function getCsrfToken() {
    var meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : '';
}

function saveProxySetting() {
    var url = document.getElementById('proxyUrl').value.trim();
    var type = document.querySelector('input[name="proxyType"]:checked').value;
    var btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 保存中...';

    fetch('api/github_auth.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'type=save_settings&csrf_token=' + getCsrfToken() + '&proxy_url=' + encodeURIComponent(url) + '&proxy_type=' + encodeURIComponent(type)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var msg = document.getElementById('proxySaveMsg');
        if (data.code === 200) {
            msg.innerHTML = '<span style="color: var(--success);"><i class="fas fa-check-circle"></i> 代理设置已保存</span>';
        } else {
            msg.innerHTML = '<span style="color: var(--danger);"><i class="fas fa-times-circle"></i> ' + (data.msg || '保存失败') + '</span>';
        }
        setTimeout(function() { msg.innerHTML = ''; }, 3000);
    })
    .catch(function() {
        document.getElementById('proxySaveMsg').innerHTML = '<span style="color: var(--danger);"><i class="fas fa-times-circle"></i> 保存失败</span>';
    })
    .finally(function() {
        btn.disabled = false;
        btn.innerHTML = '保存代理设置';
    });
}

function testProxy() {
    var url = document.getElementById('proxyUrl').value.trim();
    if (!url) { alert('请先输入代理地址'); return; }
    var btn = document.getElementById('testProxyBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 测试中...';
    document.getElementById('proxySaveMsg').innerHTML = '';

    fetch('api/github_auth.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'type=test_proxy&csrf_token=' + getCsrfToken() + '&proxy_url=' + encodeURIComponent(url)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var msg = document.getElementById('proxySaveMsg');
        if (data.success) {
            msg.innerHTML = '<span style="color: var(--success);"><i class="fas fa-check-circle"></i> 代理连通！延迟: ' + data.ms + 'ms</span>';
        } else {
            msg.innerHTML = '<span style="color: var(--danger);"><i class="fas fa-times-circle"></i> ' + (data.error || '连接失败') + '</span>';
        }
    })
    .catch(function() {
        document.getElementById('proxySaveMsg').innerHTML = '<span style="color: var(--danger);"><i class="fas fa-times-circle"></i> 测试请求失败</span>';
    })
    .finally(function() {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-plug"></i> 测试连接';
    });
}

loadGithubStatus();
loadSettings();
</script>

<?php require_once('footer.php'); ?>
