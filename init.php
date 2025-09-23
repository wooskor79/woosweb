<?php
/**
 * my_app init.php
 * - 세션/DB/유틸/인증/CSRF 초기화 (기존)
 * - 스트리밍 관련 상수 및 캐시 디렉터리(사진/비디오) 분리 설정 추가
 */

require_once __DIR__.'/config.php';
session_name(SESSION_NAME);
session_start();

require_once __DIR__.'/lib/db.php';
require_once __DIR__.'/lib/utils.php';
require_once __DIR__.'/lib/auth.php';
require_once __DIR__.'/lib/csrf.php';

// 로그인 상태에서 세션 고정 방지
if (!isset($_SESSION['regen'])) {
    session_regenerate_id(true);
    $_SESSION['regen'] = 1;
}

/* ============================================
 * ✅ 앱 공통/스트리밍/썸네일 관련 추가 설정
 * ============================================ */

/** 1) 실제 동영상 폴더(공유 폴더) 경로 */
if (!defined('VIDEO_ROOT')) {
    // Synology 공유 폴더의 실제 경로로 유지
    define('VIDEO_ROOT', '/volume1/ShareFolder');
}

/** 2) 퍼블릭 베이스 경로 (가상호스트 문서 루트가 /volume1/web/my_app → URL 기준 '/') */
if (!defined('APP_BASE')) {
    define('APP_BASE', ''); // 필요 시 '/'로 바꿔도 동일 동작
}

/** 3) (선택) 사진 루트 — 사용 시 주석 해제하고 실제 경로로 맞추세요 */
// if (!defined('PHOTO_ROOT')) {
//     define('PHOTO_ROOT', '/volume1/ShareFolder/Photos'); // 예시 경로
// }

/** 4) 썸네일 캐시 경로 (사진/비디오 분리 저장) */
if (!defined('THUMB_CACHE_DIR')) {
    // 웹 루트 내부. 보안상 웹 루트 밖(예: /volume1/ShareFolder/.thumb_cache)도 가능
    define('THUMB_CACHE_DIR', __DIR__ . '/cache/thumbs');
}
if (!defined('THUMB_CACHE_DIR_VIDEO')) {
    define('THUMB_CACHE_DIR_VIDEO', THUMB_CACHE_DIR . '/video');
}
if (!defined('THUMB_CACHE_DIR_PHOTO')) {
    define('THUMB_CACHE_DIR_PHOTO', THUMB_CACHE_DIR . '/photo');
}

/** 5) 허용 확장자(문자열/목록) */
if (!defined('ALLOWED_VIDEO_EXTS')) {
    define('ALLOWED_VIDEO_EXTS', 'mp4,mkv,avi,mov,wmv,mpg,mpeg,flv,m3u8,ts,webm');
}
if (!defined('ALLOWED_PHOTO_EXTS')) {
    define('ALLOWED_PHOTO_EXTS', 'jpg,jpeg,png,gif,webp');
}

/** 6) 캐시 디렉터리 미리 생성(권한 문제 완화) */
@mkdir(THUMB_CACHE_DIR,        0775, true);
@mkdir(THUMB_CACHE_DIR_VIDEO,  0775, true);
@mkdir(THUMB_CACHE_DIR_PHOTO,  0775, true);

// ✅ [추가] FFMPEG 실행 파일 경로 (동영상 썸네일 생성에 필수)
// 서버에 설치된 ffmpeg의 전체 경로를 입력하세요.
// 터미널에서 'which ffmpeg' 명령으로 확인할 수 있습니다. (예: /usr/bin/ffmpeg)
if (!defined('FFMPEG_PATH')) {
    // ✅ [확인] 이 경로는 이전 대화에서 확인한 최종 경로입니다.
    define('FFMPEG_PATH', '/volume1/@appstore/ffmpeg7/bin/ffmpeg');
}


/** 7) (선택) 런타임 환경 점검 로그 — 운영에서는 주석 처리 권장 */
// $ob = (string)ini_get('open_basedir');
// if ($ob) {
//     if (strpos($ob, '/volume1/ShareFolder') === false) {
//         error_log('[init.php] WARNING: open_basedir does not include /volume1/ShareFolder: ' . $ob);
//     }
//     if (defined('PHOTO_ROOT')) {
//         $pr = realpath(PHOTO_ROOT);
//         if ($pr && strpos($ob, $pr) === false) {
//             error_log('[init.php] WARNING: open_basedir does not include PHOTO_ROOT: ' . $pr);
//         }
//     }
//     $tc = realpath(THUMB_CACHE_DIR);
//     if ($tc && strpos($ob, $tc) === false) {
//         error_log('[init.php] WARNING: open_basedir does not include THUMB_CACHE_DIR: ' . $tc);
//     }
// }

/** 8) (선택) 내부 인코딩 — 프로젝트에서 멀티바이트 처리 필요 시 */
// if (function_exists('mb_internal_encoding')) {
//     @mb_internal_encoding('UTF-8');
// }