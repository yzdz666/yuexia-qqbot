<?php
/**
 * 代理辅助函数 - 为 stream_context_create 添加代理支持
 * 
 * 用法: $ctx = proxyContext(['http' => [...]]);
 * 返回: 已包含代理配置的 stream_context
 */

function proxyContext($opts = []) {
    static $proxyUrl = null;
    static $proxyType = 'http';
    
    if ($proxyUrl === null) {
        try {
            $row = db()->fetch("SELECT value FROM plugin_market_settings WHERE key = 'proxy_url'");
            $proxyUrl = $row ? $row['value'] : '';
            $row2 = db()->fetch("SELECT value FROM plugin_market_settings WHERE key = 'proxy_type'");
            $proxyType = $row2 ? $row2['value'] : 'http';
        } catch (Exception $e) {
            $proxyUrl = '';
        }
    }
    
    if (!empty($proxyUrl)) {
        $scheme = parse_url($proxyUrl, PHP_URL_SCHEME) ?: 'http';
        if (!isset($opts[$scheme])) $opts[$scheme] = [];
        $opts[$scheme]['proxy'] = $proxyUrl;
        $opts[$scheme]['request_fulluri'] = true;
    }
    
    return stream_context_create($opts);
}

/**
 * 带代理的 file_get_contents 替代
 */
function proxyGet($url, $timeout = 15, $headers = '') {
    $opts = [];
    if ($headers) $opts['http']['header'] = $headers;
    $opts['http']['timeout'] = $timeout;
    $opts['http']['method'] = 'GET';
    return @file_get_contents($url, false, proxyContext($opts));
}

/**
 * 带代理的 POST 请求
 */
function proxyPost($url, $data, $timeout = 30, $headers = '') {
    $opts = [
        'http' => [
            'method' => 'POST',
            'content' => is_string($data) ? $data : json_encode($data),
            'timeout' => $timeout,
        ]
    ];
    if ($headers) $opts['http']['header'] = $headers;
    if (is_array($data)) $opts['http']['header'] = ($headers ? $headers . "\r\n" : '') . "Content-Type: application/json";
    return @file_get_contents($url, false, proxyContext($opts));
}
