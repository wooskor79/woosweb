<?php
require_once __DIR__.'/init.php';

$f = $_GET['f'] ?? '';
$path = base64_decode($f, true);
if (!$path) { http_response_code(400); exit; }

// 루트 경로 이탈 방지
$real = realpath($path);
$root = realpath(PHOTO_ROOT);
if (!$real || strpos($real, $root) !== 0) { http_response_code(403); exit; }

if (!is_readable($real)) { http_response_code(404); exit; }

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $real) ?: 'application/octet-stream';
finfo_close($finfo);

header('Content-Type: '.$mime);
header('Content-Disposition: inline; filename="'.basename($real).'"');
header('Cache-Control: max-age=86400, public');
readfile($real);
