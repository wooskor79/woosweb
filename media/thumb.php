<?php
// /web/media/thumb.php
require_once __DIR__ . '/../init.php';

// --- 설정 ---
// ✅ 썸네일을 추출할 시간 (HH:MM:SS 형식). '00:00:10'은 10초 지점을 의미합니다.
// 이 시간이 지나도 검은 화면이 계속되면 '00:00:30' 등으로 늘려보세요.
$thumbnail_timestamp = '00:00:20';
// ------------

// === 파라미터 수신 및 기본값 설정 ===
$relative_path = $_GET['p'] ?? '';
$width = (int)($_GET['w'] ?? 320);
$root_type = $_GET['root'] ?? 'video';

// === 보안 검사: 경로 조작 방지 ===
if (strpos($relative_path, '..') !== false || substr($relative_path, 0, 1) === '/') {
    header('HTTP/1.1 400 Bad Request');
    exit('Invalid path');
}

// === 루트 경로 및 캐시 경로 설정 ===
$source_root = '';
$cache_root = '';

if ($root_type === 'video' && defined('VIDEO_ROOT') && defined('THUMB_CACHE_DIR_VIDEO')) {
    $source_root = VIDEO_ROOT;
    $cache_root = THUMB_CACHE_DIR_VIDEO;
} elseif ($root_type === 'photo' && defined('PHOTO_ROOT') && defined('THUMB_CACHE_DIR_PHOTO')) {
    $source_root = PHOTO_ROOT;
    $cache_root = THUMB_CACHE_DIR_PHOTO;
} else {
    header('HTTP/1.1 500 Internal Server Error');
    exit('Root constants are not defined.');
}

$source_file = realpath($source_root . DIRECTORY_SEPARATOR . $relative_path);

if (!$source_file || strpos($source_file, realpath($source_root)) !== 0) {
    header('HTTP/1.1 404 Not Found');
    exit('File not found or access denied.');
}

// === 캐시 파일 경로 생성 ===
$cache_hash = md5($relative_path);
$cache_filename = $cache_hash . '_w' . $width . '.jpg';
$cache_file = rtrim($cache_root, '/') . DIRECTORY_SEPARATOR . $cache_filename;

// === 캐시 확인 및 제공 ===
if (file_exists($cache_file) && filemtime($cache_file) > filemtime($source_file)) {
    header('Content-Type: image/jpeg');
    readfile($cache_file);
    exit;
}

if (!is_dir($cache_root)) {
    mkdir($cache_root, 0777, true);
}

// === 썸네일 생성 로직 ===
if ($root_type === 'video') {
    if (!defined('FFMPEG_PATH') || !is_executable(FFMPEG_PATH)) {
        header('HTTP/1.1 500 Internal Server Error');
        exit('FFmpeg not configured or not executable.');
    }
    
    // ✅ 설정된 시간($thumbnail_timestamp)을 사용하도록 FFmpeg 명령어 수정
    $ffmpeg_cmd = FFMPEG_PATH . ' -i ' . escapeshellarg($source_file) .
                  ' -ss ' . $thumbnail_timestamp . ' -vframes 1 -vf "scale=' . $width . ':-1" -q:v 2 ' .
                  escapeshellarg($cache_file) . ' -y 2>&1';
    
    shell_exec($ffmpeg_cmd);

} else {
    // (사진 썸네일 로직은 기존과 동일)
    $source_info = getimagesize($source_file);
    if (!$source_info) exit('Invalid image');

    $mime = $source_info['mime'];
    $src_w = $source_info[0];
    $src_h = $source_info[1];
    $ratio = $src_w / $src_h;
    $height = (int)($width / $ratio);

    $thumb = imagecreatetruecolor($width, $height);
    
    $source_image = null;
    if ($mime == 'image/jpeg') $source_image = imagecreatefromjpeg($source_file);
    elseif ($mime == 'image/png') $source_image = imagecreatefrompng($source_file);
    elseif ($mime == 'image/gif') $source_image = imagecreatefromgif($source_file);
    elseif ($mime == 'image/webp') $source_image = imagecreatefromwebp($source_file);

    if ($source_image) {
        imagecopyresampled($thumb, $source_image, 0, 0, 0, 0, $width, $height, $src_w, $src_h);
        imagejpeg($thumb, $cache_file, 90);
        imagedestroy($thumb);
        imagedestroy($source_image);
    }
}

// === 생성된 썸네일 제공 ===
if (file_exists($cache_file)) {
    header('Content-Type: image/jpeg');
    readfile($cache_file);
    exit;
} else {
    header('HTTP/1.1 500 Internal Server Error');
    exit('Thumbnail generation failed.');
}