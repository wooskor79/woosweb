<?php
require_once __DIR__.'/../init.php';

$uid = current_user()['id'];

// due_date를 기준으로 그룹화하고, due_date와 id로 정렬합니다.
$sql = "SELECT * FROM tasks WHERE user_id = ? ORDER BY due_date, id";
$stmt = db()->prepare($sql);
$stmt->execute([$uid]);
$tasks_raw = $stmt->fetchAll();

$tasks_by_date = [];
foreach ($tasks_raw as $task) {
    $tasks_by_date[$task['due_date']][] = $task;
}
?>
<div class="panel">
    <div class="panel-header">
        <h2>할일</h2>
    </div>
    <div class="panel-body">
        <form method="post" action="<?=h(base_url('controllers/tasks.php'))?>" class="inline" style="margin-bottom: 20px; flex-wrap:wrap;">
            <?=csrf_input()?>
            <input type="hidden" name="action" value="create">
            <input type="text" name="title" placeholder="할일 내용 입력" required style="flex:1; min-width: 200px;">
            <input type="date" name="due_date" value="<?=date('Y-m-d')?>" required>
            <button type="submit">추가</button>
        </form>

        <?php if (empty($tasks_by_date)): ?>
            <div class="empty">등록된 할일이 없습니다.</div>
        <?php else: ?>
            <?php foreach ($tasks_by_date as $date => $tasks): ?>
                <h3 class="date-head"><?=h($date)?></h3>
                <ul class="todo-list">
                    <?php foreach ($tasks as $task): ?>
                        <li class="todo-item <?= $task['completed'] ? 'completed' : '' ?>">
                            <form method="post" action="<?=h(base_url('controllers/tasks.php'))?>" onchange="toggleTaskBox(this.completed_check)">
                                <?=csrf_input()?>
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="task_id" value="<?=$task['id']?>">
                                <input type="checkbox" name="completed_check" class="task-check" <?=$task['completed'] ? 'checked' : ''?>>
                            </form>

                            <div class="delete-box">
                                <button type="button" class="danger small delete-btn" onclick="showTaskDeleteConfirm(this)">삭제</button>
                                <form class="confirm inline hidden" method="post" action="<?=h(base_url('controllers/tasks.php'))?>">
                                    <?=csrf_input()?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="task_id" value="<?=$task['id']?>">
                                    <span>삭제?</span>
                                    <button type="submit" class="danger small">예</button>
                                    <button type="button" class="small" onclick="cancelTaskDeleteConfirm(this)">아니오</button>
                                </form>
                            </div>

                            <span class="title"><?=h($task['title'])?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>