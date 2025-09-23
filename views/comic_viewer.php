<?php
// /view/comic_viewer.php
require_once __DIR__ . '/../init.php';

// --- 설정 ---
$comics_root_path = '/volume1/ShareFolder/Comics';
$cache_base_dir = __DIR__ . '/../cache/comics_cache';
$allowed_image_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

// --- 보안: 파일 경로 확인 ---
$file_rel_path = $_GET['file'] ?? '';
if (empty($file_rel_path) || strpos($file_rel_path, '..') !== false) {
    die("잘못된 파일 경로입니다.");
}

$file_abs_path = realpath($comics_root_path . '/' . $file_rel_path);
if (!$file_abs_path || strpos($file_abs_path, realpath($comics_root_path)) !== 0) {
    die("허용되지 않은 파일에 접근할 수 없습니다.");
}

// --- 임시 폴더 및 압축 해제 ---
$file_hash = md5($file_rel_path);
$temp_dir = $cache_base_dir . '/' . $file_hash;
$image_files = [];

if (!is_dir($temp_dir)) {
    @mkdir($temp_dir, 0777, true);

    $ext = strtolower(pathinfo($file_abs_path, PATHINFO_EXTENSION));
    if ($ext === 'zip' || $ext === 'cbz') {
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive;
            if ($zip->open($file_abs_path) === TRUE) {
                $zip->extractTo($temp_dir);
                $zip->close();
            }
        } else {
            die("서버에 ZipArchive 모듈이 활성화되어 있지 않습니다.");
        }
    }
    // (참고) RAR/CBR은 보안 및 라이선스 문제로 서버에서 직접 압축 해제를 지원하기 어렵습니다.
    // 꼭 필요하다면 서버에 'unrar' 커맨드라인 도구를 설치하고 shell_exec 함수를 이용해야 합니다.
}

// --- 이미지 파일 목록 읽기 ---
if (is_dir($temp_dir)) {
    $files = scandir($temp_dir);
    foreach ($files as $file) {
        // Mac OS에서 생성된 보조 파일(__MACOSX) 무시
        if (strpos($file, '__MACOSX') !== false || $file[0] === '.') continue;

        $file_path = $temp_dir . '/' . $file;
        if (is_dir($file_path)) continue; // 하위 폴더는 무시

        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed_image_exts)) {
            $image_files[] = $file;
        }
    }
    natsort($image_files); // 자연어 순으로 정렬 (1.jpg, 2.jpg, 10.jpg)
}

$base_cache_url = base_url(str_replace($_SERVER['DOCUMENT_ROOT'], '', $cache_base_dir));
$image_urls = array_map(function($file) use ($base_cache_url, $file_hash) {
    return $base_cache_url . '/' . $file_hash . '/' . rawurlencode($file);
}, array_values($image_files));
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>만화책 뷰어</title>
<style>
    html, body { margin: 0; padding: 0; background: #212529; color: #fff; font-family: sans-serif; height: 100%; overflow: hidden; }
    .viewer-container { display: flex; flex-direction: column; height: 100%; }
    .viewer { flex: 1; display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative; }
    .viewer img { max-width: 100%; max-height: 100%; object-fit: contain; }
    .nav-overlay { position: absolute; top: 0; width: 40%; height: 100%; cursor: pointer; }
    .nav-overlay.left { left: 0; }
    .nav-overlay.right { right: 0; }
    .controls { background: rgba(0,0,0,0.6); padding: 10px; text-align: center; display: flex; align-items: center; justify-content: center; gap: 20px; flex-shrink: 0; user-select: none; }
    .controls button { background: #495057; color: #fff; border: 1px solid #6c757d; border-radius: 4px; padding: 10px 20px; cursor: pointer; font-size: 16px; }
    .controls button:disabled { background: #343a40; color: #6c757d; cursor: not-allowed; }
    .page-info { font-size: 18px; min-width: 80px; }
    .back-link { position: absolute; top: 15px; left: 15px; z-index: 10; color: #fff; background: rgba(0,0,0,0.6); padding: 8px 12px; border-radius: 4px; text-decoration: none; }
</style>
</head>
<body>
    <div class="viewer-container">
        <a class="back-link" href="?page=comics&path=<?= urlencode(dirname($file_rel_path)) ?>">목록으로</a>
        <div class="viewer">
            <img id="comic-page" src="data:," alt="만화책 페이지">
            <div id="prev-overlay" class="nav-overlay left" title="이전 페이지"></div>
            <div id="next-overlay" class="nav-overlay right" title="다음 페이지"></div>
        </div>
        <div class="controls">
            <button id="prev-btn">이전</button>
            <div id="page-info" class="page-info">0 / 0</div>
            <button id="next-btn">다음</button>
        </div>
    </div>

<script>
    (function() {
        const images = <?= json_encode($image_urls) ?>;
        const totalPages = images.length;
        let currentPage = 0;

        const imgElement = document.getElementById('comic-page');
        const pageInfoElement = document.getElementById('page-info');
        const prevBtn = document.getElementById('prev-btn');
        const nextBtn = document.getElementById('next-btn');
        const prevOverlay = document.getElementById('prev-overlay');
        const nextOverlay = document.getElementById('next-overlay');

        function showPage(pageNumber) {
            if (pageNumber < 0 || pageNumber >= totalPages) return;
            
            // 이미지 미리 로딩
            const preloader = new Image();
            preloader.src = images[pageNumber];
            preloader.onload = () => {
                imgElement.src = preloader.src;
                currentPage = pageNumber;
                updateControls();
            };
        }

        function updateControls() {
            pageInfoElement.textContent = `${currentPage + 1} / ${totalPages}`;
            prevBtn.disabled = (currentPage === 0);
            nextBtn.disabled = (currentPage === totalPages - 1);
        }

        function nextPage() { showPage(currentPage + 1); }
        function prevPage() { showPage(currentPage - 1); }

        // Event Listeners
        nextBtn.addEventListener('click', nextPage);
        prevBtn.addEventListener('click', prevPage);
        nextOverlay.addEventListener('click', nextPage);
        prevOverlay.addEventListener('click', prevPage);

        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowRight' || e.key === ' ') nextPage();
            else if (e.key === 'ArrowLeft') prevPage();
        });

        // Initial load
        if (totalPages > 0) {
            showPage(0);
        } else {
            pageInfoElement.textContent = "이미지를 찾을 수 없습니다.";
            prevBtn.disabled = true;
            nextBtn.disabled = true;
        }
    })();
</script>
</body>
</html>