<?php
// /volume1/web/my_app/view/video.php
require_once __DIR__ . '/../init.php';

// ---------------------------------------------------
// ✅ [설정] 여기서 제외하고 싶은 폴더 이름을 지정하세요.
// ---------------------------------------------------
$exclude_dirs = [
    'Comics',
    // 여기에 추가로 제외할 폴더 이름을 넣으세요. 예: 'Animation'
];

// 기존 시스템 폴더 제외 목록과 사용자 지정 목록을 합칩니다.
$system_exclude_dirs = ['@eaDir', '#recycle'];
$master_exclude_list = array_merge($system_exclude_dirs, $exclude_dirs);


// ALLOWED_VIDEO_EXTS 상수를 사용하여 일관성 유지
$allowed_extensions = array_filter(array_map('trim', explode(',', ALLOWED_VIDEO_EXTS)));

$video_root_path = VIDEO_ROOT;
$real_root_path = realpath($video_root_path);
if ($real_root_path === false) { http_response_code(500); exit('VIDEO_ROOT not found.'); }

$current_relative_path = $_GET['path'] ?? '/';
$current_relative_path = str_replace('\\', '/', $current_relative_path);
if ($current_relative_path === '') $current_relative_path = '/';

$join = rtrim($real_root_path, '/') . '/' . ltrim($current_relative_path, '/');
$current_absolute_path = realpath($join);

// 보안: 루트 밖 접근 차단
if (!$current_absolute_path || strpos($current_absolute_path, $real_root_path) !== 0 || !is_dir($current_absolute_path)) {
    $current_relative_path = '/';
    $current_absolute_path = $real_root_path;
}

$items = @scandir($current_absolute_path) ?: [];

// 엔드포인트 베이스
$stream_base = rtrim(defined('APP_BASE') ? APP_BASE : '', '/');
$stream_base = ($stream_base === '') ? '/media/stream.php' : ($stream_base . '/media/stream.php');

$thumb_base  = rtrim(defined('APP_BASE') ? APP_BASE : '', '/');
$thumb_base  = ($thumb_base === '') ? '/media/thumb.php'  : ($thumb_base . '/media/thumb.php');

// 상대경로 계산
function rel_from_root(string $root, string $abs): string {
    $root = rtrim($root, '/');
    if ($abs === $root) return '';
    if (strpos($abs, $root . '/') === 0) return substr($abs, strlen($root) + 1);
    return '';
}
?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>파일 탐색기</title>
<style>
  :root{
    --gap: 12px;
    --fg: #111;
    --muted: #666;
    --bg: #fff;
    --br: 10px;
    --btn: #2d6cdf;
    --btn2: #666;
    --np: #24a148;     /* nPlayer */
    --mx: #ff6a00;     /* MX Player */
    --border: #ececec;
  }
  * { box-sizing: border-box; }
  html, body { margin:0; padding:0; background:var(--bg); color:var(--fg); }
  body { font-family: system-ui,-apple-system,Segoe UI,Roboto,Pretendard,Apple SD Gothic Neo,Noto Sans KR,sans-serif; }

  .wrap { max-width: 980px; margin: 0 auto; padding: 14px 16px; }
  h2 { margin: 6px 0 12px; padding-bottom: 10px; border-bottom: 2px solid var(--border); font-size: clamp(18px, 2.8vw, 22px); }
  .file-path { font-size: 14px; color: var(--muted); margin: 6px 0 14px; word-break: break-all; overflow-wrap: anywhere; }

  .up { margin: 8px 0 16px; }
  .up a { text-decoration:none; color: var(--fg); border: 1px solid var(--border); padding: 10px 12px; border-radius: var(--br); display:inline-flex; align-items:center; gap:8px; }

  ul.file-list { list-style: none; padding: 0; margin: 0; display: grid; grid-template-columns: 1fr; gap: 12px; }
  .dir-list { margin-bottom: 16px; } /* 폴더와 파일 목록 간격 */
  .dir { text-decoration: none; color: inherit; padding: 10px; display: block; background: #f8f9fa; border: 1px solid var(--border); border-radius: 8px; }
  .dir:hover { background: #f1f3f5; }


  .item {
    border: 1px solid var(--border); border-radius: var(--br); padding: 10px; background: #fff;
    display: grid; grid-template-columns: 1fr; grid-template-areas:
      "thumb"
      "meta"
      "actions";
    gap: 10px;
  }

  .thumb-wrap {
    grid-area: thumb;
    position: relative; width: 100%; border-radius: 8px; overflow: hidden; background: #f2f3f5;
  }
  .thumb-wrap::before { content: ''; display: block; padding-top: 56.25%; } /* 16:9 공간 확보 */
  .thumb { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; display:block; }

  .meta { grid-area: meta; display: grid; grid-template-columns: 1fr auto; gap: 8px; align-items: center; }
  .title {
    font-size: 15px; line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2; /* ✅ [수정] 표준 속성 추가 */
    -webkit-box-orient: vertical;
    overflow: hidden;
    word-break: break-word;
    overflow-wrap: anywhere;
  }
  .badge { font-size: 12px; color: #fff; background:#888; padding: 2px 8px; border-radius: 999px; height: 22px; display:inline-flex; align-items:center; }

  .actions { grid-area: actions; display: flex; flex-wrap: wrap; gap: 8px; width: 100%; }
  .btn {
    appearance: none; display: inline-flex; align-items: center; justify-content: center;
    padding: 10px 12px; border-radius: 10px; text-decoration: none; color: #fff; background: var(--btn);
    min-height: 44px; min-width: 44px; font-size: 14px; touch-action: manipulation;
  }
  .btn.secondary { background: var(--btn2); }
  .btn.np { background: var(--np); }
  .btn.mx { background: var(--mx); }

  @media (min-width: 700px) {
    .item {
      grid-template-columns: 160px 1fr auto;
      grid-template-areas: "thumb meta actions";
      align-items: center;
    }
    .actions { width: auto; justify-content: flex-end; }
  }

  @media (max-width: 420px) {
    .actions .btn { flex: 1 1 calc(50% - 8px); }
    .btn.secondary { flex: 1 1 100%; }
  }

  a, button { -webkit-tap-highlight-color: transparent; }
</style>
</head>
<body>
  <div class="wrap">
    <h2>영상보기</h2>
    <div class="file-path">현재 위치: <?= htmlspecialchars($current_relative_path) ?></div>

    <div class="up">
      <?php
      if ($current_absolute_path !== $real_root_path) {
          $parent_path = rtrim(dirname($current_relative_path), '/');
          if ($parent_path === '' || $parent_path === '.') $parent_path = '/';
          echo '<a href="?page=videos&amp;path=' . urlencode($parent_path) . '">⬆️ .. (상위 폴더)</a>';
      }
      ?>
    </div>

    <ul class="file-list dir-list">
      <?php
      // 디렉터리
      foreach ($items as $item) {
          if ($item === '.' || $item === '..') continue;
          $item_path = $current_absolute_path . '/' . $item;
          if (is_dir($item_path)) {
              // ✅ [수정] 마스터 제외 목록에 포함된 폴더는 건너뛰기
              if (in_array($item, $master_exclude_list)) continue;
              $link_path = rtrim($current_relative_path, '/') . '/' . $item;
              echo '<li><a class="dir" href="?page=videos&amp;path=' . urlencode($link_path) . '">📁 ' . htmlspecialchars($item) . '</a></li>';
          }
      }
      ?>
    </ul>

    <ul class="file-list">
      <?php
      // 파일
      foreach ($items as $item) {
          if ($item === '.' || $item === '..') continue;
          $item_path = $current_absolute_path . '/' . $item;
          if (!is_file($item_path)) continue;

          $ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
          if (!in_array($ext, $allowed_extensions)) continue;

          $relative_for_stream = rel_from_root($real_root_path, $item_path);
          if ($relative_for_stream === '') continue;

          $streamBase = $stream_base . '?p=' . rawurlencode($relative_for_stream);
          $downloadUrl = $streamBase . '&mode=download';

          $mtime = @filemtime($item_path) ?: time();
          $thumbUrl = $thumb_base . '?p=' . rawurlencode($relative_for_stream) . '&root=video&w=320&ts=' . $mtime;

          $ariaFile = htmlspecialchars($item, ENT_QUOTES);
          ?>
          <li class="item">
            <div class="thumb-wrap">
              <img class="thumb" src="<?= htmlspecialchars($thumbUrl) ?>" alt="썸네일: <?= $ariaFile ?>" loading="lazy"
                   onerror="this.style.display='none'; this.parentElement.style.background='#e9eef3';">
            </div>
            <div class="meta">
              <div class="title" title="<?= htmlspecialchars($item) ?>"><?= htmlspecialchars($item) ?></div>
              <span class="badge"><?= strtoupper($ext) ?></span>
            </div>
            <div class="actions">
              <a class="btn secondary" href="<?= htmlspecialchars($downloadUrl) ?>" aria-label="다운로드: <?= $ariaFile ?>">다운로드</a>
              <a class="btn np open-np" href="#" data-stream="<?= htmlspecialchars($streamBase) ?>" aria-label="nPlayer로 열기: <?= $ariaFile ?>">nPlayer</a>
              <a class="btn mx open-mx" href="#" data-stream="<?= htmlspecialchars($streamBase) ?>" aria-label="MX Player로 열기: <?= $ariaFile ?>">MX Player</a>
            </div>
          </li>
          <?php
      }
      ?>
    </ul>
  </div>

  <script>
    const ua = navigator.userAgent.toLowerCase();
    const isAndroid = ua.includes('android');
    const isIOS = /iphone|ipad|ipod/.test(ua);

    if (isIOS) {
      document.querySelectorAll('.open-mx').forEach(el => el.style.display = 'none');
    }
    
    function buildAbsStreamUrl(streamBase) {
      const mode = 'inline';
      const url = streamBase + (streamBase.includes('?') ? '&' : '?') + 'mode=' + mode;
      return new URL(url, window.location.origin).href;
    }

    function buildNPlayerLink(absUrl) {
      const noProto = absUrl.replace(/^https?:\/\//, '');
      return `nplayer-https://${noProto}`;
    }

    function buildMXIntent(absUrl, pro=false) {
      const noProto = absUrl.replace(/^https?:\/\//, '');
      const pkg = pro ? 'com.mxtech.videoplayer.pro' : 'com.mxtech.videoplayer.ad';
      return `intent://${noProto}#Intent;scheme=https;package=${pkg};end`;
    }

    document.querySelectorAll('.open-np').forEach(a => {
      a.addEventListener('click', (e) => {
        e.preventDefault();
        const streamBase = a.dataset.stream;
        const absUrl = buildAbsStreamUrl(streamBase);
        const link = buildNPlayerLink(absUrl);
        window.location.href = link;
      }, { passive: true });
    });

    document.querySelectorAll('.open-mx').forEach(a => {
      a.addEventListener('click', (e) => {
        e.preventDefault();
        if (!isAndroid) { alert('MX Player는 Android 전용입니다.'); return; }
        const streamBase = a.dataset.stream;
        const absUrl = buildAbsStreamUrl(streamBase);
        window.location.href = buildMXIntent(absUrl, false);
      }, { passive: true });
    });
  </script>
</body>
</html>