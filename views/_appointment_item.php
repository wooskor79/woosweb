<?php // views/_appointment_item.php
// 이 파일은 $a (약속 정보)와 $confs (확인자 정보) 변수를 전달받아 사용합니다.
$is_active = $a['status'] === 'active';
?>
<div class="appointment <?=!$is_active ? 'muted' : ''?>">
  <div class="row1">
    <div class="title">
      <span class="group-chip"><?=$a['is_common']?'공통':h($a['group_name'] ?? '그룹없음')?></span>
      <?=h($a['title'])?>
      <?php if(!$is_active): ?>
        <span class="status-chip <?=$a['status']==='deleted'?'del':''?>"><?=$a['status']==='deleted'?'삭제됨':'만료됨'?></span>
      <?php endif; ?>
    </div>
    <div class="meta">by <?=h($a['creator'])?> · <?=h($a['created_at'])?></div>
  </div>
  <div class="content"><?=nl2br(h($a['content']))?></div>

  <div class="who-text" id="who-<?=$a['id']?>">
    <?php
      $names = array_map(fn($c)=> (int)$c['confirmed']===1 ? $c['username'].'(확인함)' : null, $confs);
      $names = array_values(array_filter($names));
      echo empty($names) ? '<span class="muted">확인한 사람이 없습니다.</span>' : h(implode(', ', $names));
    ?>
  </div>

  <div class="action-row">
    <?php if (is_admin()): ?>
        <?php if ($is_active): ?>
            <form method="post" action="<?=h(base_url('controllers/appointments.php'))?>">
                <?=csrf_input()?>
                <input type="hidden" name="action" value="expire">
                <input type="hidden" name="id" value="<?=$a['id']?>">
                <button type="submit" class="outline small">만료</button>
            </form>
            <div class="delete-box" id="delbox-<?=$a['id']?>">
              <button type="button" class="danger small" onclick="showApptDeleteConfirm(<?=$a['id']?>)">삭제</button>
              <form class="confirm hidden inline" method="post" action="<?=h(base_url('controllers/appointments.php'))?>">
                <?=csrf_input()?> <input type="hidden" name="action" value="delete"> <input type="hidden" name="id" value="<?=$a['id']?>">
                <span class="muted">삭제?</span>
                <button type="submit" class="danger small">예</button>
                <button type="button" class="small" onclick="cancelApptDeleteConfirm(<?=$a['id']?>)">아니오</button>
              </form>
            </div>
        <?php else: // History items ?>
            <form method="post" action="<?=h(base_url('controllers/appointments.php'))?>">
                <?=csrf_input()?> <input type="hidden" name="action" value="unexpire"> <input type="hidden" name="id" value="<?=$a['id']?>">
                <button type="submit" class="outline small">재개</button>
            </form>
            <?php if ($a['status'] === 'expired'): ?>
                <div class="delete-box" id="delbox-<?=$a['id']?>">
                  <button type="button" class="danger small" onclick="showApptDeleteConfirm(<?=$a['id']?>)">삭제</button>
                  <form class="confirm hidden inline" method="post" action="<?=h(base_url('controllers/appointments.php'))?>">
                      <?=csrf_input()?> <input type="hidden" name="action" value="delete"> <input type="hidden" name="id" value="<?=$a['id']?>">
                      <span class="muted">삭제?</span>
                      <button type="submit" class="danger small">예</button>
                      <button type="button" class="small" onclick="cancelApptDeleteConfirm(<?=$a['id']?>)">아니오</button>
                  </form>
                </div>
            <?php else: // 'deleted' ?>
                <div class="delete-box" id="delbox-<?=$a['id']?>">
                  <button type="button" class="danger small" onclick="showApptDeleteConfirm(<?=$a['id']?>)">완전삭제</button>
                  <form class="confirm hidden inline" method="post" action="<?=h(base_url('controllers/appointments.php'))?>">
                      <?=csrf_input()?> <input type="hidden" name="action" value="purge"> <input type="hidden" name="id" value="<?=$a['id']?>">
                      <span class="muted">영구히 삭제합니다.</span>
                      <button type="submit" class="danger small">예</button>
                      <button type="button" class="small" onclick="cancelApptDeleteConfirm(<?=$a['id']?>)">아니오</button>
                  </form>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($is_active): ?>
        <?php
            $mine = array_values(array_filter($confs, fn($c)=>$c['username']===current_user()['username']))[0] ?? null;
            $my_confirmed = $mine ? (int)$mine['confirmed']===1 : false;
        ?>
        <form class="inline" method="post" action="<?=h(base_url('controllers/appointments.php'))?>" onsubmit="return toggleConfirm(event,this)">
            <?=csrf_input()?> <input type="hidden" name="action" value="toggle_confirm"> <input type="hidden" name="appointment_id" value="<?=$a['id']?>">
            <button type="submit" class="confirm-btn <?=$my_confirmed?'on':''?>" data-appt-id="<?=$a['id']?>"><?=$my_confirmed?'확인됨':'미확인'?></button>
        </form>
    <?php endif; ?>
  </div>
</div>
<hr class="divider">