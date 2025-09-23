<style>
    .music-page-container {
        padding: 24px; box-sizing: border-box; height: 100vh;
        display: flex; flex-direction: column;
    }
    .music-page-container h2 { margin-bottom: 16px; flex-shrink: 0; }
    .music-list {
        list-style: none; padding-left: 0; margin: 0;
        flex: 1; overflow-y: auto;
    }
    .music-list a {
        display: block; padding: 8px 12px; border-radius: 6px;
        text-decoration: none; color: var(--ink);
    }
    .music-list a:hover { background-color: #f0f7ff; }
    .music-list a.playing {
        background-color: var(--primary); color: white; font-weight: bold;
    }

    #pip-player {
        position: absolute; z-index: 1000;
        background-color: var(--panel);
        border: 1px solid var(--border); border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        display: flex; flex-direction: column;
        width: 450px;
        height: 253px;
        top: 700px;
        left: 20px;
        transition: width 0.3s ease, height 0.3s ease;
    }
    .pip-header {
        display: flex; align-items: center; gap: 10px;
        padding: 8px 10px; cursor: move;
        background-color: #f6fafe; border-bottom: 1px solid var(--border);
        color: var(--ink); font-size: 14px; font-weight: bold;
        border-top-left-radius: 12px; border-top-right-radius: 12px;
    }
    .drag-handle-btn {
        background-color: #e53935; color: white;
        font-size: 12px; padding: 4px 8px; border-radius: 6px;
        user-select: none; 
    }
    .header-title { flex-grow: 1; }
    .size-controls button {
        background-color: #e0e0e0; color: #333;
        border: 1px solid #ccc; font-size: 12px;
        padding: 3px 7px; border-radius: 6px; cursor: pointer;
    }
    .size-controls button:hover { background-color: #d0d0d0; }
    .pip-content { flex: 1; background: #000; }
    #youtube-iframe { width: 100%; height: 100%; border: none; }
    .resizable-handle {
        position: absolute; width: 20px; height: 20px;
        right: 0; bottom: 0; cursor: se-resize;
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
    </ul>
</div>

<div id="pip-player">
    <div class="pip-header" id="pip-header">
        <span class="drag-handle-btn">여기잡고 드래그</span>
        <span class="header-title">YouTube Music Player</span>
        <div class="size-controls">
            <button id="size-half">x0.5</button>
            <button id="size-full">x1</button>
            <button id="size-pc">PC</button>
        </div>
    </div>
    <div class="pip-content">
        <iframe id="youtube-iframe" allow="autoplay; encrypted-media" allowfullscreen></iframe>
    </div>
    <div class="resizable-handle"></div>
</div>

<script>
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

    const player = document.getElementById('pip-player');
    const header = document.getElementById('pip-header');
    const resizer = player.querySelector('.resizable-handle');
    const sizeControls = player.querySelector('.size-controls');
    
    const originalPlayerSize = { width: 450, height: 253 };

    let isDragging = false, isResizing = false;
    let offsetX, offsetY, originalWidth, originalHeight, originalMouseX, originalMouseY;

    const startDrag = (e) => {
        isDragging = true;
        const clientX = e.clientX ?? e.touches[0].clientX;
        const clientY = e.clientY ?? e.touches[0].clientY;
        offsetX = clientX - player.offsetLeft;
        offsetY = clientY - player.offsetTop;
        player.style.transition = 'none';
        e.preventDefault();
    };
    const startResize = (e) => {
        isResizing = true;
        const clientX = e.clientX ?? e.touches[0].clientX;
        const clientY = e.clientY ?? e.touches[0].clientY;
        originalWidth = player.offsetWidth;
        originalHeight = player.offsetHeight;
        originalMouseX = clientX;
        originalMouseY = clientY;
        player.style.transition = 'none';
        e.preventDefault();
    };
    const move = (e) => {
        if (!isDragging && !isResizing) return;
        const clientX = e.clientX ?? e.touches[0].clientX;
        const clientY = e.clientY ?? e.touches[0].clientY;
        if (isDragging) {
            player.style.left = `${clientX - offsetX}px`;
            player.style.top = `${clientY - offsetY}px`;
        }
        if (isResizing) {
            const width = originalWidth + (clientX - originalMouseX);
            const height = originalHeight + (clientY - originalMouseY);
            if (width > 300) player.style.width = `${width}px`;
            if (height > 150) player.style.height = `${height}px`;
        }
    };
    const end = () => {
        isDragging = false;
        isResizing = false;
        player.style.transition = '';
    };

    header.addEventListener('mousedown', startDrag);
    header.addEventListener('touchstart', startDrag, { passive: false });
    resizer.addEventListener('mousedown', startResize);
    resizer.addEventListener('touchstart', startResize, { passive: false });
    document.addEventListener('mousemove', move);
    document.addEventListener('touchmove', move, { passive: false });
    document.addEventListener('mouseup', end);
    document.addEventListener('touchend', end);

    sizeControls.addEventListener('mousedown', (e) => e.stopPropagation());
    sizeControls.addEventListener('touchstart', (e) => e.stopPropagation());

    document.getElementById('size-half').addEventListener('click', () => {
        player.style.width = `${originalPlayerSize.width * 0.5}px`;
        player.style.height = `${originalPlayerSize.height * 0.5}px`;
    });
    document.getElementById('size-full').addEventListener('click', () => {
        player.style.width = `${originalPlayerSize.width}px`;
        player.style.height = `${originalPlayerSize.height}px`;
    });
    document.getElementById('size-pc').addEventListener('click', () => {
        player.style.width = `${originalPlayerSize.width * 2}px`;
        player.style.height = `${originalPlayerSize.height * 2}px`;
    });

    document.addEventListener('DOMContentLoaded', function() {
        const firstSongLink = document.querySelector('#music-list li a');
        if(firstSongLink){
            const videoId = firstSongLink.getAttribute('onclick').match(/'([^']+)'/)[1];
            loadMusic(videoId, firstSongLink);
        }
    });
</script>