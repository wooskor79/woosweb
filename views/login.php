<?php require_once __DIR__.'/../init.php'; ?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>로그인 - <?=h(APP_NAME)?></title>
<link rel="stylesheet" href="<?=h(base_url('assets/css/style.css'))?>">
<script defer src="<?=h(base_url('assets/js/app.js'))?>"></script>
</head>

<body class="auth">
  <div class="auth-card">
    <h1 class="title">로그인</h1>

    <?php if (!empty($_GET['error'])): ?>
      <div class="error">아이디 또는 비밀번호를 확인하세요.</div>
    <?php endif; ?>

    <form method="post" action="<?=h(base_url('controllers/auth_controller.php'))?>" style="display:flex;flex-direction:column;gap:12px;">
      <?=csrf_input()?>
      <input type="hidden" name="action" value="login">

      <!-- 아이디 -->
      <label for="username">아이디</label>
      <input id="username" name="username" required autofocus>

      <!-- 비밀번호 -->
      <label for="password">비밀번호</label>
      <input id="password" type="password" name="password" required>

      <!-- 로그인 버튼 -->
      <div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px;">
        <button type="submit" style="flex:1;">로그인</button>
        <a href="<?=h(base_url('index.php?page=register'))?>" style="margin-left:12px;font-size:12px;color:#555;text-decoration:none;">회원가입</a>
      </div>
    </form>
  </div>
</body>
</html>
