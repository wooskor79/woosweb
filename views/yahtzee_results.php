<?php
require_once __DIR__.'/../controllers/yahtzee_results_controller.php';
?>
<div class="panel">
  <div class="panel-header">
    <h2>야찌(Yahtzee) 전체 결과</h2>
  </div>
  <div class="panel-body">
    
    <form method="get" class="inline" style="margin-bottom: 16px;">
      <input type="hidden" name="page" value="yahtzee_results">
      <select name="user_id" onchange="this.form.submit()" style="min-width: 200px; padding: 8px;">
        <option value="all">-- 모든 사용자 --</option>
        <?php foreach ($all_users as $user): ?>
          <option value="<?= h($user['id']) ?>" <?= ($selected_user_id == $user['id']) ? 'selected' : '' ?>>
            <?= h($user['username']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </form>

    <?php if ($selected_user_id !== 'all' && !empty($all_users)): ?>
      <?php 
        $selected_username = '';
        foreach($all_users as $u) { if ($u['id'] == $selected_user_id) $selected_username = $u['username']; }
      ?>
      <div class="stats-summary">
        <strong><?= h($selected_username) ?></strong> 님의 전적:
        <span style="color: blue;"><?= $wins ?>승</span>
        <span style="color: red;"><?= $losses ?>패</span>
        <span><?= $draws ?>무</span>
        (총 <?= count($results) ?> 게임)
      </div>
    <?php endif; ?>

    <div class="table-wrapper">
      <table class="table">
        <thead>
          <tr>
            <th>플레이어</th>
            <th>점수 (본인)</th>
            <th>점수 (AI)</th>
            <th>승/패</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($results)): ?>
            <tr>
              <td colspan="4" style="text-align: center; color: var(--muted);">
                표시할 결과가 없습니다.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($results as $row): ?>
              <tr>
                <td><?= h($row['username']) ?></td>
                <td><?= h($row['player_score']) ?></td>
                <td><?= h($row['ai_score']) ?></td>
                <td>
                  <?php // ✅ 2. 승/패/무승부로 표시
                  if ($row['winner'] === 'Player') {
                      echo '<span style="color:blue; font-weight:bold;">승리</span>';
                  } elseif ($row['winner'] === 'AI') {
                      echo '<span style="color:red;">패배</span>';
                  } else {
                      echo '<span>무승부</span>';
                  }
                  ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>