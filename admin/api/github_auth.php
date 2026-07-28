<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ERROR);
ini_set('display_errors', 0);

require_once dirname(__DIR__, 2) . '/function.php';
require_once dirname(__DIR__, 2) . '/auth.php';

Auth::init();
if (!Auth::check()) {
    echo json_encode(["code" => 401, "msg" => "未登录"], JSON_UNESCAPED_UNICODE);
    exit;
}

$type = $_REQUEST["type"] ?? "";

ensureGithubTables();

switch ($type) {
    case "login_url":
        handleLoginUrl();
        break;
    case "pat_login":
        handlePatLogin();
        break;
    case "callback":
        handleCallback();
        break;
    case "status":
        handleStatus();
        break;
    case "logout":
        handleLogout();
        break;
    case "save_settings":
        handleSaveSettings();
        break;
    case "get_settings":
        handleGetSettings();
        break;
    case "my_prs":
        handleMyPrs();
        break;
    case "my_forks":
        handleMyForks();
        break;
    case "repo_info":
        handleRepoInfo();
        break;
    case "sync_from_github":
        handleSyncFromGithub();
        break;
    case "create_oauth_manifest":
        handleCreateOAuthManifest();
        break;
    case "manifest_callback":
        handleManifestCallback();
        break;
    default:
        echo json_encode(["code" => 400, "msg" => "无效操作"], JSON_UNESCAPED_UNICODE);
}

function ensureGithubTables() {
    try {
        db()->execute("CREATE TABLE IF NOT EXISTS github_accounts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            github_id INTEGER UNIQUE,
            login TEXT NOT NULL,
            avatar_url TEXT,
            name TEXT,
            email TEXT,
            access_token TEXT,
            token_type TEXT DEFAULT 'token',
            scope TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        db()->execute("CREATE TABLE IF NOT EXISTS plugin_market_settings (
            key TEXT PRIMARY KEY,
            value TEXT
        )");
    } catch (Exception $e) {
        wlog("创建GitHub表失败: " . $e->getMessage(), 'system');
    }
}

function getSetting($key, $default = '') {
    try {
        $row = db()->fetch("SELECT value FROM plugin_market_settings WHERE key = ?", [$key]);
        return $row ? $row['value'] : $default;
    } catch (Exception $e) {
        return $default;
    }
}

function setSetting($key, $value) {
    try {
        db()->execute("INSERT OR REPLACE INTO plugin_market_settings (key, value) VALUES (?, ?)", [$key, $value]);
    } catch (Exception $e) {}
}

function getMirrorUrl($originalUrl) {
    $mirror = getSetting('mirror_url', 'https://github.com');
    if ($mirror !== 'https://github.com') {
        $originalUrl = str_replace('https://github.com', $mirror, $originalUrl);
    }
    return $originalUrl;
}

function handleLoginUrl() {
    $clientId = getSetting('github_client_id');
    
    // 如果没有配置 OAuth App，但有 admin token，直接用 PAT 登录
    if (empty($clientId)) {
        $adminToken = getSetting('github_admin_token', '');
        if (!empty($adminToken)) {
            echo json_encode(["code" => 200, "pat_mode" => true, "msg" => "使用 Token 登录"], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(["code" => 400, "msg" => "未配置GitHub登录，请在系统设置中配置 OAuth 或 Token"], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    $redirectUri = getSetting('github_redirect_uri', '');
    if (empty($redirectUri)) {
        $redirectUri = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://')
            . $_SERVER['HTTP_HOST']
            . dirname($_SERVER['SCRIPT_NAME'])
            . '/github_auth.php?type=callback';
    }

    $state = bin2hex(random_bytes(16));
    $_SESSION['github_oauth_state'] = $state;

    $url = "https://github.com/login/oauth/authorize?"
        . "client_id=" . urlencode($clientId)
        . "&redirect_uri=" . urlencode($redirectUri)
        . "&scope=read:user,user:email,public_repo"
        . "&state=" . urlencode($state);

    echo json_encode(["code" => 200, "url" => $url], JSON_UNESCAPED_UNICODE);
}

function handleCallback() {
    $code = $_GET["code"] ?? "";
    $state = $_GET["state"] ?? "";

    $expectedState = $_SESSION['github_oauth_state'] ?? '';
    unset($_SESSION['github_oauth_state']);

    if (empty($state) || !hash_equals($expectedState, $state)) {
        echo json_encode(["code" => 400, "msg" => "State验证失败，请重新授权"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $clientId = getSetting('github_client_id');
    $clientSecret = getSetting('github_client_secret');
    $redirectUri = getSetting('github_redirect_uri');

    if (empty($clientId) || empty($clientSecret)) {
        echo json_encode(["code" => 400, "msg" => "GitHub OAuth未配置"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $tokenUrl = "https://github.com/login/oauth/access_token";
    $postData = json_encode([
        "client_id" => $clientId,
        "client_secret" => $clientSecret,
        "code" => $code,
        "redirect_uri" => $redirectUri
    ]);

    $ctx = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\nAccept: application/json\r\nUser-Agent: Yuexia-PHP-Marketplace/1.0\r\n",
            'content' => $postData,
            'timeout' => 15
        ]
    ]);

    $response = @file_get_contents($tokenUrl, false, $ctx);
    if (!$response) {
        echo json_encode(["code" => 500, "msg" => "获取access_token失败"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $tokenData = json_decode($response, true);
    $accessToken = $tokenData['access_token'] ?? '';

    if (empty($accessToken)) {
        $error = $tokenData['error_description'] ?? ($tokenData['error'] ?? '未知错误');
        echo json_encode(["code" => 500, "msg" => "GitHub授权失败: " . $error], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $userCtx = stream_context_create([
        'http' => [
            'header' => "Authorization: Bearer " . $accessToken . "\r\nUser-Agent: Yuexia-PHP-Marketplace/1.0\r\n",
            'timeout' => 10
        ]
    ]);

    $userResp = @file_get_contents('https://api.github.com/user', false, $userCtx);
    if (!$userResp) {
        echo json_encode(["code" => 500, "msg" => "获取用户信息失败"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $userData = json_decode($userResp, true);

    $emailResp = @file_get_contents('https://api.github.com/user/emails', false, $userCtx);
    $primaryEmail = '';
    if ($emailResp) {
        $emails = json_decode($emailResp, true);
        if (is_array($emails)) {
            foreach ($emails as $e) {
                if ($e['primary'] && $e['verified']) {
                    $primaryEmail = $e['email'];
                    break;
                }
            }
        }
    }

    $githubId = $userData['id'];
    $login = $userData['login'];
    $avatar = $userData['avatar_url'];
    $name = $userData['name'] ?? $login;

    try {
        db()->execute(
            "INSERT OR REPLACE INTO github_accounts (github_id, login, avatar_url, name, email, access_token, updated_at) VALUES (?, ?, ?, ?, ?, ?, datetime('now','localtime'))",
            [$githubId, $login, $avatar, $name, $primaryEmail, $accessToken]
        );
    } catch (Exception $e) {}

    $_SESSION['github_user'] = [
        'id' => $githubId,
        'login' => $login,
        'avatar' => $avatar,
        'name' => $name,
        'email' => $primaryEmail
    ];

    echo json_encode([
        "code" => 200,
        "msg" => "GitHub绑定成功",
        "user" => $_SESSION['github_user']
    ], JSON_UNESCAPED_UNICODE);
}

/**
 * 使用存储的 PAT Token 直接登录（不需要 OAuth App）
 */
function handlePatLogin() {
    $adminToken = getSetting('github_admin_token', '');
    if (empty($adminToken)) {
        echo json_encode(["code" => 400, "msg" => "未配置 GitHub Token，请在系统设置中配置"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $ctx = stream_context_create([
        'http' => [
            'header' => "Authorization: Bearer " . $adminToken . "\r\nUser-Agent: Yuexia-PHP-Marketplace/1.0\r\n",
            'timeout' => 10
        ]
    ]);

    // 获取用户信息
    $userResp = @file_get_contents('https://api.github.com/user', false, $ctx);
    if (!$userResp) {
        echo json_encode(["code" => 500, "msg" => "GitHub API 请求失败，Token 可能已失效"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $userData = json_decode($userResp, true);
    if (!$userData || isset($userData['message'])) {
        $errMsg = $userData['message'] ?? '未知错误';
        echo json_encode(["code" => 500, "msg" => "GitHub 认证失败: " . $errMsg], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 获取邮箱
    $emailResp = @file_get_contents('https://api.github.com/user/emails', false, $ctx);
    $primaryEmail = '';
    if ($emailResp) {
        $emails = json_decode($emailResp, true);
        if (is_array($emails)) {
            foreach ($emails as $e) {
                if ($e['primary'] && $e['verified']) {
                    $primaryEmail = $e['email'];
                    break;
                }
            }
        }
    }

    $githubId = $userData['id'];
    $login = $userData['login'];
    $avatar = $userData['avatar_url'];
    $name = $userData['name'] ?? $login;

    // 存数据库
    try {
        db()->execute(
            "INSERT OR REPLACE INTO github_accounts (github_id, login, avatar_url, name, email, access_token, updated_at) VALUES (?, ?, ?, ?, ?, ?, datetime('now','localtime'))",
            [$githubId, $login, $avatar, $name, $primaryEmail, $adminToken]
        );
    } catch (Exception $e) {}

    // 写 session
    $_SESSION['github_user'] = [
        'id' => $githubId,
        'login' => $login,
        'avatar' => $avatar,
        'name' => $name,
        'email' => $primaryEmail
    ];

    echo json_encode([
        "code" => 200,
        "msg" => "GitHub 登录成功",
        "user" => $_SESSION['github_user']
    ], JSON_UNESCAPED_UNICODE);
}

function handleStatus() {
    $user = $_SESSION['github_user'] ?? null;
    if ($user) {
        echo json_encode(["code" => 200, "logged_in" => true, "user" => $user], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(["code" => 200, "logged_in" => false], JSON_UNESCAPED_UNICODE);
    }
}

function handleLogout() {
    unset($_SESSION['github_user']);
    unset($_SESSION['github_oauth_state']);
    echo json_encode(["code" => 200, "msg" => "已断开GitHub账号"], JSON_UNESCAPED_UNICODE);
}

function handleSaveSettings() {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!Auth::verifyCsrfToken($csrfToken)) {
        echo json_encode(["code" => 403, "msg" => "CSRF token验证失败"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $fields = ['github_client_id', 'github_client_secret', 'github_redirect_uri', 'mirror_url', 'official_repo'];
    foreach ($fields as $field) {
        $val = $_POST[$field] ?? '';
        setSetting($field, $val);
    }

    echo json_encode(["code" => 200, "msg" => "设置保存成功"], JSON_UNESCAPED_UNICODE);
}

function handleGetSettings() {
    $settings = [
        'github_client_id' => getSetting('github_client_id'),
        'github_client_secret' => getSetting('github_client_secret'),
        'github_redirect_uri' => getSetting('github_redirect_uri'),
        'mirror_url' => getSetting('mirror_url', 'https://github.com'),
        'official_repo' => getSetting('official_repo', 'yuexia-php/plugins'),
    ];

    if (!empty($settings['github_client_secret']) && strlen($settings['github_client_secret']) > 8) {
        $secret = $settings['github_client_secret'];
        $settings['github_client_secret_masked'] = substr($secret, 0, 4) . '****' . substr($secret, -4);
    }

    echo json_encode(["code" => 200, "settings" => $settings], JSON_UNESCAPED_UNICODE);
}

/**
 * 获取用户 access_token
 */
function getUserAccessToken() {
    $user = $_SESSION['github_user'] ?? null;
    if (!$user) return '';
    try {
        $row = db()->fetch("SELECT access_token FROM github_accounts WHERE github_id = ?", [$user['id']]);
        return $row ? $row['access_token'] : '';
    } catch (Exception $e) {
        return '';
    }
}

/**
 * 获取官方插件仓库信息
 */
function getOfficialRepo() {
    $repo = getSetting('official_repo', 'yuexia-php/plugins');
    $parts = explode('/', $repo);
    if (count($parts) !== 2) {
        return ['owner' => 'yuexia-php', 'repo' => 'plugins'];
    }
    return ['owner' => $parts[0], 'repo' => $parts[1]];
}

/**
 * 获取仓库信息
 */
function handleRepoInfo() {
    $info = getOfficialRepo();
    $mirrorUrl = getMirrorUrl('https://github.com');
    echo json_encode([
        "code" => 200,
        "owner" => $info['owner'],
        "repo" => $info['repo'],
        "html_url" => $mirrorUrl . '/' . $info['owner'] . '/' . $info['repo'],
        "fork_url" => $mirrorUrl . '/' . $info['owner'] . '/' . $info['repo'] . '/fork',
        "pulls_url" => $mirrorUrl . '/' . $info['owner'] . '/' . $info['repo'] . '/pulls',
        "raw_marketplace_url" => 'https://raw.githubusercontent.com/' . $info['owner'] . '/' . $info['repo'] . '/main/marketplace.json'
    ], JSON_UNESCAPED_UNICODE);
}

/**
 * 获取我的PR列表
 */
function handleMyPrs() {
    $accessToken = getUserAccessToken();
    if (empty($accessToken)) {
        echo json_encode(["code" => 200, "prs" => [], "msg" => "未登录GitHub"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $info = getOfficialRepo();
    $owner = $info['owner'];
    $repo = $info['repo'];
    $user = $_SESSION['github_user'];
    $login = $user['login'];

    $ctx = stream_context_create([
        'http' => [
            'header' => "Authorization: Bearer " . $accessToken . "\r\nUser-Agent: Yuexia-PHP-Marketplace/1.0\r\n",
            'timeout' => 15
        ]
    ]);

    // 获取用户的所有PR（open + closed）
    $prs = [];
    
    // 先查open状态的PR
    $openUrl = "https://api.github.com/repos/{$owner}/{$repo}/pulls?state=open&head={$login}:";
    $openResp = @file_get_contents($openUrl, false, $ctx);
    if ($openResp) {
        $openPrs = json_decode($openResp, true);
        if (is_array($openPrs)) {
            foreach ($openPrs as $pr) {
                $prs[] = formatPrData($pr);
            }
        }
    }

    // 再查用户的所有PR（包括closed/merged）
    $allUrl = "https://api.github.com/search/issues?q=repo:{$owner}/{$repo}+author:{$login}+type:pr&sort=updated&order=desc&per_page=20";
    $allResp = @file_get_contents($allUrl, false, $ctx);
    if ($allResp) {
        $allData = json_decode($allResp, true);
        $items = $allData['items'] ?? [];
        $existingIds = [];
        foreach ($prs as $p) {
            $existingIds[] = $p['id'];
        }
        foreach ($items as $item) {
            if (!in_array($item['number'], $existingIds)) {
                $prs[] = [
                    'id' => $item['number'],
                    'title' => $item['title'],
                    'state' => $item['state'] === 'closed' && $item['pull_request']['merged_at'] ? 'merged' : $item['state'],
                    'html_url' => $item['html_url'],
                    'created_at' => $item['created_at'],
                    'updated_at' => $item['updated_at'],
                    'body' => mb_substr($item['body'] ?? '', 0, 200),
                    'user' => [
                        'login' => $item['user']['login'],
                        'avatar' => $item['user']['avatar_url']
                    ]
                ];
            }
        }
    }

    // 按更新时间排序
    usort($prs, function($a, $b) {
        return strcmp($b['updated_at'], $a['updated_at']);
    });

    echo json_encode(["code" => 200, "prs" => $prs], JSON_UNESCAPED_UNICODE);
}

function formatPrData($pr) {
    return [
        'id' => $pr['number'],
        'title' => $pr['title'],
        'state' => $pr['state'] === 'closed' && ($pr['merged_at'] ?? null) ? 'merged' : $pr['state'],
        'html_url' => $pr['html_url'],
        'created_at' => $pr['created_at'],
        'updated_at' => $pr['updated_at'],
        'body' => mb_substr($pr['body'] ?? '', 0, 200),
        'head' => [
            'label' => $pr['head']['label'],
            'ref' => $pr['head']['ref'],
            'repo' => $pr['head']['repo']['full_name'] ?? ''
        ],
        'user' => [
            'login' => $pr['user']['login'],
            'avatar' => $pr['user']['avatar_url']
        ]
    ];
}

/**
 * 检查用户是否已fork官方仓库
 */
function handleMyForks() {
    $accessToken = getUserAccessToken();
    if (empty($accessToken)) {
        echo json_encode(["code" => 200, "forked" => false, "msg" => "未登录GitHub"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $info = getOfficialRepo();
    $owner = $info['owner'];
    $repo = $info['repo'];
    $user = $_SESSION['github_user'];
    $login = $user['login'];

    $ctx = stream_context_create([
        'http' => [
            'header' => "Authorization: Bearer " . $accessToken . "\r\nUser-Agent: Yuexia-PHP-Marketplace/1.0\r\n",
            'timeout' => 15
        ]
    ]);

    // 检查用户是否有fork
    $forkUrl = "https://api.github.com/repos/{$login}/{$repo}";
    $forkResp = @file_get_contents($forkUrl, false, $ctx);
    
    if ($forkResp) {
        $forkData = json_decode($forkResp, true);
        if (isset($forkData['fork']) && $forkData['fork']) {
            echo json_encode([
                "code" => 200,
                "forked" => true,
                "fork_url" => $forkData['html_url'],
                "default_branch" => $forkData['default_branch'],
                "updated_at" => $forkData['updated_at']
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    echo json_encode(["code" => 200, "forked" => false], JSON_UNESCAPED_UNICODE);
}

/**
 * 从官方GitHub仓库同步marketplace.json
 */
function handleSyncFromGithub() {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!Auth::verifyCsrfToken($csrfToken)) {
        echo json_encode(["code" => 403, "msg" => "CSRF token验证失败"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $info = getOfficialRepo();
    $owner = $info['owner'];
    $repo = $info['repo'];

    // 先尝试用当前登录用户的token，再尝试存储的管理员token
    $accessToken = getUserAccessToken();
    if (empty($accessToken)) {
        $accessToken = getSetting('github_admin_token', '');
    }
    $headerStr = "User-Agent: Yuexia-PHP-Marketplace/1.0\r\n";
    if (!empty($accessToken)) {
        $headerStr .= "Authorization: Bearer " . $accessToken . "\r\n";
    }

    // 尝试main分支
    $rawUrl = "https://raw.githubusercontent.com/{$owner}/{$repo}/main/marketplace.json";
    $ctx = stream_context_create([
        'http' => [
            'header' => $headerStr,
            'timeout' => 20,
            'ignore_errors' => true
        ]
    ]);

    $content = @file_get_contents($rawUrl, false, $ctx);
    
    // 如果main失败，尝试master
    if ($content === false || strlen($content) < 10) {
        $rawUrl = "https://raw.githubusercontent.com/{$owner}/{$repo}/master/marketplace.json";
        $content = @file_get_contents($rawUrl, false, $ctx);
    }

    if ($content === false || strlen($content) < 10) {
        echo json_encode(["code" => 500, "msg" => "从GitHub同步失败，请检查仓库地址和网络连接"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 验证JSON格式
    $data = json_decode($content, true);
    if ($data === null) {
        echo json_encode(["code" => 500, "msg" => "同步失败：marketplace.json格式无效"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 写入本地文件
    $marketplaceFile = dirname(__DIR__, 2) . '/marketplace.json';
    if (file_put_contents($marketplaceFile, $content)) {
        $pluginCount = count($data['plugins'] ?? []);
        wlog("插件市场已从GitHub同步: {$owner}/{$repo}，共{$pluginCount}个插件", 'system');
        echo json_encode([
            "code" => 200, 
            "msg" => "同步成功，共 {$pluginCount} 个插件",
            "plugins" => $data['plugins'] ?? []
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(["code" => 500, "msg" => "同步失败：无法写入marketplace.json"], JSON_UNESCAPED_UNICODE);
    }
}

/**
 * 生成 GitHub App Manifest URL（一键创建 OAuth App）
 */
function handleCreateOAuthManifest() {
    // 动态检测 callback URL
    $callbackUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://')
        . $_SERVER['HTTP_HOST']
        . dirname($_SERVER['SCRIPT_NAME'])
        . '/github_auth.php?type=manifest_callback';

    $manifest = [
        "name" => "yuexia-marketplace-" . bin2hex(random_bytes(3)),
        "url" => "http://" . $_SERVER['HTTP_HOST'],
        "redirect_url" => $callbackUrl,
        "callback_urls" => [$callbackUrl],
        "public" => true,
        "default_permissions" => [
            "contents" => "read",
            "metadata" => "read",
            "pull_requests" => "read",
            "emails" => "read"
        ],
        "default_events" => ["pull_request"]
    ];

    $manifestJson = json_encode($manifest);
    $manifestB64 = urlencode($manifestJson);
    $githubUrl = "https://github.com/settings/apps/new?manifest=" . $manifestB64;

    echo json_encode([
        "code" => 200,
        "url" => $githubUrl,
        "callback_url" => $callbackUrl
    ], JSON_UNESCAPED_UNICODE);
}

/**
 * 处理 GitHub App Manifest 回调（自动保存 OAuth 凭证）
 */
function handleManifestCallback() {
    $code = $_GET['code'] ?? '';
    if (empty($code)) {
        // 可能是用户取消了，重定向到设置页
        header('Location: ' . dirname($_SERVER['SCRIPT_NAME']) . '/../github_settings.php?msg=setup_cancelled');
        exit;
    }

    // 用 code 换 app 凭证
    $ctx = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\nUser-Agent: Yuexia-PHP-Marketplace/1.0\r\n",
            'content' => '{}',
            'timeout' => 15
        ]
    ]);

    $resp = @file_get_contents("https://api.github.com/app-manifests/{$code}/conversions", false, $ctx);
    if (!$resp) {
        header('Location: ' . dirname($_SERVER['SCRIPT_NAME']) . '/../github_settings.php?msg=setup_failed');
        exit;
    }

    $data = json_decode($resp, true);
    if (!$data || !isset($data['client_id'])) {
        header('Location: ' . dirname($_SERVER['SCRIPT_NAME']) . '/../github_settings.php?msg=setup_failed');
        exit;
    }

    // 保存凭证
    setSetting('github_client_id', $data['client_id']);
    setSetting('github_client_secret', $data['client_secret']);
    if (isset($data['pem'])) {
        setSetting('github_app_pem', $data['pem']);
    }
    if (isset($data['id'])) {
        setSetting('github_app_id', strval($data['id']));
    }

    wlog("GitHub OAuth App 自动配置成功: client_id=" . $data['client_id'], 'system');

    // 重定向回设置页
    header('Location: ' . dirname($_SERVER['SCRIPT_NAME']) . '/../github_settings.php?msg=setup_ok');
}


注意：severity 字段: safe=安全, warning=需关注, danger=高风险。
score 字段: 0-100 分，越高越安全。
如果没有发现问题，issues 数组可以为空。";

    $maxCodeLen = 80000;
    if (strlen($code) > $maxCodeLen) {
        $code = substr($code, 0, $maxCodeLen) . "\n\n// ... (代码过长，已截断)";
    }

    $postData = json_encode([
        "model" => $model,
        "messages" => [
            ["role" => "system", "content" => $systemPrompt],
            ["role" => "user", "content" => "请审核以下 PHP 代码：\n\n```php\n" . $code . "\n```"]
        ],
        "temperature" => 0.1,
        "max_tokens" => 4000
    ]);

    $apiUrl = $baseUrl . '/chat/completions';
    $ctx = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\nAuthorization: Bearer " . $apiKey . "\r\nUser-Agent: Yuexia-PHP-Marketplace/1.0\r\n",
            'content' => $postData,
            'timeout' => 60
        ]
    ]);

    $response = @file_get_contents($apiUrl, false, $ctx);
    if (!$response) {
        echo json_encode(["code" => 500, "msg" => "AI API 请求失败，请检查 API Key 和网络连接"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $result = json_decode($response, true);
    if (!$result || !isset($result['choices'][0]['message']['content'])) {
        $errMsg = $result['error']['message'] ?? 'AI 响应格式异常';
        echo json_encode(["code" => 500, "msg" => "AI 审核失败: " . $errMsg], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $aiContent = $result['choices'][0]['message']['content'];

    $report = json_decode($aiContent, true);
    if ($report && isset($report['severity'])) {
        $severity = $report['severity'];
        $score = $report['score'] ?? 0;
        $summary = $report['summary'] ?? '';
        $issues = $report['issues'] ?? [];
        $overall = $report['overall'] ?? '';

        $formattedReport = "## 审核结果\n\n";
        $formattedReport .= "**评分**: {$score}/100\n\n";
        $formattedReport .= "**总结**: {$summary}\n\n";

        if (!empty($issues)) {
            $formattedReport .= "### 发现的问题\n\n";
            foreach ($issues as $i => $issue) {
                $line = $issue['line'] !== 'general' ? ' (第 ' . $issue['line'] . ' 行)' : '';
                $formattedReport .= "**{$issue['title']}**{$line}\n";
                $formattedReport .= "   - 类型: {$issue['type']} | 严重度: {$issue['level']}\n";
                $formattedReport .= "   - {$issue['description']}\n";
                if (!empty($issue['suggestion'])) {
                    $formattedReport .= "   - 建议: {$issue['suggestion']}\n";
                }
                $formattedReport .= "\n";
            }
        } else {
            $formattedReport .= "未发现明显问题，代码质量良好。\n\n";
        }

        if ($overall) {
            $formattedReport .= "### 总体评价\n\n{$overall}";
        }

        echo json_encode([
            "code" => 200,
            "report" => $formattedReport,
            "severity" => $severity,
            "score" => $score,
            "summary" => $summary,
            "issues_count" => count($issues)
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            "code" => 200,
            "report" => $aiContent,
            "severity" => "warning"
        ], JSON_UNESCAPED_UNICODE);
    }
}

