<?php
require_once __DIR__.'/init.php';

$page = $_GET['page'] ?? '';
$error = isset($_GET['error']);

if (!is_logged_in()) {
    if ($page === 'register') {
        require __DIR__.'/views/register.php';
    } else {
        require __DIR__.'/views/login.php';
    }
    exit;
}

// 로그인 후
require __DIR__.'/views/layout.php';
