<?php
require_once __DIR__.'/../init.php';
if (!is_logged_in()) { http_response_code(401); exit; }
// 목록 조회는 뷰에서 직접 쿼리해도 되지만, 이 파일은 확장 여지를 위해 빈 파일로 남겨둠.
