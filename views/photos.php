<?php
// /volume1/web/my_app/view/photos.php
require_once __DIR__.'/../init.php';

/**
 * 사진 보기: PHOTO_ROOT 아래 이미지를 재귀 스캔 → 최신순 그리드
 * - 썸네일: /thumb.php?p=상대경로&root=photo&w=224&ts=mtime  <-- 사이즈 수정됨
 * - 원본:   /photo_proxy.php?p=상대경로
 * - 중복 방지: @eaDir/앱 캐시/숨김/보조 파일/썸네일 파일 제외 필터 적용
 */

if (!defined('PHOTO_ROOT')) {
  ?>
  <div class="panel">
    <div class="panel-header"><h2>사진보기</h2></div>
    <div class="empty">PHOTO_ROOT가 설정되지 않았습니다. config.php 또는 init.php에서 PHOTO_ROOT를 지정해주세요.</div>
  </div>
  <?php
  return;
}

// 허용 확장자 (상수 우선)
$allowed = [];
if (defined('ALLOWED_PHOTO_EXTS') && is_string(ALLOWED_PHOTO_EXTS)) {
  $allowed = array_filter(array_map('trim', explode(',', strtolower(ALLOWED_PHOTO_EXTS))));
}
if (empty($allowed)) {
  $allowed = ['jpg','jpeg','png','gif','webp'];
}

$root = realpath(PHOTO_ROOT);
if (!$root || !is_dir($root)) {
  ?>
  <div class="panel"><div class="empty">PHOTO_ROOT 경로가 존재하지 않거나 폴더가 아닙니다.</div></div>
  <?php
  return;
}

/** === 제외할 디렉터리/파일 패턴 정의 === */
$excludeDirNames = [
  '@eaDir', '.@__thumb', '.thumbnails', '.thumbnail', '.cache', 'cache', 'thumbs', '.git', '.svn'
];
// 우리 앱의 사진 썸네일 캐시 디렉터리도 제외
$photoCachePath = defined('THUMB_CACHE_DIR_PHOTO') ? realpath(THUMB_CACHE_DIR_PHOTO) : null;

/** === 필터 === */
$dirIt = new RecursiveDirectoryIterator(
  $root,
  FilesystemIterator::SKIP_DOTS // . .. 제외
  // FilesystemIterator::FOLLOW_SYMLINKS 를 붙이지 않음(심볼릭 링크 따라가지 않기)
);

$filter = new RecursiveCallbackFilterIterator($dirIt, function($current, $key, $iterator) use ($excludeDirNames, $photoCachePath) {
  $name = $current->getFilename();
  $path = $current->getPathname();

  // 공통: @eaDir 경로 강제 제외
  if (strpos($path, DIRECTORY_SEPARATOR.'@eaDir'.DIRECTORY_SEPARATOR) !== false) return false;

  // 캐시 폴더 경로 제외(실제 경로가 ROOT 하위인 경우)
  if ($photoCachePath && strpos($path, $photoCachePath) === 0) return false;

  if ($current->isDir()) {
    // 이름으로 제외할 디렉터리
    if (in_array($name, $excludeDirNames, true)) return false;
    // 숨김 디렉터리(.으로 시작) 제외
    if (strlen($name) > 0 && $name[0] === '.') return false;
    return true; // 디렉터리는 통과
  }

  // 파일: 숨김/보조/썸네일 파일 제외
  // 숨김 파일 제외(., ._)
  if (strlen($name) > 0 && ($name[0] === '.' || strpos($name, '._') === 0)) return false;
  // Windows 보조
  if (strcasecmp($name, 'Thumbs.db') === 0) return false;
  // Synology 썸네일 파일 규칙
  if (strpos($name, 'SYNOPHOTO_') === 0 || strpos($name, 'SYNOPHOTO_THUMB') !== false) return false;

  // 확장자 필터
  $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
  return in_array($ext, $GLOBALS['allowed'], true);
});

// 재귀 순회(파일만 수집)
$rii = new RecursiveIteratorIterator($filter, RecursiveIteratorIterator::SELF_FIRST);
$files = [];
foreach ($rii as $f) {
  /** @var SplFileInfo $f */
  if ($f->isFile()) {
    $files[] = $f->getPathname();
  }
}

// 중복 경로 정리(혹시 모를 중복 대비)
$files = array_values(array_unique($files));

// 최신순 정렬 (수정시간 기준)
usort($files, fn($a,$b) => filemtime($b) <=> filemtime($a));

// PHOTO_ROOT 기준 상대경로 계산
function rel_from_root(string $root, string $abs): string {
  $root = rtrim($root, '/');
  if ($abs === $root) return '';
  if (strpos($abs, $root . '/') === 0) return substr($abs, strlen($root) + 1);
  return '';
}

// base_url() 있는 경우 사용
$B = function(string $path) {
  return function_exists('base_url') ? base_url($path) : $path;
};
?>
<div class="panel">
  <div class="panel-header">
    <h2>사진보기</h2>
  </div>

  <?php if (empty($files)): ?>
    <div class="empty">표시할 사진이 없습니다. (경로/권한/open_basedir 확인)</div>
  <?php else: ?>
    
    <div class="album-grid">
      <?php foreach ($files as $i => $abs):
        $rel   = rel_from_root($root, $abs);
        $et    = rawurlencode($rel);
        $mtime = @filemtime($abs) ?: time();

        // ✅ 썸네일 크기 수정 (320 -> 224)
        $thumb = $B('thumb.php?p='.$et.'&root=photo&w=224&ts='.$mtime);
        $full  = $B('photo_proxy.php?p='.$et);
      ?>
      <a class="tile" href="<?=$full?>" data-index="<?=$i?>" data-full="<?=$full?>">
        <img loading="lazy" src="<?=$thumb?>" alt="사진 썸네일"
             onerror="this.style.display='none'; this.parentElement.style.background='#e9eef3';">
      </a>
      <?php endforeach; ?>
    </div>

    <div class="lightbox" id="lightbox" aria-hidden="true">
      <button class="lb-close" id="lbClose" aria-label="닫기">×</button>
      <button class="lb-prev"  id="lbPrev"  aria-label="이전">‹</button>
      <img id="lbImg" src="" alt="">
      <button class="lb-next"  id="lbNext"  aria-label="다음">›</button>
    </div>

    <script>
    (function(){
      const items = Array.from(document.querySelectorAll('.album-grid .tile'));
      const box   = document.getElementById('lightbox');
      const img   = document.getElementById('lbImg');
      const btnC  = document.getElementById('lbClose');
      const btnP  = document.getElementById('lbPrev');
      const btnN  = document.getElementById('lbNext');
      let cur = -1;

      function openAt(i){
        if (i<0||i>=items.length) return;
        cur = i;
        img.src = items[i].dataset.full;
        box.classList.add('open');
        box.setAttribute('aria-hidden','false');
      }
      function closeLB(){
        box.classList.remove('open');
        box.setAttribute('aria-hidden','true');
        img.src = '';
      }
      function prev(){ if (cur>0) openAt(cur-1); }
      function next(){ if (cur<items.length-1) openAt(cur+1); }

      items.forEach((a,idx)=>{
        a.addEventListener('click', (e)=>{
          e.preventDefault();
          openAt(idx);
        }, { passive: true });
      });

      btnC.addEventListener('click', closeLB, { passive: true });
      btnP.addEventListener('click', prev,    { passive: true });
      btnN.addEventListener('click', next,    { passive: true });

      box.addEventListener('click', (e)=>{ if (e.target===box) closeLB(); }, { passive: true });

      window.addEventListener('keydown', (e)=>{
        if (!box.classList.contains('open')) return;
        if (e.key==='Escape') closeLB();
        if (e.key==='ArrowLeft') prev();
        if (e.key==='ArrowRight') next();
      });
    })();
    </script>
  <?php endif; ?>
</div>