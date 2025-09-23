<?php
require_once __DIR__.'/../init.php';

$uid = current_user()['id'];

// ✅ 그룹 미배정 사용자(관리자 제외) 안내만 표시
if (!is_logged_in() || (empty(user_group_ids($uid)) && !is_admin())) {
?>
  <div class="panel appointments">
    <div class="center-note">
      <div class="big">그룹에 속하지 않으면 사용불가 관리자에 문의하세요</div>
    </div>
  </div>
<?php
  return;
}


// ===================================================================
// 🚀 성능 개선 1: 약속 목록을 단 하나의 쿼리로 가져오기
// ===================================================================

// ✅ [수정] 관리자와 일반 사용자의 약속 조회 조건을 분리
if (is_admin()) {
    $params = [];
    $cond = '1'; // 관리자는 모든 약속을 볼 수 있도록 조건 없음
} else {
    $gidList = user_group_ids($uid);
    $gidIn = implode(',', array_fill(0, count($gidList), '?')) ?: 'NULL';
    $params = $gidList;
    $cond = "(a.is_common=1 OR a.group_id IN ($gidIn))";
}

$sql = "SELECT a.*, g.name AS group_name, u.username AS creator
        FROM appointments a
        LEFT JOIN groups g ON g.id=a.group_id
        JOIN users u ON u.id=a.created_by
        WHERE $cond AND a.status IN ('active', 'expired', 'deleted')
        ORDER BY
          CASE a.status
            WHEN 'active' THEN 1
            WHEN 'expired' THEN 2
            WHEN 'deleted' THEN 3
          END,
          COALESCE(a.updated_at, a.created_at) DESC";

$stmt = db()->prepare($sql);
$stmt->execute($params);
$all_appointments = $stmt->fetchAll();

// PHP에서 active와 history로 분리
$active = [];
$history = [];
foreach ($all_appointments as $a) {
    if ($a['status'] === 'active') {
        $active[] = $a;
    } else {
        $history[] = $a;
    }
}


// ===================================================================
// 🚀 성능 개선 2: N+1 문제를 해결하기 위해 확인자 정보를 한 번에 가져오기
// ===================================================================
$appointment_ids = array_column($all_appointments, 'id');
$confirmations_by_id = [];
if (!empty($appointment_ids)) {
    $id_in = implode(',', array_fill(0, count($appointment_ids), '?'));
    $sql_confs = "SELECT c.appointment_id, u.username, c.confirmed
                  FROM appointment_confirmations c
                  JOIN users u ON u.id=c.user_id
                  WHERE c.appointment_id IN ($id_in)
                  ORDER BY u.username";
    $stmt_confs = db()->prepare($sql_confs);
    $stmt_confs->execute($appointment_ids);
    $all_confirmations = $stmt_confs->fetchAll();

    // appointment_id를 키로 사용하여 데이터를 재구성
    foreach ($all_confirmations as $conf) {
        $confirmations_by_id[$conf['appointment_id']][] = $conf;
    }
}

// 이제 이 함수는 사용하지 않습니다.
// function confirmations_for($appt_id): array { ... }


// 관리자: 약속 생성용 그룹 목록
$groups = [];
if (is_admin()) {
  $groups = db()->query("SELECT id,name FROM groups ORDER BY name")->fetchAll();
}
?>
<div class="panel appointments">
  <div class="panel-header">
    <h2 style="margin:0;">약속</h2>
    <?php if (is_admin()): ?>
    <button type="button" class="outline" onclick="document.getElementById('create-form-box').classList.toggle('hidden')">새 약속 만들기</button>
    <?php endif; ?>
  </div>

  <?php if (is_admin()): ?>
  <div id="create-form-box" class="hidden" style="margin-bottom:16px; border-bottom:1px dashed var(--border); padding-bottom:16px;">
    <form class="inline" method="post" action="<?=h(base_url('controllers/appointments.php'))?>" style="flex-wrap:wrap;">
      <?=csrf_input()?>
      <input type="hidden" name="action" value="create">
      <label>제목</label>
      <input name="title" required>
      <label>내용</label>
      <input name="content" placeholder="간단한 내용">

      <select name="group_select" id="group_select" style="min-width:200px;">
        <option value="common">공통(모든 그룹)</option>
        <optgroup label="그룹 선택">
          <?php foreach($groups as $g): ?>
            <option value="<?=$g['id']?>"><?=h($g['name'])?></option>
          <?php endforeach; ?>
        </optgroup>
      </select>
      <button type="submit">만들기</button>
    </form>
  </div>
  <?php endif; ?>


  <details class="expired-box">
    <summary>지난기록 <?=empty($history)?'':'('.count($history).')'?></summary>
    <?php if (empty($history)): ?>
      <div class="empty">지난기록이 없습니다.</div>
    <?php else: ?>
      <div class="list">
        <?php foreach($history as $a): ?>
          <?php // 🔧 구조 개선: 약속 항목을 별도 파일로 분리하여 include
            $confs = $confirmations_by_id[$a['id']] ?? [];
            include __DIR__.'/_appointment_item.php';
          ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </details>

  <h3 class="section-title">진행중인 약속</h3>
  <div class="list">
    <?php if (empty($active)): ?>
      <div class="empty">진행중 약속이 없습니다.</div>
    <?php else: ?>
      <?php foreach($active as $a): ?>
        <?php // 🔧 구조 개선: 약속 항목을 별도 파일로 분리하여 include
            $confs = $confirmations_by_id[$a['id']] ?? [];
            include __DIR__.'/_appointment_item.php';
        ?>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>