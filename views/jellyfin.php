<html>
<head>
    <meta charset="UTF-8">
    <title>영화 보기 안내</title>
    <script>
        function copyToClipboard(text) {
            // 임시 textarea 엘리먼트 생성
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);

            // textarea의 내용을 선택하고 복사 명령 실행
            textarea.select();
            document.execCommand('copy');

            // 임시 textarea 엘리먼트 제거
            document.body.removeChild(textarea);

            // 복사 완료 알림
            alert(text + ' 주소가 복사되었습니다.');
        }
    </script>
</head>
<body>

    <div style="font-size: 22px;">
        <p>
            영화 보실 분은 <a href="http://media.wooskor.site" target="_blank">http://media.wooskor.site</a> 가세요
            <a href="http://media.wooskor.site" target="_blank" style="display: inline-block; background-color: #007bff; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px; font-size: 16px; vertical-align: middle; margin-left: 10px;">링크</a>
        </p>
        <p>
            젤리핀 아이디는 본인 아이디 비밀번호는 1234 입니다
        </p>
        <p>
            위 사이트나 젤리핀 앱 에서 실행 가능합니다
        </p>
        <p>
            젤리핀에서 서버 주소는 media.wooskor.site
            <button onclick="copyToClipboard('media.wooskor.site')" style="background-color: #28a745; color: white; padding: 5px 10px; text-decoration: none; border-radius: 5px; font-size: 14px; vertical-align: middle; margin-left: 10px; border: none; cursor: pointer;">주소 복사</button>
        </p>
        <p>
            플레이 스토어 <a href="https://play.google.com/store/search?q=jellyfin&c=apps" target="_blank">https://play.google.com/store/search?q=jellyfin&c=apps</a> 다운
        </p>
        <p>
            앱스토어 <a href="https://apps.apple.com/kr/app/jellyfin-mobile/id1480192618" target="_blank">https://apps.apple.com/kr/app/jellyfin-mobile/id1480192618</a> 다운
        </p>
    </div>

</body>
</html>