<?php
/**
 * lib/csrf.php
 * CSRF(Cross-Site Request Forgery) 공격 방지를 위한 토큰을 관리합니다.
 */

/**
 * 현재 요청에 대한 CSRF 토큰을 가져오거나 생성합니다.
 * 세션에 토큰이 없으면 새로 만들고, 있으면 기존 토큰을 반환합니다.
 * @return string
 */
function csrf_token(): string {
    if (isset($_SESSION['csrf_token'])) {
        return $_SESSION['csrf_token'];
    }
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;
    return $token;
}

/**
 * CSRF 토큰을 포함한 hidden input HTML을 생성합니다.
 * @return string
 */
function csrf_input(): string {
    $token = csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * 제출된 토큰이 세션의 토큰과 일치하는지 확인합니다.
 * @param string $token - 사용자가 제출한 토큰
 * @return bool
 */
function csrf_check(string $token): bool {
    if (empty($token) || !isset($_SESSION['csrf_token'])) {
        return false;
    }
    
    $result = hash_equals($_SESSION['csrf_token'], $token);
    
    // 한번 사용된 토큰은 세션에서 제거하여 재사용을 방지합니다.
    unset($_SESSION['csrf_token']);
    
    return $result;
}