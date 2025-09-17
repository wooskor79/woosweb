<?php
require_once __DIR__.'/../init.php';

$uid  = current_user()['id'];
$sort = strtolower($_GET['sort'] ?? 'asc');
$sort = in_array($sort, ['asc','desc'], true) ? $sort : 'asc';
$order = $sort === 'desc' ? 'DESC' : 'ASC';

if (is_admin()) {
  $sql = "SELECT u.id, u.username, u.phone
          FROM users u
          ORDER BY u.username $order";
  $rows = db()->query($sql)->fetchAll();
} else {
  $gids = user_group_ids($uid);
  if (!empty($gids)) {
    $in = implode(',', array_fill(0, count($gids), '?'));
    $sql = "SELECT DISTINCT u.id, u.username, u.phone
            FROM users u
            JOIN user_groups ug ON ug.user_id=u.id
            WHERE ug.group_id IN ($in)
            ORDER BY u.username $order";
    $stmt = db()->prepare($sql);
    $stmt->execute($gids);
    $rows = $stmt->fetchAll();
  } else {
    $rows = [];
  }
}

$base = base_url('index.php?page=contacts');
?>
<style>
  .contact-list {
    list-style: none;
    padding: 0;
    margin: 0;
    border-top: 1px solid #eee; /* 리스트 상단에도 선 추가하여 통일감 부여 */
  }
  .contact-item {
    display: flex; /* Flexbox 레이아웃 적용 */
    justify-content: space-between; /* 이름과 전화번호를 양쪽 끝으로 정렬 */
    align-items: center; /* 세로 중앙 정렬 */
    padding: 12px 8px;
    border-bottom: 1px solid #eee; /* 각 항목을 구분하는 하단 선 */
  }
  .contact-item:last-child {
    border-bottom: none; /* 마지막 항목의 하단 선은 제거 */
  }
  .contact-name {
    font-weight: 600; /* 이름 텍스트를 굵게 */
    font-size: 1rem;
  }
  .contact-phone {
    color: #555; /* 전화번호 텍스트 색상 */
    font-size: 0.95rem;
  }
</style>

<div class="panel">
  <div class="panel-header">
    <h2>연락처</h2>
  </div>
  <div class="panel-body">
    <div class="inline" style="gap:8px;margin:4px 0 12px;">
      <a class="outline small" href="<?=h($base.'&sort=asc')?>">▲ 오름차순</a>
      <a class="outline small" href="<?=h($base.'&sort=desc')?>">▼ 내림차순</a>
    </div>

    <?php if (empty($rows)): ?>
      <div class="empty">표시할 연락처가 없습니다.</div>
    <?php else: ?>
      <ul class="contact-list">
        <?php foreach($rows as $r): ?>
        <li class="contact-item">
          <span class="contact-name"><?=h($r['username'])?></span>
          <span class="contact-phone"><?=h($r['phone'])?></span>
        </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</div>