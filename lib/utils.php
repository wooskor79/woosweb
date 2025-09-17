<?php
/**
 * lib/utils.php
 * 자주 사용되는 헬퍼(보조) 함수들을 정의합니다.
 */

/**
 * HTML 특수 문자를 이스케이프하여 XSS 공격을 방지합니다.
 * @param string|null $s
 * @return string
 */
function h(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * 지정된 URL로 리다이렉트하고 스크립트를 종료합니다.
 * @param string $url
 */
function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

/**
 * 프로젝트의 기본 URL 경로를 포함한 전체 URL을 생성합니다.
 * @param string $path - 추가할 경로
 * @return string
 */
function base_url(string $path = ''): string {
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

/**
 * 배열을 JSON 형식으로 변환하여 출력하고 스크립트를 종료합니다.
 * @param array $data - 출력할 데이터
 * @param int $status_code - HTTP 상태 코드
 */
function json_response(array $data, int $status_code = 200): void {
    http_response_code($status_code);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($data);
    exit;
}

/**
 * 전화번호 형식에서 숫자만 추출합니다.
 * @param string $phone
 * @return string
 */
function normalize_phone(string $phone): string {
    return preg_replace('/\D/', '', $phone);
}