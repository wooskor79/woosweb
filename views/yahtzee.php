<?php
$user = current_user();
?>
<div class="panel">
  <div class="panel-header">
    <h2>야찌 (Yahtzee) — Player vs AI</h2>
  </div>
  
  <div class="panel-body yahtzee-game" id="yahtzee-container" data-username="<?= h($user['username']) ?>">

    <div class="sticky-header">
      <section class="game-section">
        <div class="top-bar">
          <div class="turn-indicator">
            <span id="easterEggTrigger">현재</span> 턴: <strong id="currentPlayerLabel">Player</strong>
          </div>
          <div class="rolls-left">
            남은 굴림: <strong id="rollsLeftLabel">3</strong>
          </div>
          <div class="controls">
            <label for="aiDifficulty">AI 난이도:</label>
            <select id="aiDifficulty" name="ai_difficulty" style="padding: 8px;">
              <option value="easy">쉬움</option>
              <option value="normal" selected>보통</option>
            </select>
            <button id="rollBtn" class="primary">🎲 굴리기</button>
            <button id="newGameBtn">🔄 새 게임</button>
          </div>
        </div>

        <div class="dice-board">
          <div class="dice-display-area" id="diceDisplayArea">
            <div class="pre-hint-text" id="preRollHint">
              <strong>굴리기</strong> 버튼을 눌러 게임을 시작하세요.
            </div>
            <div class="dice-row" id="diceRow"></div>
          </div>
        </div>
      </section>
    </div>

    <div class="scoreboards">
      <div class="scorecard">
        <h3><?= h($user['username']) ?> 점수판</h3>
        <table id="playerScoreTable" class="score-table"></table>
        <div class="totals">
          <div><span>상단 합계</span><span id="playerUpperSum">0</span></div>
          <div><span>상단 보너스(+35)</span><span id="playerUpperBonus">0</span></div>
          <div><span>하단 합계</span><span id="playerLowerSum">0</span></div>
          <div class="grand-total"><span>총점</span><strong id="playerGrandTotal">0</strong></div>
        </div>
      </div>

      <div class="scorecard">
        <h3>AI 점수판</h3>
        <table id="aiScoreTable" class="score-table ai"></table>
        <div class="totals">
          <div><span>상단 합계</span><span id="aiUpperSum">0</span></div>
          <div><span>상단 보너스(+35)</span><span id="aiUpperBonus">0</span></div>
          <div><span>하단 합계</span><span id="aiLowerSum">0</span></div>
          <div class="grand-total"><span>총점</span><strong id="aiGrandTotal">0</strong></div>
        </div>
      </div>
    </div>
    
    <section class="results-section">
      <h3>최근 결과</h3>
      <p class="small">결과는 DB에 저장됩니다.</p>
      <table class="table leaderboard-table" id="resultsTable">
        <thead>
          <tr>
            <th>일시</th><th>플레이어</th><th>AI</th><th>결과</th><th>소요(s)</th>
          </tr>
        </thead>
        <tbody id="resultsTbody"></tbody>
      </table>
    </section>

  </div>
</div>

<div id="modalOverlay" class="yahtzee-modal-overlay hidden">
  <div class="modal">
    <h3 id="modalTitle">알림</h3>
    <p id="modalBody" style="margin: 12px 0;"></p>
    <div id="endGameActions" class="modal-actions hidden">
      <button id="modalNewGameBtn">새 게임</button>
      <button id="modalCloseBtn">닫기</button>
    </div>
    <div id="confirmActions" class="modal-actions hidden">
      <button id="modalCancelBtn">취소</button>
      <button id="modalConfirmBtn">확인</button>
    </div>
  </div>
</div>