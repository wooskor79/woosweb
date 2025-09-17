<?php
require_once __DIR__.'/../init.php';
if (!is_admin()) { http_response_code(403); exit; }

// 정렬 파라미터
$sort = strtolower($_GET['sort'] ?? 'asc');
$sort = in_array($sort, ['asc','desc'], true) ? $sort : 'asc';
$order = $sort === 'desc' ? 'DESC' : 'ASC';

// ✅ [수정] 필터 파라미터 추가
$filter = $_GET['filter'] ?? 'all';

$groups = db()->query("SELECT id,name FROM groups ORDER BY name")->fetchAll();

// ✅ [수정] 필터에 따라 사용자 목록을 가져오는 SQL 변경
if ($filter === 'ungrouped') {
    // 그룹이 없는 사용자만 선택하는 쿼리
    $sql_users = "SELECT u.id, u.username, u.is_admin
                  FROM users u
                  LEFT JOIN user_groups ug ON u.id = ug.user_id
                  WHERE ug.group_id IS NULL AND u.is_admin = 0
                  ORDER BY u.username $order";
    $users = db()->query($sql_users)->fetchAll();
} else {
    // 모든 사용자를 선택하는 쿼리 (기존 로직)
    $sql_users = "SELECT id,username,is_admin FROM users ORDER BY username $order";
    $users  = db()->query($sql_users)->fetchAll();
}


$map = [];
$q = db()->query("SELECT ug.user_id, g.id gid, g.name gname
                  FROM user_groups ug JOIN groups g ON g.id=ug.group_id
                  ORDER BY g.name");
foreach ($q->fetchAll() as $r) {
  $map[$r['user_id']][] = $r;
}

$baseAdminUrl = base_url('index.php?page=admin');
?>
<div class="panel">
  <div class="panel-header">
    <h2>그룹/사용자 관리</h2>
  </div>
  <div class="panel-body">
    <h3>그룹 만들기</h3>
    <div class="inline" style="flex-wrap:wrap;gap:12px; margin-bottom: 24px;">
      <form class="inline" method="post" action="<?=h(base_url('controllers/groups.php'))?>">
        <?=csrf_input()?>
        <input type="hidden" name="action" value="create_group">
        <input type="hidden" name="redirect" value="<?=h($baseAdminUrl.'&sort='.$sort)?>">
        <input name="name" placeholder="새 그룹명" required>
        <button type="submit">그룹 만들기</button>
      </form>

      <div class="inline" style="gap:8px;flex-wrap:wrap;">
        <?php if (empty($groups)): ?>
          <span class="muted">그룹이 없습니다.</span>
        <?php else: ?>
          <?php foreach ($groups as $g): ?>
            <span class="chip" style="display:inline-flex;align-items:center;gap:6px; background: #fff; border: 1px solid var(--border);">
              <?=h($g['name'])?>
              <form method="post" action="<?=h(base_url('controllers/groups.php'))?>" onsubmit="return confirm('그룹 \'<?=h($g['name'])?>\'을 삭제할까요?\\n- 해당 그룹 소속 사용자는 소속이 해제됩니다.\\n- 해당 그룹의 약속은 \"삭제됨\"으로 이동합니다.')">
                <?=csrf_input()?>
                <input type="hidden" name="action" value="delete_group">
                <input type="hidden" name="group_id" value="<?=$g['id']?>">
                <input type="hidden" name="redirect" value="<?=h($baseAdminUrl.'&sort='.$sort)?>">
                <button type="submit" class="danger small" style="padding: 2px 6px; font-size: 11px;">삭제</button>
              </form>
            </span>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <h3>사용자별 소속 그룹 설정</h3>

    <div class="inline" style="gap:8px;margin:4px 0 12px; flex-wrap:wrap;">
      <div style="font-weight: bold; font-size: 13px;">정렬:</div>
      <a class="outline small <?= $sort === 'asc' ? 'active' : '' ?>" href="<?=h($baseAdminUrl.'&sort=asc&filter='.$filter)?>">▲ 오름차순</a>
      <a class="outline small <?= $sort === 'desc' ? 'active' : '' ?>" href="<?=h($baseAdminUrl.'&sort=desc&filter='.$filter)?>">▼ 내림차순</a>
      
      <div style="margin-left: 16px; font-weight: bold; font-size: 13px;">필터:</div>
      <a class="outline small <?= $filter === 'all' ? 'active' : '' ?>" href="<?=h($baseAdminUrl.'&sort='.$sort.'&filter=all')?>">모든 사용자</a>
      <a class="outline small <?= $filter === 'ungrouped' ? 'active' : '' ?>" href="<?=h($baseAdminUrl.'&sort='.$sort.'&filter=ungrouped')?>">그룹 없는 사용자</a>
    </div>

    <table class="table admin-user-table">
      <thead><tr><th style="width:200px">사용자</th><th>소속 그룹 (체크박스 다중 선택)</th><th style="width:140px">작업</th></tr></thead>
      <tbody>
        <?php if (empty($users)): ?>
          <tr><td colspan="3" class="empty">조건에 맞는 사용자가 없습니다.</td></tr>
        <?php else: ?>
          <?php foreach ($users as $u):
            $owned = array_column($map[$u['id']] ?? [], 'gid');
          ?>
          <tr>
            <td data-label="사용자">
              <strong><?=h($u['username'])?></strong>
              <?=$u['is_admin']?' <span class="chip">관리자</span>':''?>
            </td>
            <td data-label="소속 그룹">
              <form class="inline" method="post" action="<?=h(base_url('controllers/groups.php'))?>">
                <?=csrf_input()?>
                <input type="hidden" name="action" value="set_user_groups">
                <input type="hidden" name="user_id" value="<?=$u['id']?>">
                <input type="hidden" name="redirect" value="<?=h($baseAdminUrl.'&sort='.$sort.'&filter='.$filter)?>">
                <div class="checks" style="display:flex;flex-wrap:wrap;gap:8px;">
                  <?php foreach ($groups as $g): ?>
                    <label class="check-chip">
                      <input type="checkbox" name="group_ids[]" value="<?=$g['id']?>" <?=in_array($g['id'],$owned)?'checked':''?>>
                      <span><?=h($g['name'])?></span>
                    </label>
                  <?php endforeach; ?>
                </div>
                <button type="submit" class="outline small">적용</button>
              </form>
            </td>
            <td data-label="작업">
              <?php if ($u['id'] !== current_user()['id']): ?>
                <form method="post" action="<?=h(base_url('controllers/groups.php'))?>" onsubmit="return confirm('정말 이 사용자를 삭제할까요?\\n- 사용자가 만든 약속은 현재 관리자에게 위임됩니다.')">
                  <?=csrf_input()?>
                  <input type="hidden" name="action" value="delete_user">
                  <input type="hidden" name="user_id" value="<?=$u['id']?>">
                  <input type="hidden" name="redirect" value="<?=h($baseAdminUrl.'&sort='.$sort.'&filter='.$filter)?>">
                  <button type="submit" class="danger small">사용자 삭제</button>
                </form>
              <?php else: ?>
                <span class="muted">본인 계정</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>

    <p class="small" style="margin-top:8px;color:#64748b">
      * 관리자는 기능적으로 모든 그룹에 자동 소속으로 취급되어 전체 데이터 열람이 가능합니다.
    </p>
  </div>
</div>