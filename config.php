<?php
// 기본 환경
define('APP_NAME', '우성이꺼');
date_default_timezone_set('Asia/Seoul');

// 배포 환경에 맞게 수정
// define('BASE_URL', '/my_app'); // 가상호스트 루트라면 '/' 또는 빈 문자열
define('BASE_URL', '/'); // 루트 도메인에서 바로 열 경우
define('DB_HOST', 'localhost');
define('DB_NAME', 'my_app');
define('DB_USER', 'root'); // 실제 사용자 입력
define('DB_PASS', 'dldntjd@D79'); // 실제 비번 입력
define('DB_CHARSET', 'utf8mb4');

// ----- 경로 설정 -----
// 사진 원본 루트
define('PHOTO_ROOT', '/volume1/photo/MobileBackup/newkids79/WooS Galaxy Z Fold7/DCIM/묭');
// [추가] 동영상 원본 루트
define('VIDEO_ROOT', '/volume1/ShareFolder');


// ----- 보안 설정 -----
define('SESSION_NAME', 'my_app_sid');
// [수정 권장] CSRF 키를 아무도 예측할 수 없는 긴 문자열로 변경해주세요.
// 예: define('CSRF_KEY', 'a9z8e7y6b5c4x3w2v1u0t9s8r7q6p5o4');
define('CSRF_KEY', 'put-a-strong-random-secret-here'); // <-- 이 부분을 직접 만드신 랜덤한 값으로 변경하세요.

// ✅ [수정] 파일 끝에 있던 불필요한 '}'를 삭제했습니다.