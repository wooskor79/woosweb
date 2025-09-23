<?php
require_once __DIR__.'/../init.php';

// 로그인한 사용자만 접근 가능
if (!is_logged_in()) {
    redirect(base_url('index.php'));
    exit;
}

$user = current_user();
$uid = $user['id'];
$action = $_POST['action'] ?? '';

// CSRF 토큰 확인
if (!csrf_check($_POST['csrf_token'] ?? '')) {
    die('잘못된 접근입니다.');
}

switch ($action) {
    case 'create':
        $title = trim($_POST['title'] ?? '');
        $due_date = $_POST['due_date'] ?? date('Y-m-d');
        if ($title) {
            $sql = "INSERT INTO tasks (user_id, title, due_date) VALUES (?, ?, ?)";
            db_execute($sql, [$uid, $title, $due_date]);
        }
        break;

    case 'toggle':
        $task_id = (int)($_POST['task_id'] ?? 0);
        if ($task_id > 0) {
            // 현재 상태를 가져와서 반대로 업데이트 (보안을 위해 user_id 조건 추가)
            $sql = "UPDATE tasks 
                    SET completed = NOT completed, completed_at = IF(completed, NULL, NOW()) 
                    WHERE id = ? AND user_id = ?";
            db_execute($sql, [$task_id, $uid]);
        }
        break;

    case 'delete':
        $task_id = (int)($_POST['task_id'] ?? 0);
        if ($task_id > 0) {
            // 보안을 위해 user_id 조건 추가
            $sql = "DELETE FROM tasks WHERE id = ? AND user_id = ?";
            db_execute($sql, [$task_id, $uid]);
        }
        break;
}

// 모든 작업 후 할일 페이지로 리다이렉트
redirect(base_url('index.php?page=tasks'));