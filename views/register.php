<?php require_once __DIR__.'/../init.php'; ?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>회원가입 - <?=h(APP_NAME)?></title>
<link rel="stylesheet" href="<?=h(base_url('assets/css/style.css'))?>">
<script defer src="<?=h(base_url('assets/js/app.js'))?>"></script>
</head>
<body class="auth">
  <div class="auth-card">
    <h1 class="title">회원가입</h1>
    <?php if (!empty($_GET['error'])): ?>
      <div class="error">입력을 확인하세요. (중복 아이디 가능성)</div>
    <?php endif; ?>
    <form method="post" action="<?=h(base_url('controllers/auth_controller.php'))?>">
      <?=csrf_input()?>
      <input type="hidden" name="action" value="register">
      <label>아이디(이름)</label>
      <input name="username" maxlength="50" required>
      <label>비밀번호</label>
      <input type="password" name="password" required>
      <label>전화번호</label>
      <input name="phone" maxlength="13" oninput="autoHyphenPhone(this)" placeholder="000-0000-0000">
      <button type="submit">가입하기</button>
    </form>
    <div class="small">
      <a href="<?=h(base_url('index.php'))?>">로그인으로 돌아가기</a>
    </div>
  </div>
</body>
</html>