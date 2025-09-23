<?php
require_once __DIR__.'/../init.php';
$user = current_user();
$active = $_GET['page'] ?? 'appointments';

// 각 페이지에 필요한 스크립트 파일을 매핑합니다.
$scripts_map = [
    'appointments' => 'app.appointments.js',
    'tasks' => 'app.tasks.js',
    'yahtzee' => 'app.yahtzee.js',
];
$page_script = $scripts_map[$active] ?? null;

// ✅ [추가] 각 페이지에 필요한 CSS 파일을 매핑합니다.
$styles_map = [
    'yahtzee' => 'yahtzee.css',
];
$page_style = $styles_map[$active] ?? null;
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?=h(APP_NAME)?></title>
<link rel="stylesheet" href="<?=h(base_url('assets/css/style.css'))?>">

<?php if ($page_style): ?>
<link rel="stylesheet" href="<?=h(base_url('assets/css/' . $page_style))?>">
<?php endif; ?>

<script defer src="<?=h(base_url('assets/js/app.common.js'))?>"></script>
<?php if ($page_script): ?>
<script defer src="<?=h(base_url('assets/js/' . $page_script))?>"></script>
<?php endif; ?>

</head>
<body class="app">
<div class="container">
  <aside class="sidebar">

    <form class="logout top" method="post" action="<?=h(base_url('controllers/auth_controller.php'))?>">
      <?=csrf_input()?>
      <input type="hidden" name="action" value="logout">
      <button type="submit">로그아웃</button>
    </form>

    <div class="brand"><?=h(APP_NAME)?></div>

    <nav class="menu">
      <a class="<?=$active==='appointments'?'active':''?>" href="<?=h(base_url('index.php?page=appointments'))?>">약속</a>
      <a class="<?=$active==='tasks'?'active':''?>" href="<?=h(base_url('index.php?page=tasks'))?>">할일</a>
      <a class="<?=$active==='contacts'?'active':''?>" href="<?=h(base_url('index.php?page=contacts'))?>">연락처</a>
      <a class="<?=$active==='photos'?'active':''?>" href="<?=h(base_url('index.php?page=photos'))?>">사진보기</a>
      <!-- <a class="<?=$active==='videos'?'active':''?>" href="<?=h(base_url('index.php?page=videos'))?>">영화보기</a> -->
      <a class="<?=$active==='jellyfin'?'active':''?>" href="<?=h(base_url('index.php?page=jellyfin'))?>">영화보기</a>
      <a class="<?=$active==='music'?'active':''?>" href="<?=h(base_url('index.php?page=music'))?>">아이묭 음악</a>
      <a class="<?=$active==='comics'?'active':''?>" href="<?=h(base_url('index.php?page=comics'))?>">만화책 보기</a>
      <a class="<?=$active==='yahtzee'?'active':''?>" href="<?=h(base_url('index.php?page=yahtzee'))?>">Yahtzee 하기</a>

      <?php if (is_admin()): ?>
      <div class="admin-label">관리자</div>
      <a class="<?=$active==='admin'?'active':''?>" href="<?=h(base_url('index.php?page=admin'))?>">그룹/사용자 관리</a>
      <a class="<?=$active==='yahtzee_results'?'active':''?>" href="<?=h(base_url('index.php?page=yahtzee_results'))?>">야찌 결과보기</a>
      <?php endif; ?>
    </nav>

  </aside>

  <main class="content">
    <?php
      switch ($active) {
        case 'tasks':        require __DIR__.'/tasks.php'; break;
        case 'contacts':     require __DIR__.'/contacts.php'; break;
        case 'photos':       require __DIR__.'/photos.php'; break;
        // case 'videos':       require __DIR__.'/videos.php'; break;
        case 'jellyfin':       require __DIR__.'/jellyfin.php'; break;
        case 'music':       require __DIR__.'/music.php'; break;
        case 'comics':       require __DIR__.'/comics.php'; break;
        case 'comic_viewer': require __DIR__.'/comic_viewer.php'; break;
        case 'yahtzee':      require __DIR__.'/yahtzee.php'; break;
        case 'admin':        require __DIR__.'/dashboard.php'; break;
        case 'yahtzee_results': require __DIR__.'/yahtzee_results.php'; break;
        case 'appointments':
        default:             require __DIR__.'/appointments.php'; break;
      }
    ?>
  </main>
</div>
</body>
</html>