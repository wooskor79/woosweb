<?php
require_once __DIR__.'/../init.php';
if (!is_admin()) { http_response_code(403); exit; }

$action   = $_POST['action'] ?? $_GET['action'] ?? '';
$redirect = $_POST['redirect'] ?? $_GET['redirect'] ?? base_url('index.php?page=admin');

if ($action === 'create_group') {
    if (!csrf_check($_POST['csrf_token'] ?? '')) die('CSRF');
    $name = trim($_POST['name'] ?? '');
    if ($name !== '') {
        $stmt = db()->prepare("INSERT INTO groups(name) VALUES(?)");
        try { $stmt->execute([$name]); } catch (PDOException $e) { /* duplicate ignore */ }
    }
    header('Location: '.$redirect);
    exit;
}

if ($action === 'set_user_groups') {
    if (!csrf_check($_POST['csrf_token'] ?? '')) die('CSRF');
    $user_id   = (int)($_POST['user_id'] ?? 0);
    $group_ids = array_map('intval', $_POST['group_ids'] ?? []);
    if ($user_id > 0) {
        $pdo = db();
        $pdo->beginTransaction();
        // 기존 소속 모두 제거 후
        $pdo->prepare("DELETE FROM user_groups WHERE user_id=?")->execute([$user_id]);
        // 새 소속 배정
        if (!empty($group_ids)) {
            $ins = $pdo->prepare("INSERT INTO user_groups(user_id,group_id) VALUES(?,?)");
            foreach ($group_ids as $gid) { $ins->execute([$user_id, $gid]); }
        }
        $pdo->commit();
    }
    header('Location: '.$redirect);
    exit;
}

if ($action === 'delete_user') {
    if (!csrf_check($_POST['csrf_token'] ?? '')) die('CSRF');
    $target_id = (int)($_POST['user_id'] ?? 0);
    $admin_id  = current_user()['id'];
    if ($target_id && $target_id !== $admin_id) {
        $pdo = db();
        $pdo->beginTransaction();
        // 그 사용자가 만든 약속은 현 관리자에게 위임
        $pdo->prepare("UPDATE appointments SET created_by=? WHERE created_by=?")->execute([$admin_id, $target_id]);
        // 사용자 삭제 (user_groups, tasks, appointment_confirmations는 FK CASCADE)
        $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$target_id]);
        $pdo->commit();
    }
    header('Location: '.$redirect);
    exit;
}

/**
 * ✅ 그룹 삭제
 * - user_groups에서 해당 그룹 소속을 전부 제거
 * - appointments가 해당 그룹을 참조 중이면: 안전하게 '삭제됨(deleted)'으로 보내고 group_id=NULL 처리
 * - 마지막으로 groups에서 실제 그룹 삭제
 */
if ($action === 'delete_group') {
    if (!csrf_check($_POST['csrf_token'] ?? '')) die('CSRF');
    $group_id = (int)($_POST['group_id'] ?? 0);
    if ($group_id > 0) {
        $pdo = db();
        $pdo->beginTransaction();
        // 1) 이 그룹의 약속을 '삭제됨'으로 마킹하고 group_id NULL로
        $pdo->prepare("UPDATE appointments SET status='deleted', group_id=NULL, updated_at=NOW() WHERE group_id=?")->execute([$group_id]);

        // 2) 사용자-그룹 관계 제거
        $pdo->prepare("DELETE FROM user_groups WHERE group_id=?")->execute([$group_id]);

        // 3) 그룹 삭제
        $pdo->prepare("DELETE FROM groups WHERE id=?")->execute([$group_id]);
        $pdo->commit();
    }
    header('Location: '.$redirect);
    exit;
}

http_response_code(400);
echo 'Bad request';