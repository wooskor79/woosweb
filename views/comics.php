<?php
// /views/comics.php
require_once __DIR__ . '/../init.php';

// --- 기본 설정 ---
$comics_root_path = '/volume1/ShareFolder/Comics';
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'zip', 'rar', 'cbz', 'cbr', 'pdf'];
$real_root_path = realpath($comics_root_path);

// --- 현재 요청 분석 ---
$search_query = trim($_GET['q'] ?? '');
$current_relative_path = '/';
$current_absolute_path = $real_root_path;

if ($real_root_path === false) {
    echo '<div class="panel"><div class="panel-header"><h2>오류</h2></div><div class="panel-body empty">만화책 폴더(' . htmlspecialchars($comics_root_path) . ')를 찾을 수 없습니다. 경로를 확인해주세요.</div></div>';
    return;
}

// 검색어가 없을 때만 경로 탐색 로직 실행
if (empty($search_query)) {
    $current_relative_path = $_GET['path'] ?? '/';
    $current_relative_path = str_replace('\\', '/', $current_relative_path);
    if ($current_relative_path === '') $current_relative_path = '/';

    $join = rtrim($real_root_path, '/') . '/' . ltrim($current_relative_path, '/');
    $current_absolute_path = realpath($join);

    if (!$current_absolute_path || strpos($current_absolute_path, $real_root_path) !== 0 || !is_dir($current_absolute_path)) {
        $current_relative_path = '/';
        $current_absolute_path = $real_root_path;
    }
}
?>
<style>
/* ✅ [수정] 북마크 관련 스타일 제거 */
.file-list li { display: flex; justify-content: space-between; align-items: center; }
.file-list li a { flex-grow: 1; }
</style>

<div class="panel">
  <div class="panel-header">
    <h2>만화책 보기</h2>
  </div>
  <div class="panel-body">

    <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; align-items: center;">
        <form method="get" action="" style="display: flex; flex-grow: 1;">
            <input type="hidden" name="page" value="comics">
            <input type="search" name="q" placeholder="캐시에서 폴더 검색..." value="<?= htmlspecialchars($search_query) ?>" style="flex-grow: 1; min-width: 150px;">
            <button type="submit">검색</button>
        </form>
        <a href="?page=comics" class="button outline">🏠 홈으로</a>
    </div>

    <?php if (!empty($search_query)): // --- 1. 캐시 기반 검색 결과 --- ?>
        <div class="file-path"><strong>'<?= htmlspecialchars($search_query) ?>'</strong> 폴더 검색 결과 (캐시)</div>
        <ul class="file-list">
        <?php
            $stmt = db()->prepare("SELECT relative_path FROM comic_folder_cache WHERE relative_path LIKE ? ORDER BY relative_path");
            $stmt->execute(['%' . $search_query . '%']);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($results)) {
                echo '<div class="empty">검색 결과가 없습니다.</div>';
            } else {
                foreach ($results as $item) {
                    $link = '?page=comics&path=' . urlencode($item['relative_path']);
            ?>
            <li>
                <a href="<?= $link ?>">
                    <span class="icon">📁</span>
                    <span class="name"><?= htmlspecialchars(basename($item['relative_path'])) ?></span>
                    <span class="muted small" style="margin-left: 8px;">(<?= htmlspecialchars(dirname($item['relative_path'])) ?>)</span>
                </a>
            </li>
            <?php
                }
            }
        ?>
        </ul>

    <?php else: // --- 2. 기본 폴더 탐색 --- ?>
        <div class="file-path">현재 위치: /<?= htmlspecialchars(ltrim($current_relative_path, '/')) ?></div>
        <?php if ($current_absolute_path !== $real_root_path):
            $parent_path = rtrim(dirname($current_relative_path), '/');
            if ($parent_path === '' || $parent_path === '.') $parent_path = '/';
        ?>
            <a class="up-link" href="?page=comics&amp;path=<?= urlencode($parent_path) ?>">⬆️ .. (상위 폴더)</a>
        <?php endif; ?>
        
        <ul class="file-list">
        <?php
            $items = @scandir($current_absolute_path) ?: [];
            $folders = [];
            $files = [];
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') continue;
                if (in_array($item, ['@eaDir', '#recycle'])) continue;
                
                $item_path = $current_absolute_path . '/' . $item;
                if (is_dir($item_path)) {
                    $relative_path = ltrim(rtrim($current_relative_path, '/') . '/' . $item, '/');
                    $folders[] = ['name' => $item, 'path' => $relative_path];
                } else {
                    $files[] = ['name' => $item];
                }
            }
            
            usort($folders, fn($a, $b) => strnatcasecmp($a['name'], $b['name']));
            usort($files, fn($a, $b) => strnatcasecmp($a['name'], $b['name']));
            
            foreach ($folders as $folder) {
                echo '<li><a href="?page=comics&path='.urlencode($folder['path']).'"><span class="icon">📁</span><span class="name">'.htmlspecialchars($folder['name']).'</span></a></li>';
            }

            foreach ($files as $file) {
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed_extensions)) continue;
                
                $relative_path = ltrim(rtrim($current_relative_path, '/') . '/' . $file['name'], '/');
                $viewer_link = '?page=comic_viewer&file=' . urlencode($relative_path);
                echo '<li><a href="'.$viewer_link.'"><span class="icon">📄</span><span class="name">'.htmlspecialchars($file['name']).'</span></a></li>';
            }

            if (empty($folders) && empty($files)) {
                echo '<div class="empty">표시할 파일이나 폴더가 없습니다.</div>';
            }
        ?>
        </ul>
    <?php endif; ?>
  </div>
</div>