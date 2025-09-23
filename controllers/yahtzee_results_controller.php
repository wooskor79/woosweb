<?php
/**
 * Admin: Yahtzee 결과 조회 컨트롤러
 */
require_once __DIR__.'/../init.php';

if (!is_admin()) {
    die('관리자만 접근할 수 있습니다.');
}

$all_users = db_query_all("SELECT id, username FROM users ORDER BY username ASC");
$selected_user_id = $_GET['user_id'] ?? 'all';

$sql = "SELECT r.player_score, r.ai_score, r.winner, r.duration_seconds, r.played_at, u.username
        FROM yahtzee_results r
        JOIN users u ON r.user_id = u.id";
$params = [];

if ($selected_user_id !== 'all' && is_numeric($selected_user_id)) {
    $sql .= " WHERE r.user_id = :user_id";
    $params[':user_id'] = $selected_user_id;
}

$sql .= " ORDER BY r.played_at DESC";

$results = db_query_all($sql, $params);

// ✅ 2. 승/패/무승부 전적 계산 로직 추가
$wins = 0;
$losses = 0;
$draws = 0;
if (!empty($results)) {
    foreach ($results as $row) {
        if ($row['winner'] === 'Player') {
            $wins++;
        } elseif ($row['winner'] === 'AI') {
            $losses++;
        } else {
            $draws++;
        }
    }
}