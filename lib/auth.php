<?php
/**
 * lib/auth.php
 * 로그인, 로그아웃, 사용자 정보 등 인증 관련 함수를 정의합니다.
 */

function login(string $username, string $password): bool {
    $user = db_query_one('SELECT * FROM users WHERE username = ?', [$username]);
    if (!$user || !password_verify($password, $user['password_hash'])) {
        return false;
    }
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    return true;
}

function logout(): void {
    session_destroy();
}

function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

function current_user(): ?array {
    // ✅ [수정] static $user 변수를 사용한 캐시 로직을 제거합니다.
    // 이렇게 하면 함수가 호출될 때마다 항상 DB에서 최신 사용자 정보를 가져옵니다.
    if (!is_logged_in()) {
        return null;
    }
    $user = db_query_one('SELECT * FROM users WHERE id = ?', [$_SESSION['user_id']]);
    return $user ?: null;
}

function is_admin(): bool {
    $user = current_user();
    return $user && $user['is_admin'];
}

function user_group_ids(int $user_id): array {
    if (is_admin()) { // 관리자는 모든 그룹 ID를 반환
        $groups = db_query_all("SELECT id FROM groups");
        return array_column($groups, 'id');
    }
    $rows = db_query_all("SELECT group_id FROM user_groups WHERE user_id = ?", [$user_id]);
    return array_column($rows, 'group_id');
}
