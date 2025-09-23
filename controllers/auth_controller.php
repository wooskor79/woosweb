<?php
require_once __DIR__.'/../init.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'login') {
    if (!csrf_check($_POST['csrf_token'] ?? '')) die('CSRF');
    $ok = login(trim($_POST['username'] ?? ''), $_POST['password'] ?? '');
    if ($ok) {
        header('Location: '.base_url('index.php'));
    } else {
        header('Location: '.base_url('index.php?error=1'));
    }
    exit;
}

if ($action === 'register') {
    if (!csrf_check($_POST['csrf_token'] ?? '')) die('CSRF');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = normalize_phone($_POST['phone'] ?? '');

    if ($username === '' || $password === '') {
        header('Location: '.base_url('index.php?page=register&error=1'));
        exit;
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    try {
        $stmt = db()->prepare("INSERT INTO users(username, password_hash, phone) VALUES(?,?,?)");
        $stmt->execute([$username, $hash, $phone]);
        // 자동 로그인
        login($username, $password);
        header('Location: '.base_url('index.php'));
    } catch (PDOException $e) {
        header('Location: '.base_url('index.php?page=register&error=dup'));
    }
    exit;
}

if ($action === 'logout') {
    logout();
    header('Location: '.base_url('index.php'));
    exit;
}