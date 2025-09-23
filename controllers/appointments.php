<?php
require_once __DIR__.'/../init.php';
if (!is_logged_in()) { http_response_code(401); exit; }

$action = $_POST['action'] ?? $_GET['action'] ?? '';

/**
 * AJAX 판별은 헤더 기반으로만!
 * - X-Requested-With: XMLHttpRequest
 * - 또는 Accept: application/json
 * (폼 히든필드 ajax=1는 더 이상 사용하지 않음)
 */
$accept  = $_SERVER['HTTP_ACCEPT'] ?? '';
$isAjax  = (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest')
        || (stripos($accept, 'application/json') !== false);

function send_json($arr, $code=200){
    while (ob_get_level()) { ob_end_clean(); }
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($arr);
    exit;
}

if ($action === 'create') {
    if (!is_admin()) { http_response_code(403); exit; }
    if (!csrf_check($_POST['csrf_token'] ?? '')) die('CSRF');

    $title   = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $sel     = $_POST['group_select'] ?? ''; // 'common' | '<group_id>'

    $is_common = 0;
    $group_id  = null;

    if ($sel === 'common') {
        $is_common = 1;
        $group_id  = null;
    } else {
        $gid = (int)$sel;
        if ($gid > 0) {
            $is_common = 0;
            $group_id  = $gid;
        }
    }

    if ($title && ($is_common === 1 || $group_id !== null)) {
        $stmt = db()->prepare("INSERT INTO appointments(title, content, group_id, is_common, created_by) VALUES(?,?,?,?,?)");
        $stmt->execute([$title, $content, $group_id, $is_common, current_user()['id']]);
    }
    header('Location: '.base_url('index.php?page=appointments'));
    exit;
}

if ($action === 'toggle_confirm') {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        if ($isAjax) send_json(['ok'=>false,'reason'=>'csrf'], 400);
        die('CSRF');
    }
    $appointment_id = (int)($_POST['appointment_id'] ?? 0);
    $uid = current_user()['id'];

    try {
        // 토글
        $stmt = db()->prepare("SELECT confirmed FROM appointment_confirmations WHERE appointment_id=? AND user_id=?");
        $stmt->execute([$appointment_id, $uid]);
        $row = $stmt->fetch();

        if ($row) {
            $new = $row['confirmed'] ? 0 : 1;
            $stmt = db()->prepare("UPDATE appointment_confirmations SET confirmed=?, confirmed_at=NOW() WHERE appointment_id=? AND user_id=?");
            $stmt->execute([$new, $appointment_id, $uid]);
            $now_confirmed = (bool)$new;
        } else {
            $stmt = db()->prepare("INSERT INTO appointment_confirmations(appointment_id,user_id,confirmed) VALUES(?,?,1)");
            $stmt->execute([$appointment_id, $uid]);
            $now_confirmed = true;
        }

        // 최신 확인자 목록
        $listStmt = db()->prepare("
          SELECT u.username
          FROM appointment_confirmations c
          JOIN users u ON u.id=c.user_id
          WHERE c.appointment_id=? AND c.confirmed=1
          ORDER BY u.username
        ");
        $listStmt->execute([$appointment_id]);
        $names = array_map(fn($r)=>$r['username'], $listStmt->fetchAll());

        if ($isAjax) send_json(['ok'=>true, 'confirmed'=>$now_confirmed, 'who'=>$names, 'id'=>$appointment_id], 200);
        // 비-AJAX이면 리다이렉트 (JSON 노출 방지)
        header('Location: '.base_url('index.php?page=appointments'));
        exit;
    } catch (Throwable $e) {
        if ($isAjax) send_json(['ok'=>false,'reason'=>'server'], 500);
        die('Error');
    }
}

if ($action === 'expire' || $action === 'unexpire' || $action === 'delete' || $action === 'purge') {
    if (!is_admin()) { http_response_code(403); exit; }
    if (!csrf_check($_POST['csrf_token'] ?? '')) die('CSRF');
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'purge') {
        // 완전삭제: DB에서 영구 삭제
        $stmt = db()->prepare("DELETE FROM appointments WHERE id=?");
        $stmt->execute([$id]);
    } else {
        $to = $action === 'expire' ? 'expired' : ($action === 'unexpire' ? 'active' : 'deleted');
        $stmt = db()->prepare("UPDATE appointments SET status=?, updated_at=NOW() WHERE id=?");
        $stmt->execute([$to, $id]);
    }
    header('Location: '.base_url('index.php?page=appointments'));
    exit;
}

http_response_code(400);
echo 'Bad request';