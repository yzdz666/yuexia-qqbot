<?php
/**
 * 框架自动更新脚本 (cron)
 * 
 * 部署后设置定时任务：
 *   crontab -e
 *   添加: 0 */6 * * * php7.4 /path/to/auto_update.php >/dev/null 2>&1
 * 
 * 更新来源: version.json 中 repo 字段指定的 GitHub 仓库
 * 跳过: data/ 目录 (保留配置和数据库)
 */
if (php_sapi_name() !== 'cli') die("Only CLI mode\n");

$rootDir = __DIR__;
$verFile = $rootDir . '/version.json';

$local = @json_decode(file_get_contents($verFile), true);
if (!$local || empty($local['repo'])) {
    echo "[" . date('Y-m-d H:i:s') . "] Error: version.json not found or invalid\n";
    exit(1);
}

$repo = $local['repo'];
$localBuild = intval($local['build'] ?? 0);

echo "[" . date('Y-m-d H:i:s') . "] Checking {$repo} ...\n";

// 获取远程版本 (公开仓库无需认证)
$opts = ['http' => ['header' => "User-Agent: Yuexia-AutoUpdater/1.0\r\n", 'timeout' => 15]];
$remoteRaw = @file_get_contents(
    "https://api.github.com/repos/{$repo}/contents/version.json",
    false,
    stream_context_create($opts)
);

if (!$remoteRaw) {
    echo "[" . date('Y-m-d H:i:s') . "] Failed to fetch remote version (network/rate limit)\n";
    exit(1);
}

$remoteData = json_decode($remoteRaw, true);
if (!$remoteData || !isset($remoteData['content'])) {
    echo "[" . date('Y-m-d H:i:s') . "] Invalid remote version response\n";
    exit(1);
}

$remote = json_decode(base64_decode($remoteData['content']), true);
if (!$remote || !isset($remote['build'])) {
    echo "[" . date('Y-m-d H:i:s') . "] Remote version format invalid\n";
    exit(1);
}

$remoteBuild = intval($remote['build']);
echo "[" . date('Y-m-d H:i:s') . "] Local: v{$local['version']} ({$localBuild}) Remote: v{$remote['version']} ({$remoteBuild})\n";

if ($remoteBuild <= $localBuild) {
    echo "[" . date('Y-m-d H:i:s') . "] Already up to date\n";
    exit(0);
}

echo "[" . date('Y-m-d H:i:s') . "] New version available, updating...\n";

// 下载源码
$zipUrl = "https://api.github.com/repos/{$repo}/zipball/main";
$zip = @file_get_contents($zipUrl, false, stream_context_create(
    ['http' => ['header' => "User-Agent: Yuexia-AutoUpdater/1.0\r\n", 'timeout' => 120]]
));

if (!$zip || strlen($zip) < 100) {
    echo "[" . date('Y-m-d H:i:s') . "] Download failed\n";
    exit(1);
}

$tmpDir = sys_get_temp_dir() . '/yuexia_up_' . time();
@mkdir($tmpDir, 0755, true);
file_put_contents($tmpDir . '/src.zip', $zip);

$zipArc = new ZipArchive();
if ($zipArc->open($tmpDir . '/src.zip') !== true) {
    removeDir($tmpDir);
    echo "[" . date('Y-m-d H:i:s') . "] Unzip failed\n";
    exit(1);
}

$rootEntry = $zipArc->getNameIndex(0);
$zipArc->extractTo($tmpDir . '/ext');
$zipArc->close();

$sourceDir = $tmpDir . '/ext/' . $rootEntry;
if (!is_dir($sourceDir)) {
    removeDir($tmpDir);
    echo "[" . date('Y-m-d H:i:s') . "] Invalid archive structure\n";
    exit(1);
}

// 备份 data 目录
$dataDir = $rootDir . '/data';
$backupDir = $tmpDir . '/backup';
if (is_dir($dataDir)) {
    @mkdir($backupDir, 0755, true);
    xcopy($dataDir, $backupDir);
}

// 替换文件
$count = 0;
$iter = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS)
);
foreach ($iter as $file) {
    $rel = substr($file->getPathname(), strlen($sourceDir) + 1);
    if (strpos($rel, 'data/') === 0 || strpos($rel, '.git') === 0) continue;
    $dest = $rootDir . '/' . $rel;
    if (!is_dir(dirname($dest))) @mkdir(dirname($dest), 0755, true);
    if (copy($file, $dest)) { @chmod($dest, 0644); $count++; }
}

// 恢复 data
if (is_dir($backupDir)) xcopy($backupDir, $dataDir);

removeDir($tmpDir);

echo "[" . date('Y-m-d H:i:s') . "] Update complete! {$count} files updated\n";
echo "[" . date('Y-m-d H:i:s') . "] v{$local['version']} -> v{$remote['version']}\n";

// 写入日志
$logFile = $rootDir . '/data/update.log';
@file_put_contents($logFile,
    "[" . date('Y-m-d H:i:s') . "] Updated from v{$local['version']} to v{$remote['version']} ({$count} files)\n",
    FILE_APPEND
);

function removeDir($d) {
    if (!is_dir($d)) return;
    foreach (array_diff(scandir($d), ['.', '..']) as $i) {
        $p = $d . '/' . $i;
        is_dir($p) ? removeDir($p) : @unlink($p);
    }
    @rmdir($d);
}

function xcopy($s, $d) {
    @mkdir($d, 0755, true);
    foreach (array_diff(scandir($s), ['.', '..']) as $i) {
        $sp = $s . '/' . $i;
        $dp = $d . '/' . $i;
        if (is_dir($sp)) xcopy($sp, $dp);
        else { copy($sp, $dp); @chmod($dp, 0644); }
    }
}
