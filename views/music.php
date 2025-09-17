<style>
    .music-page-container {
        /* 플레이어가 떠다니므로, 목록이 전체 공간을 사용합니다. */
        padding: 24px;
        box-sizing: border-box;
        height: 100vh;
        display: flex;
        flex-direction: column;
    }
    .music-page-container h2 {
        margin-bottom: 16px;
        flex-shrink: 0;
    }
    .music-list {
        list-style: none;
        padding-left: 0;
        margin: 0;
        flex: 1; /* 남은 세로 공간을 모두 차지 */
        overflow-y: auto; /* 목록이 길어지면 스크롤 생성 */
    }
    .music-list a {
        display: block;
        padding: 8px 12px;
        border-radius: 6px;
        text-decoration: none;
        color: var(--ink);
    }
    .music-list a:hover {
        background-color: #f0f7ff;
    }
    .music-list a.playing {
        background-color: var(--primary);
        color: white;
        font-weight: bold;
    }

    /* =========================================
       PIP 플레이어 스타일
       ========================================= */
    #pip-player {
        position: absolute; /* 화면 기준으로 자유롭게 떠다니도록 설정 */
        z-index: 1000;      /* 다른 요소들보다 항상 위에 있도록 설정 */
        background-color: var(--panel);
        border: 1px solid var(--border);
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        display: flex;
        flex-direction: column;
        /* 초기 크기 및 위치 */
        width: 900px;
        height: 506px;
        top: 100px;
        left: 450px;
    }

    .pip-header {
        display: flex; /* ✅ 변경: 내부 요소 정렬을 위해 flex 추가 */
        align-items: center; /* ✅ 추가: 세로 중앙 정렬 */
        gap: 10px; /* ✅ 추가: 버튼과 글씨 사이 간격 */
        padding: 8px 10px; /* ✅ 변경: 패딩 조정 */
        cursor: move; /* 마우스 커서를 이동 모양으로 변경 */
        background-color: #f6fafe;
        border-bottom: 1px solid var(--border);
        color: var(--ink);
        font-size: 14px;
        font-weight: bold;
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
    }

    /* ✅ 추가: 드래그 버튼 스타일 */
    .drag-handle-btn {
        background-color: #e53935;
        color: white;
        font-size: 12px;
        padding: 4px 8px;
        border-radius: 6px;
        /* 사용자가 이 글씨를 선택(드래그)하지 못하게 막아 드래그 경험을 향상시킴 */
        user-select: none; 
    }

    .pip-content {
        flex: 1; /* 헤더를 제외한 남은 공간을 모두 차지 */
        background: #000;
    }

    #youtube-iframe {
        width: 100%;
        height: 100%;
        border: none;
    }

    .resizable-handle {
        position: absolute;
        width: 15px;
        height: 15px;
        right: 0;
        bottom: 0;
        cursor: se-resize; /* 크기 조절 모양으로 커서 변경 */
        background: transparent;
    }
</style>

<div class="music-page-container">
    <h2>🎵 아이묭 음악 듣기 🎵</h2>
    <ul class="music-list" id="music-list">
        <li id="song-TSGOZyt2iDI"><a href="#" onclick="loadMusic('TSGOZyt2iDI', this); return false;">愛を伝えたいだとか - Ai Wo Tsutaetaidatoka - </a></li>
        <li id="song-20Pf9exP2P4"><a href="#" onclick="loadMusic('20Pf9exP2P4', this); return false;">マリーゴールド - Marigold - </a></li>
        <li id="song-zfnZxuaVZyM"><a href="#" onclick="loadMusic('zfnZxuaVZyM', this); return false;">君はロックを聴かない - Kimi Wa Rock Wo Kikanai - </a></li>
        <li id="song-dF3GDN2P84A"><a href="#" onclick="loadMusic('dF3GDN2P84A', this); return false;">ハルノヒ - Harunohi - </a></li>
        <li id="song-KK8A6pVrCdQ"><a href="#" onclick="loadMusic('KK8A6pVrCdQ', this); return false;">愛を知るまでは - Till I Know What Love Is (I'm Never Gonna Die) - </a></li>
        <li id="song-k-WRFG_k30g"><a href="#" onclick="loadMusic('k-WRFG_k30g', this); return false;">会いに行くのに - Wish I could see you, but - </a></li>
        <li id="song-PKZac_B93DE"><a href="#" onclick="loadMusic('PKZac_B93DE', this); return false;">ラッキーカラー - Lucky Color - </a></li>
        <li id="song-kbJrEHj-2qk"><a href="#" onclick="loadMusic('kbJrEHj-2qk', this); return false;">空の青さを知る人よ - Her Blue Sky - </a></li>
        <li id="song-wdDqsb1wmMk"><a href="#" onclick="loadMusic('wdDqsb1wmMk', this); return false;">双葉 - Futaba - </a></li>
        <li id="song-pVNR1XgRRno"><a href="#" onclick="loadMusic('pVNR1XgRRno', this); return false;">スケッチ - Sketch - </a></li>
        <li id="song-T4rS8-vz94o"><a href="#" onclick="loadMusic('T4rS8-vz94o', this); return false;">ざらめ - Zarame - </a></li>
        <li id="song-4dTduRcOjIo"><a href="#" onclick="loadMusic('4dTduRcOjIo', this); return false;">夢追いベンガル - Dream Chaser Bengal - </a></li>
        <li id="song-3Nq4E5ATUz4"><a href="#" onclick="loadMusic('3Nq4E5ATUz4', this); return false;">裸の心 - Naked Heart - </a></li>
        <li id="song-ABSKGxOVI9U"><a href="#" onclick="loadMusic('ABSKGxOVI9U', this); return false;">GOOD NIGHT BABY - </a></li>
        <li id="song-L5nD74wJWkQ"><a href="#" onclick="loadMusic('L5nD74wJWkQ', this); return false;">ら、のはなし - What If... - </a></li>
        <li id="song-KNvVkC96hJk"><a href="#" onclick="loadMusic('KNvVkC96hJk', this); return false;">恋をしたから - Because I'm in Love - </a></li>
        <li id="song-3p4L3XYHXio"><a href="#" onclick="loadMusic('3p4L3XYHXio', this); return false;">貴方解剖純愛歌～死ね～ - </a></li>
        <li id="song-nhq52Q7MHFA"><a href="#" onclick="loadMusic('nhq52Q7MHFA', this); return false;">愛の花 - ai no hana - </a></li>
        <li id="song-6Mwg_NrfKOk"><a href="#" onclick="loadMusic('6Mwg_NrfKOk', this); return false;">19歳になりたくない - </a></li>
        <li id="song-NgAnXUbPqyw"><a href="#" onclick="loadMusic('NgAnXUbPqyw', this); return false;">ひかりもの - Raw Like Sushi - </a></li>
        <li id="song-sfJVV6n3jUQ"><a href="#" onclick="loadMusic('sfJVV6n3jUQ', this); return false;">3636 - </a></li>
        <li id="song-PQlVzEAvxMA"><a href="#" onclick="loadMusic('PQlVzEAvxMA', this); return false;">さよならの今日に - On This Day We Say Goodbye - </a></li>
        <li id="song-VTN4SgUpdMs"><a href="#" onclick="loadMusic('VTN4SgUpdMs', this); return false;">今夜このまま - Let the Night - </a></li>
        <li id="song-zrbQ1AOY2kk"><a href="#" onclick="loadMusic('zrbQ1AOY2kk', this); return false;">ノット・オーケー - NOT OK - </a></li>
        <li id="song-uUtrDWfghQM"><a href="#" onclick="loadMusic('uUtrDWfghQM', this); return false;">初恋が泣いている - My First Love is Crying - </a></li>
        <li id="song-bts6MsScoOY"><a href="#" onclick="loadMusic('bts6MsScoOY', this); return false;">あのね - Anone - </a></li>
        <li id="song-3uyBVI5kWkU"><a href="#" onclick="loadMusic('3uyBVI5kWkU', this); return false;">鯉 - Carp - </a></li>
        <li id="song-WU8uaa3PCbY"><a href="#" onclick="loadMusic('WU8uaa3PCbY', this); return false;">青春と青春と青春 - Seishun to Seishun to Seishun - </a></li>
        <li id="song-B6EKkD2QugM"><a href="#" onclick="loadMusic('B6EKkD2QugM', this); return false;">葵 - Aoi - </a></li>
        <li id="song-SrazvevTn1s"><a href="#" onclick="loadMusic('SrazvevTn1s', this); return false;">スーパーガール - Super Girl - </a></li>
        <li id="song-pPs_s285QmI"><a href="#" onclick="loadMusic('pPs_s285QmI', this); return false;">いちについて - On Your Marks - </a></li>
        <li id="song-gm5yfg0vve8"><a href="#" onclick="loadMusic('gm5yfg0vve8', this); return false;">ナウなヤングにバカウケするのは当たり前だのクラッ歌 - </a></li>
        <li id="song-dcVismL0cPI"><a href="#" onclick="loadMusic('dcVismL0cPI', this); return false;">マシマロ - Marshmallow - </a></li>
        <li id="song-gg-ucletSio"><a href="#" onclick="loadMusic('gg-ucletSio', this); return false;">駅前喫茶ポプラ - Coffee Shop Poplar - </a></li>
        <li id="song-lMJBJ_HEIE0"><a href="#" onclick="loadMusic('lMJBJ_HEIE0', this); return false;">ハッピー - Happy - </a></li>
        <li id="song-pedGP_x2M4k"><a href="#" onclick="loadMusic('pedGP_x2M4k', this); return false;">MIO - </a></li>
        <li id="song-3VuMRjoGyms"><a href="#" onclick="loadMusic('3VuMRjoGyms', this); return false;">恋する惑星「アナタ」 - </a></li>
        <li id="song-aGzHSJ_MXYA"><a href="#" onclick="loadMusic('aGzHSJ_MXYA', this); return false;">若者のすべて - Wakamonono Subete - </a></li>
        <li id="song-WEYKF44MWNU"><a href="#" onclick="loadMusic('WEYKF44MWNU', this); return false;">恋風 - </a></li>
        <li id="song-bGQQfNgtlz8"><a href="#" onclick="loadMusic('bGQQfNgtlz8', this); return false;">不可幸力 - </a></li>
        <li id="song-xvuAIf8sM_c"><a href="#" onclick="loadMusic('xvuAIf8sM_c', this); return false;">明日晴れるかな - </a></li>
        <li id="song-_ZFozPfJXeg"><a href="#" onclick="loadMusic('_ZFozPfJXeg', this); return false;">○○ちゃん - </a></li>
        <li id="song-KoNBf6IT0k0"><a href="#" onclick="loadMusic('KoNBf6IT0k0', this); return false;">아이묭(あいみょん) - 마리골드(マリーゴールド) COVER [MIRO&INE 미로아이네] - </a></li>
        <li id="song-BY2kSjIV8mY"><a href="#" onclick="loadMusic('BY2kSjIV8mY', this); return false;">マリーゴールド (あいみょん) ／ダズビー COVER - </a></li>
        <li id="song-PPNoc_rhMac"><a href="#" onclick="loadMusic('PPNoc_rhMac', this); return false;">Marigold Covered by GAEUL - </a></li>
        <li id="song-XZzdy6Xqo60"><a href="#" onclick="loadMusic('XZzdy6Xqo60', this); return false;">マリーゴールド (Instrumental) - Marigold (Instrumental) - </a></li>
        <li id="song-hqB9_UgzJ5A"><a href="#" onclick="loadMusic('hqB9_UgzJ5A', this); return false;">Marigold (feat. Guriri) - </a></li>
        <li id="song-EhNYTA2QTJY"><a href="#" onclick="loadMusic('EhNYTA2QTJY', this); return false;">아카네 리제-マリーゴールド(Marigold/마리골드) Cover. - </a></li>
        <li id="song-0xSiBpUdW4E"><a href="#" onclick="loadMusic('0xSiBpUdW4E', this); return false;">マリーゴールド - Marigold - </a></li>
    </ul>
</div>

<div id="pip-player">
    <div class="pip-header" id="pip-header">
        <span class="drag-handle-btn">여기잡고 드래그</span>
        <span>YouTube Music Player        크기조절가능</span>
    </div>
    <div class="pip-content">
        <iframe id="youtube-iframe" allow="autoplay; encrypted-media" allowfullscreen></iframe>
    </div>
    <div class="resizable-handle"></div>
</div>

<script>
    // --- 현재 재생 곡 스타일링 관련 ---
    let currentPlayingLink = null;

    function loadMusic(videoId, clickedElement) {
        const iframe = document.getElementById('youtube-iframe');
        iframe.src = `https://www.youtube.com/embed/${videoId}?autoplay=1`;

        if (currentPlayingLink) {
            currentPlayingLink.classList.remove('playing');
        }
        if (clickedElement) {
            clickedElement.classList.add('playing');
            currentPlayingLink = clickedElement;
        }
    }

    // --- PIP 플레이어 이동 및 크기 조절 로직 ---
    const player = document.getElementById('pip-player');
    const header = document.getElementById('pip-header');
    const resizer = player.querySelector('.resizable-handle');

    // --- 플레이어 이동(Drag) 로직 ---
    let isDragging = false;
    let offsetX, offsetY;

    header.addEventListener('mousedown', (e) => {
        isDragging = true;
        offsetX = e.clientX - player.offsetLeft;
        offsetY = e.clientY - player.offsetTop;
        player.style.transition = 'none'; // 드래그 중에는 부드러운 움직임 효과 제거
    });

    // --- 플레이어 크기 조절(Resize) 로직 ---
    let isResizing = false;
    let originalWidth, originalHeight, originalMouseX, originalMouseY;

    resizer.addEventListener('mousedown', (e) => {
        e.preventDefault();
        isResizing = true;
        originalWidth = player.offsetWidth;
        originalHeight = player.offsetHeight;
        originalMouseX = e.clientX;
        originalMouseY = e.clientY;
        player.style.transition = 'none';
    });

    // --- 마우스 움직임 및 떼는 이벤트는 document 전체에 적용 ---
    document.addEventListener('mousemove', (e) => {
        if (isDragging) {
            player.style.left = `${e.clientX - offsetX}px`;
            player.style.top = `${e.clientY - offsetY}px`;
        }
        if (isResizing) {
            const width = originalWidth + (e.clientX - originalMouseX);
            const height = originalHeight + (e.clientY - originalMouseY);
            // 최소 크기 제한
            if (width > 300) player.style.width = `${width}px`;
            if (height > 150) player.style.height = `${height}px`;
        }
    });

    document.addEventListener('mouseup', () => {
        isDragging = false;
        isResizing = false;
        player.style.transition = ''; // 드래그 끝나면 효과 복원
    });


    // --- 페이지 로드 시 첫 곡 재생 ---
    document.addEventListener('DOMContentLoaded', function() {
        // 첫번째 li의 a 태그를 찾아서 loadMusic 함수에 전달
        const firstSongLink = document.querySelector('#music-list li a');
        if(firstSongLink){
            const videoId = firstSongLink.getAttribute('onclick').match(/'([^']+)'/)[1];
            loadMusic(videoId, firstSongLink);
        }
    });
</script>