<?php
/**
 * Yahtzee 게임 결과 저장 및 조회 API (최종본)
 */
require_once __DIR__.'/../init.php';

function write_yahtzee_log($message) {
    $log_file = __DIR__ . '/../cache/yahtzee_debug.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] " . $message . "\n", FILE_APPEND);
}

// 로그인한 사용자만 접근 가능
if (!is_logged_in()) {
    json_response(['ok' => false, 'reason' => '로그인이 필요합니다.'], 401);
    exit;
}
$user = current_user();

// 요청 방식에 따라 분기
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // === 결과 저장 ===
    $json_input = file_get_contents('php://input');
    $data = json_decode($json_input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        write_yahtzee_log("Invalid JSON received: " . $json_input);
        json_response(['ok' => false, 'reason' => '잘못된 데이터 형식입니다.'], 400);
        exit;
    }
    
    $player_score = (int)($data['player_score'] ?? 0);
    $ai_score = (int)($data['ai_score'] ?? 0);
    $winner = $data['winner'] ?? 'Draw';
    $duration = (int)($data['duration_seconds'] ?? 0);

    $sql = "INSERT INTO yahtzee_results (user_id, player_score, ai_score, winner, duration_seconds)
            VALUES (:user_id, :player_score, :ai_score, :winner, :duration_seconds)";

    $params = [
        ':user_id' => $user['id'],
        ':player_score' => $player_score,
        ':ai_score' => $ai_score,
        ':winner' => $winner,
        ':duration_seconds' => $duration
    ];

    try {
        db_execute($sql, $params);
        json_response(['ok' => true]);
    } catch (PDOException $e) {
        write_yahtzee_log("DB Insert Failed. UserID: {$user['id']}, Error: {$e->getMessage()}, Params: " . json_encode($params));
        json_response(['ok' => false, 'reason' => 'DB 저장에 실패했습니다.'], 500);
    }

} else {
    // === 결과 조회 ===
    try {
        $sql = "SELECT r.player_score, r.ai_score, r.winner, r.duration_seconds, r.played_at AS ts, u.username
                FROM yahtzee_results r
                JOIN users u ON r.user_id = u.id
                ORDER BY r.played_at DESC
                LIMIT 30";
        
        $results = db_query_all($sql);

        foreach ($results as &$row) {
            if ($row['winner'] === 'Player') {
                $row['winner'] = h($row['username']);
            }
        }
        unset($row);

        json_response($results);

    } catch (PDOException $e) {
        write_yahtzee_log("DB Select Failed. UserID: {$user['id']}, Error: {$e->getMessage()}");
        json_response([]);
    }
}