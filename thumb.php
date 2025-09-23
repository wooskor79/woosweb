<?php
require_once __DIR__ . '/init.php';

/**
 * 통합 썸네일 엔드포인트 (사진 + 비디오)
 * 
 * 입력:
 *   - (권장) p=상대경로 + root=photo|video
 *   - (호환) f=base64(절대경로)
 *   - w=가로폭(120~1280, 기본 320), ts=(브라우저 캐시 무효화용)
 * 
 * 캐시:
 *   - 사진:  THUMB_CACHE_DIR_PHOTO/{shard}/{sha1}.jpg
 *   - 비디오: THUMB_CACHE_DIR_VIDEO/{shard}/{sha1}.jpg
 */

function http_bad($code = 400, $msg = 'Bad Request') {
    http_response_code($code);
    header('Content-Type: text/plain; charset=utf-8');
    echo $msg;
    exit;
}

function out_jpeg_file($path) {
    header('Content-Type: image/jpeg');
    header('Cache-Control: public, max-age=86400, immutable');
    readfile($path);
    exit;
}

function out_orig($file) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file) ?: 'application/octet-stream';
    finfo_close($finfo);
    header('Content-Type: '.$mime);
    header('Cache-Control: public, max-age=86400');
    readfile($file);
    exit;
}

function find_ffmpeg() {
    $candidates = [
        '/usr/bin/ffmpeg',
        '/bin/ffmpeg',
        '/usr/local/bin/ffmpeg',
        '/var/packages/CodecPack/target/bin/ffmpeg', // Synology CodecPack
        '/usr/syno/bin/ffmpeg',
    ];
    foreach ($candidates as $bin) {
        if (is_file($bin) && is_executable($bin)) return $bin;
    }
    return null;
}

// ===== 입력 파라미터 =====
$w = isset($_GET['w']) ? (int)$_GET['w'] : 320;
if ($w < 120) $w = 120;
if ($w > 1280) $w = 1280;

$rootHint = isset($_GET['root']) ? strtolower(trim($_GET['root'])) : ''; // 'photo' | 'video' | ''
$p = isset($_GET['p']) ? ltrim(str_replace('\\','/', $_GET['p']), '/') : '';
$f = $_GET['f'] ?? '';

$abs = null;
$originGroup = null; // 'photo' | 'video'

// 1) p + root 방식 우선
if ($p !== '' && $rootHint !== '') {
    if ($rootHint === 'photo') {
        if (!defined('PHOTO_ROOT')) http_bad(500, 'PHOTO_ROOT not defined.');
        $root = realpath(PHOTO_ROOT);
        if (!$root) http_bad(500, 'PHOTO_ROOT not found.');
        $cand = realpath(rtrim($root,'/').'/'.$p);
        if ($cand && strpos($cand, $root) === 0 && is_file($cand)) {
            $abs = $cand; $originGroup = 'photo';
        }
    } elseif ($rootHint === 'video') {
        $root = realpath(VIDEO_ROOT);
        if (!$root) http_bad(500, 'VIDEO_ROOT not found.');
        $cand = realpath(rtrim($root,'/').'/'.$p);
        if ($cand && strpos($cand, $root) === 0 && is_file($cand)) {
            $abs = $cand; $originGroup = 'video';
        }
    } else {
        http_bad(400, 'Invalid root (use photo|video).');
    }
}

// 2) f(base64 절대경로) 호환
if (!$abs && $f !== '') {
    $decoded = base64_decode($f, true);
    if ($decoded !== false) {
        $cand = realpath($decoded);
        if ($cand && is_file($cand)) {
            // 어느 ROOT에 속하는지 확인
            if (defined('PHOTO_ROOT')) {
                $pr = realpath(PHOTO_ROOT);
                if ($pr && strpos($cand, $pr) === 0) { $abs = $cand; $originGroup = 'photo'; }
            }
            if (!$abs) {
                $vr = realpath(VIDEO_ROOT);
                if ($vr && strpos($cand, $vr) === 0) { $abs = $cand; $originGroup = 'video'; }
            }
        }
    }
}

// 3) p만 있고 root 미지정이면 양쪽 검사
if (!$abs && $p !== '' && $rootHint === '') {
    if (defined('PHOTO_ROOT')) {
        $pr = realpath(PHOTO_ROOT);
        if ($pr) {
            $cand = realpath(rtrim($pr,'/').'/'.$p);
            if ($cand && strpos($cand, $pr) === 0 && is_file($cand)) { $abs = $cand; $originGroup = 'photo'; }
        }
    }
    if (!$abs) {
        $vr = realpath(VIDEO_ROOT);
        if ($vr) {
            $cand = realpath(rtrim($vr,'/').'/'.$p);
            if ($cand && strpos($cand, $vr) === 0 && is_file($cand)) { $abs = $cand; $originGroup = 'video'; }
        }
    }
}

if (!$abs || !$originGroup) {
    http_bad(404, 'File not found or access denied.');
}

// 확장자로 타입 추정 (비디오/사진)
$ext = strtolower(pathinfo($abs, PATHINFO_EXTENSION));
$videoExts = ['mp4','mkv','mov','avi','wmv','mpg','mpeg','flv','webm','ts'];
$photoExts = ['jpg','jpeg','png','gif','webp'];
$isVideo = in_array($ext, $videoExts, true) || ($originGroup === 'video');
$isPhoto = in_array($ext, $photoExts, true) || ($originGroup === 'photo');

// ===== 캐시 경로 =====
$cacheRoot = $isVideo ? THUMB_CACHE_DIR_VIDEO : THUMB_CACHE_DIR_PHOTO;
$cacheKey  = sha1($abs.'|'.$w);
$shard     = substr($cacheKey, 0, 2);
$cachePath = rtrim($cacheRoot, '/').'/'.$shard.'/'.$cacheKey.'.jpg';
@mkdir(dirname($cachePath), 0775, true);

// 원본 갱신 체크
$srcMTime = @filemtime($abs) ?: time();
if (is_file($cachePath) && @filemtime($cachePath) >= $srcMTime) {
    out_jpeg_file($cachePath);
}

// ===== 분기: 비디오 vs 사진 =====
if ($isVideo) {
    // --- 비디오: FFmpeg 필요 ---
    $ffmpeg = find_ffmpeg();
    $disabled = array_map('trim', explode(',', (string)ini_get('disable_functions')));
    if (!$ffmpeg || in_array('exec', $disabled, true)) {
        http_bad(503, 'Thumbnail service unavailable: ffmpeg not found or exec() disabled.');
    }

    $tmp = $cachePath.'.part';
    @unlink($tmp);

    // 3초 지점 → 실패 시 10초 지점
    $vf = "scale={$w}:-1:flags=bicubic";
    $cmd = sprintf('%s -hide_banner -loglevel error -nostdin -ss 3 -i %s -frames:v 1 -vf %s -q:v 3 -y %s 2>&1',
        escapeshellarg($ffmpeg), escapeshellarg($abs), escapeshellarg($vf), escapeshellarg($tmp)
    );
    exec($cmd, $out1, $ret1);
    if ($ret1 !== 0 || !is_file($tmp)) {
        $cmd = sprintf('%s -hide_banner -loglevel error -nostdin -ss 10 -i %s -frames:v 1 -vf %s -q:v 3 -y %s 2>&1',
            escapeshellarg($ffmpeg), escapeshellarg($abs), escapeshellarg($vf), escapeshellarg($tmp)
        );
        exec($cmd, $out2, $ret2);
        if ($ret2 !== 0 || !is_file($tmp)) {
            http_bad(500, 'Failed to generate video thumbnail.');
        }
    }

    @rename($tmp, $cachePath);
    out_jpeg_file($cachePath);

} elseif ($isPhoto) {
    // --- 사진: GD + EXIF 보정 ---
    if (!extension_loaded('gd')) {
        // GD가 없으면 원본 출력
        out_orig($abs);
    }

    // 로더
    $src = false;
    if (in_array($ext, ['jpg','jpeg'], true) && function_exists('imagecreatefromjpeg')) {
        $src = @imagecreatefromjpeg($abs);
    } elseif ($ext === 'png' && function_exists('imagecreatefrompng')) {
        $src = @imagecreatefrompng($abs);
    } elseif ($ext === 'gif' && function_exists('imagecreatefromgif')) {
        $src = @imagecreatefromgif($abs);
    } elseif ($ext === 'webp' && function_exists('imagecreatefromwebp')) {
        $src = @imagecreatefromwebp($abs);
    }
    if (!$src) {
        // 로드 실패 시 원본
        out_orig($abs);
    }

    // EXIF 회전 (JPEG)
    if (in_array($ext, ['jpg','jpeg'], true) && function_exists('exif_read_data')) {
        $exif = @exif_read_data($abs);
        if (!empty($exif['Orientation'])) {
            $o = (int)$exif['Orientation'];
            if ($o === 3)      $src = imagerotate($src, 180, 0);
            elseif ($o === 6)  $src = imagerotate($src, -90, 0);
            elseif ($o === 8)  $src = imagerotate($src, 90, 0);
        }
    }

    $sw = imagesx($src); $sh = imagesy($src);
    if ($sw <= 0 || $sh <= 0) { imagedestroy($src); out_orig($abs); }

    $scale = min(1.0, $w / $sw);
    $tw = max(1, (int)round($sw * $scale));
    $th = max(1, (int)round($sh * $scale));

    $dst = imagecreatetruecolor($tw, $th);
    $white = imagecolorallocate($dst, 255, 255, 255);
    imagefill($dst, 0, 0, $white);

    imagecopyresampled($dst, $src, 0,0, 0,0, $tw,$th, $sw,$sh);

    $tmp = $cachePath.'.part';
    @unlink($tmp);
    $ok = @imagejpeg($dst, $tmp, 85);
    imagedestroy($dst);
    imagedestroy($src);

    if (!$ok || !is_file($tmp)) {
        // 저장 실패: 권한 문제 등 → 직접 출력
        header('Content-Type: image/jpeg');
        @imagejpeg($dst, null, 85);
        exit;
    }

    @rename($tmp, $cachePath);
    out_jpeg_file($cachePath);

} else {
    // 알 수 없는 형식 → 원본
    out_orig($abs);
}
