<?php
// /controllers/build_comics_cache.php

require_once __DIR__ . '/../init.php';

// 작업 스케줄러(cron)는 로그인 세션이나 CSRF 토큰이 없으므로 관련 로직 제거
// require_login(); 
// if (!verify_csrf_token(...)) { ... }

// 만화책 루트 폴더 설정
$comics_root_path = '/volume1/ShareFolder/Comics';
$real_root_path = realpath($comics_root_path);

if ($real_root_path === false) {
    // cron 실행 시 오류를 파일 로그로 남기도록 처리
    error_log('[build_comics_cache] Comics root directory not found: ' . $comics_root_path);
    http_response_code(500);
    exit('Comics root directory not found.');
}

$db = db();
$folder_count = 0;

try {
    // 1. 기존 캐시 삭제
    $db->beginTransaction();

    // ✅ [수정] TRUNCATE는 암시적 커밋을 유발하므로, 트랜잭션에 안전한 DELETE로 변경
    $db->exec("DELETE FROM comic_folder_cache");

    // 2. 폴더 스캔 및 DB에 추가
    $stmt = $db->prepare("INSERT INTO comic_folder_cache (relative_path) VALUES (?)");

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($real_root_path, FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        if (!$item->isDir() || in_array($item->getBasename(), ['@eaDir', '#recycle'])) {
            continue;
        }
        
        $relative_path = ltrim(str_replace($real_root_path, '', $item->getPathname()), '/');
        if ($relative_path) {
            $stmt->execute([$relative_path]);
            $folder_count++;
        }
    }

    // 3. 트랜잭션 완료
    $db->commit();

    // cron 실행 시 성공 로그를 남기도록 처리
    $success_message = "[build_comics_cache] Cache rebuild success! Total {$folder_count} folders.";
    error_log($success_message);
    echo $success_message;

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    // cron 실행 시 오류를 파일 로그로 남기도록 처리
    $error_message = '[build_comics_cache] Cache rebuild failed: ' . $e->getMessage();
    error_log($error_message);
    http_response_code(500);
    exit($error_message);
}