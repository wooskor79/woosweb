<?php
// /volume1/web/my_app/media/stream.php
require_once __DIR__ . '/../init.php';

// (필요 시) 로그인 요구
// if (!is_logged_in()) { http_response_code(401); exit('Unauthorized'); }

$root = realpath(VIDEO_ROOT);
if ($root === false) {
    http_response_code(500);
    exit('Config error: VIDEO_ROOT not found.');
}

$p = $_GET['p'] ?? '';
$mode = $_GET['mode'] ?? 'inline';

// 상대경로 정규화
$rel = ltrim(str_replace('\\', '/', $p), '/');
$path = realpath($root . '/' . $rel);

// ===== 디버그 모드 =====
if (isset($_GET['debug']) && $_GET['debug'] == '1') {
    header('Content-Type: text/plain; charset=utf-8');
    echo "DEBUG MODE\n";
    echo "VIDEO_ROOT(realpath): $root\n";
    echo "GET p (raw): " . (string)$p . "\n";
    echo "rel (normalized): $rel\n";
    echo "Joined: " . $root . '/' . $rel . "\n";
    echo "realpath(path): " . var_export($path, true) . "\n";
    if ($path) {
        echo "is_file: " . (is_file($path) ? 'true' : 'false') . "\n";
        echo "is_readable: " . (is_readable($path) ? 'true' : 'false') . "\n";
        echo "filesize: " . @filesize($path) . "\n";
    }
    // open_basedir 확인
    echo "open_basedir: " . ini_get('open_basedir') . "\n";
    exit;
}

if (!$path || strpos($path, $root) !== 0 || !is_file($path)) {
    http_response_code(404);
    exit('File not found or access denied.');
}

// MIME
$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$mimeMap = [
    'mp4'=>'video/mp4','m4v'=>'video/mp4','mkv'=>'video/x-matroska','webm'=>'video/webm',
    'mov'=>'video/quicktime','avi'=>'video/x-msvideo','wmv'=>'video/x-ms-wmv',
    'mpg'=>'video/mpeg','mpeg'=>'video/mpeg','flv'=>'video/x-flv',
    'm3u8'=>'application/vnd.apple.mpegurl','ts'=>'video/MP2T','srt'=>'application/x-subrip',
];
$mime = $mimeMap[$ext] ?? 'application/octet-stream';

$filesize = filesize($path);
$fp = fopen($path, 'rb');
if ($fp === false) {
    http_response_code(500);
    exit('Cannot open file.');
}

if (function_exists('session_write_close')) @session_write_close();
@set_time_limit(0);
@ini_set('output_buffering', 'off');
@ini_set('zlib.output_compression', '0');
if (ob_get_level()) @ob_end_clean();

$start = 0;
$end = $filesize > 0 ? $filesize - 1 : 0;
$length = $filesize;

$rangeHeader = $_SERVER['HTTP_RANGE'] ?? null;
if ($rangeHeader && preg_match('/bytes=(\d*)-(\d*)/i', $rangeHeader, $m)) {
    $r0 = $m[1] === '' ? null : (int)$m[1];
    $r1 = $m[2] === '' ? null : (int)$m[2];

    if ($r0 !== null && $r1 !== null) {
        $start = $r0; $end = min($r1, $filesize - 1);
    } elseif ($r0 !== null) {
        $start = $r0; $end = $filesize - 1;
    } elseif ($r1 !== null) {
        $start = max(0, $filesize - $r1); $end = $filesize - 1;
    }

    if ($start > $end || $start >= $filesize) {
        header('HTTP/1.1 416 Range Not Satisfiable');
        header("Content-Range: bytes */$filesize");
        fclose($fp);
        exit;
    }

    $length = $end - $start + 1;
    header('HTTP/1.1 206 Partial Content');
    header("Content-Range: bytes $start-$end/$filesize");
} else {
    header('HTTP/1.1 200 OK');
}

header('Content-Type: ' . $mime);
header('Accept-Ranges: bytes');
header('Content-Length: ' . $length);

$disposition = ($mode === 'download') ? 'attachment' : 'inline';
$filename = basename($path);
header('Content-Disposition: ' . $disposition . '; filename*=UTF-8\'\'' . rawurlencode($filename));

header('Cache-Control: private, max-age=3600');
header('Pragma: public');

fseek($fp, $start);
$buf = 1024 * 64;
$left = $length;

while ($left > 0 && !feof($fp)) {
    $read = ($left > $buf) ? $buf : $left;
    $chunk = fread($fp, $read);
    if ($chunk === false) break;
    echo $chunk;
    $left -= strlen($chunk);
    if (connection_aborted()) break;
    flush();
}

fclose($fp);
exit;
