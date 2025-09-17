<?php // views/player.php
$file_path = $_GET['file'] ?? '';
if (!$file_path) { die('파일이 지정되지 않았습니다.'); }
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>Video Player</title>
    <style> body { margin: 0; background: #000; } video { width: 100vw; height: 100vh; } </style>
</head>
<body>
    <video controls autoplay>
        <source src="index.php?page=videos&action=stream&file=<?= urlencode($file_path) ?>" type="video/mp4">
        브라우저가 비디오 태그를 지원하지 않거나, 지원하지 않는 영상 형식입니다.
    </video>
</body>
</html>