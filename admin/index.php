<?php
/**
 * 管理后台 - 仪表盘
 */
$pageTitle = '仪表盘';
require_once('header.php');

// ==================== 统计数据 ====================
try {
    $totalBots = (int) db()->fetchColumn("SELECT COUNT(*) FROM bots");
} catch (Exception $e) { $totalBots = 0; }

try {
    $totalMessages = (int) db()->fetchColumn("SELECT COUNT(*) FROM messages");
} catch (Exception $e) { $totalMessages = 0; }

try {
    $totalUsers = (int) db()->fetchColumn("SELECT COUNT(*) FROM users");
} catch (Exception $e) { $totalUsers = 0; }

try {
    $totalGroups = (int) db()->fetchColumn("SELECT COUNT(*) FROM groups");
} catch (Exception $e) { $totalGroups = 0; }

try {
    $todayMessages = (int) db()->fetchColumn("SELECT COUNT(*) FROM messages WHERE created_at >= datetime('now','localtime','start of day')");
} catch (Exception $e) { $todayMessages = 0; }

try {
    $receiveCount = (int) db()->fetchColumn("SELECT COUNT(*) FROM messages WHERE direction = '接收'");
} catch (Exception $e) { $receiveCount = 0; }

try {
    $sendCount = (int) db()->fetchColumn("SELECT COUNT(*) FROM messages WHERE direction = '发送'");
} catch (Exception $e) { $sendCount = 0; }

// ==================== 最近7天消息趋势 ====================
$trendData = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $trendData[$date] = 0;
}
try {
    $trendRows = db()->fetchAll(
        "SELECT date(created_at) AS msg_date, COUNT(*) AS cnt
         FROM messages
         WHERE created_at >= datetime('now','localtime','-6 days','start of day')
         GROUP BY date(created_at)
         ORDER BY msg_date"
    );
    foreach ($trendRows as $row) {
        if (isset($trendData[$row['msg_date']])) {
            $trendData[$row['msg_date']] = (int) $row['cnt'];
        }
    }
} catch (Exception $e) {}

$trendMax = max($trendData);
$hasTrendData = $trendMax > 0;
$trendMax = $trendMax > 0 ? $trendMax : 1;

// ==================== 最近消息（10条） ====================
try {
    $recentMessages = db()->fetchAll("SELECT * FROM messages ORDER BY id DESC LIMIT 10");
} catch (Exception $e) { $recentMessages = []; }

// ==================== 系统信息 ====================
$sysInfo = getSystemInfo();
// 使用 function.php 中已经处理过的磁盘原始值
$diskFreeRaw  = $sysInfo['disk_free_raw'] ?? @disk_free_space('/');
$diskTotalRaw = $sysInfo['disk_total_raw'] ?? @disk_total_space('/');
$diskUsedPercent = ($diskTotalRaw && $diskTotalRaw > 0 && $diskFreeRaw !== false)
    ? round((1 - $diskFreeRaw / $diskTotalRaw) * 100, 1)
    : 0;
$loadAvg = $sysInfo['load_avg'] ?? null;
$loadStr = '不可用';
if (is_array($loadAvg) && count($loadAvg) >= 1) {
    $parts = [];
    foreach ($loadAvg as $v) {
        $parts[] = number_format((float) $v, 2);
    }
    $loadStr = implode(' / ', $parts);
}
?>
<style>
/* 仪表盘图表与布局（纯CSS，无JS） */
.chart-bars { display:flex; align-items:flex-end; gap:12px; height:200px; padding:10px 0; }
.chart-col { flex:1; display:flex; flex-direction:column; align-items:center; justify-content:flex-end; height:100%; }
.chart-bar { width:100%; max-width:42px; background:var(--primary); border-radius:4px 4px 0 0; min-height:2px; transition:background 0.2s ease; }
.chart-bar:hover { background:var(--primary-hover); }
.chart-value { font-size:11px; color:var(--text-secondary); margin-bottom:4px; }
.chart-label { font-size:11px; color:var(--text-muted); margin-top:6px; white-space:nowrap; }
.dashboard-row { display:grid; grid-template-columns:1.6fr 1fr; gap:16px; margin-bottom:16px; }
.info-table td:first-child { width:38%; color:var(--text-muted); white-space:nowrap; }
.progress { height:6px; background:var(--border); border-radius:3px; overflow:hidden; margin-top:5px; }
.progress-bar { height:100%; background:var(--primary); border-radius:3px; }
@media (max-width:900px) { .dashboard-row { grid-template-columns:1fr; } }
</style>

<div class="page-header">
  <h2>仪表盘</h2>
</div>

<!-- ==================== 统计卡片 ==================== -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-label">机器人总数</div>
    <div class="stat-value"><?= $totalBots ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label">消息总数</div>
    <div class="stat-value"><?= $totalMessages ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label">用户总数</div>
    <div class="stat-value"><?= $totalUsers ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label">群组总数</div>
    <div class="stat-value"><?= $totalGroups ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label">今日消息</div>
    <div class="stat-value"><?= $todayMessages ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label">接收消息</div>
    <div class="stat-value"><?= $receiveCount ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label">发送消息</div>
    <div class="stat-value"><?= $sendCount ?></div>
  </div>
</div>

<!-- ==================== 最近7天消息趋势 ==================== -->
<div class="card mb-3">
  <div class="card-header">
    <h3>最近7天消息趋势</h3>
    <span class="text-muted" style="font-size:12px;">单位：条</span>
  </div>
  <div class="card-body">
    <div class="chart-bars">
      <?php foreach ($trendData as $date => $count):
        $barHeight = $hasTrendData ? round(($count / $trendMax) * 170) : 0;
        if ($count > 0 && $barHeight < 6) $barHeight = 6;
        $barHeightPx = $count > 0 ? $barHeight : 2;
      ?>
      <div class="chart-col">
        <span class="chart-value"><?= $count ?></span>
        <div class="chart-bar" style="height:<?= $barHeightPx ?>px;"></div>
        <span class="chart-label"><?= date('m-d', strtotime($date)) ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- ==================== 最近消息 + 系统信息 ==================== -->
<div class="dashboard-row">
  <!-- 最近消息 -->
  <div class="card">
    <div class="card-header">
      <h3>最近消息</h3>
      <a href="messages.php" class="btn btn-outline btn-sm">查看全部</a>
    </div>
    <div class="card-body no-padding">
      <?php if (empty($recentMessages)): ?>
        <div class="empty-state">
          <div class="empty-icon">--</div>
          <p>暂无消息记录</p>
        </div>
      <?php else: ?>
        <?php foreach ($recentMessages as $msg):
          $isRecv = ($msg['direction'] === '接收');
          $content = trim($msg['content'] ?? '');
          if ($content === '') $content = '(无内容)';
          if (mb_strlen($content, 'UTF-8') > 80) {
              $content = mb_substr($content, 0, 80, 'UTF-8') . '...';
          }
          $sourceType = $msg['source_type'] ?? '';
          if ($sourceType === '') $sourceType = '未知';
        ?>
        <div class="message-item">
          <div class="msg-direction <?= $isRecv ? 'recv' : 'send' ?>">
            <?= $isRecv ? '&#8595;' : '&#8593;' ?>
          </div>
          <div class="msg-content">
            <div class="msg-meta" style="display:flex; justify-content:space-between; gap:8px;">
              <span>
                <span class="badge <?= $isRecv ? 'badge-info' : 'badge-success' ?>"><?= htmlspecialchars($msg['direction']) ?></span>
                <?= htmlspecialchars($sourceType) ?>
              </span>
              <span style="white-space:nowrap;"><?= htmlspecialchars($msg['created_at']) ?></span>
            </div>
            <div class="msg-text"><?= htmlspecialchars($content) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- 系统信息 -->
  <div class="card">
    <div class="card-header">
      <h3>系统信息</h3>
    </div>
    <div class="card-body no-padding">
      <table class="table info-table">
        <tr>
          <td>PHP 版本</td>
          <td><?= htmlspecialchars($sysInfo['php_version']) ?></td>
        </tr>
        <tr>
          <td>操作系统</td>
          <td><?= htmlspecialchars($sysInfo['os']) ?></td>
        </tr>
        <tr>
          <td>主机名</td>
          <td><?= htmlspecialchars($sysInfo['hostname']) ?></td>
        </tr>
        <tr>
          <td>内存使用</td>
          <td><?= htmlspecialchars($sysInfo['memory_usage']) ?> / <?= htmlspecialchars($sysInfo['memory_limit']) ?></td>
        </tr>
        <tr>
          <td>磁盘空间</td>
          <td><?= htmlspecialchars($sysInfo['disk_free']) ?> 可用 / <?= htmlspecialchars($sysInfo['disk_total']) ?> 总计</td>
        </tr>
        <tr>
          <td>磁盘使用率</td>
          <td><?= $diskUsedPercent ?>%
            <div class="progress"><div class="progress-bar" style="width:<?= min($diskUsedPercent, 100) ?>%;"></div></div>
          </td>
        </tr>
        <tr>
          <td>系统负载</td>
          <td><?= htmlspecialchars($loadStr) ?> <span class="text-muted" style="font-size:12px;">(1分/5分/15分)</span></td>
        </tr>
        <tr>
          <td>时区</td>
          <td><?= htmlspecialchars($sysInfo['timezone']) ?></td>
        </tr>
      </table>
    </div>
  </div>
</div>

<!-- ==================== 版本更新 ==================== -->
<div class="card" style="margin-top:16px;">
  <div class="card-header">
    <h3>框架版本</h3>
  </div>
  <div class="card-body" style="display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap;">
    <div>
      <span style="font-size:13px; color:var(--text-secondary);">当前版本：</span>
      <span style="font-weight:600;">
        <?php
        $vFile = __DIR__ . '/../version.json';
        $vData = @json_decode(file_get_contents($vFile), true);
        if ($vData) echo 'v' . htmlspecialchars($vData['version']) . ' (' . htmlspecialchars($vData['build']) . ')';
        else echo '未知';
        ?>
      </span>
    </div>
    <div>
      <span id="remoteVersion" style="font-size:13px; color:var(--text-secondary);"></span>
      <button class="btn btn-outline btn-sm" onclick="checkUpdate()" id="checkUpdateBtn"><i class="fas fa-refresh"></i> 检查更新</button>
      <button class="btn btn-primary btn-sm" onclick="doUpdate()" id="updateBtn" style="display:none;"><i class="fas fa-download"></i> 更新</button>
    </div>
  </div>
</div>

<script>
function checkUpdate() {
  var btn = document.getElementById('checkUpdateBtn');
  btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 检查中...';
  
  // 通过服务器代理检查更新（解决浏览器无法直连 GitHub 的问题）
  fetch('api/github_auth.php?type=check_framework_version')
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.code === 200) {
        document.getElementById('remoteVersion').innerHTML = '最新版本：v' + data.remote_version + ' (' + data.remote_build + ')';
        if (data.has_update) {
          document.getElementById('updateBtn').style.display = 'inline-flex';
          document.getElementById('remoteVersion').innerHTML += ' <span style="color:var(--success);font-weight:600;">可更新</span>';
        } else {
          document.getElementById('remoteVersion').innerHTML += ' <span style="color:var(--text-muted);">已是最新</span>';
        }
      } else {
        document.getElementById('remoteVersion').innerHTML = '<span style="color:var(--text-muted);">' + (data.msg || '检查失败') + '</span>';
      }
      btn.disabled = false; btn.innerHTML = '<i class="fas fa-refresh"></i> 检查更新';
    })
    .catch(function() {
      document.getElementById('remoteVersion').innerHTML = '<span style="color:var(--text-muted);">网络请求失败</span>';
      btn.disabled = false; btn.innerHTML = '<i class="fas fa-refresh"></i> 检查更新';
    });
}

function doUpdate() {
  if (!confirm('确定要更新框架吗？更新过程中可能短暂影响服务。')) return;
  var btn = document.getElementById('updateBtn');
  btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 更新中...';
  
  fetch('api.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'action=framework_update&csrf_token=' + encodeURIComponent(document.querySelector('meta[name="csrf-token"]').content)
  })
  .then(function(r) { return r.json(); })
  .then(function(d) {
    if (d.success) {
      alert('更新成功！' + (d.message ? '\\n' + d.message : ''));
      document.getElementById('updateBtn').style.display = 'none';
    } else {
      alert('更新失败：' + (d.message || '未知错误'));
    }
    btn.disabled = false; btn.innerHTML = '<i class="fas fa-download"></i> 更新';
  })
  .catch(function() {
    alert('更新请求失败');
    btn.disabled = false; btn.innerHTML = '<i class="fas fa-download"></i> 更新';
  });
}

checkUpdate();
</script>

<?php require_once('footer.php'); ?>
