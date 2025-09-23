/**
 * app.yahtzee.js
 * 'Yahtzee' 게임 페이지 전용 스크립트
 */
if (document.getElementById('rollBtn')) {
  
  const BACKEND_ENABLED = true; 
  const API_BASE = 'controllers/yahtzee_api.php';

  const gameContainer = document.getElementById('yahtzee-container');
  const LOGGED_IN_USERNAME = gameContainer ? gameContainer.dataset.username : 'Player';

  const CATEGORIES = ['ones','twos','threes','fours','fives','sixes','threeKind','fourKind','fullHouse','smallStraight','largeStraight','yahtzee','chance'];
  const CATEGORY_LABELS = {ones:'Ones (1)',twos:'Twos (2)',threes:'Threes (3)',fours:'Fours (4)',fives:'Fives (5)',sixes:'Sixes (6)',threeKind:'Three of a Kind',fourKind:'Four of a Kind',fullHouse:'Full House (25)',smallStraight:'Small Straight (30)',largeStraight:'Large Straight (40)',yahtzee:'Yahtzee (50)',chance:'Chance'};
  const UPPER = ['ones','twos','threes','fours','fives','sixes'];
  const LOWER = ['threeKind','fourKind','fullHouse','smallStraight','largeStraight','yahtzee','chance'];
  const DICE_SYMBOLS = ['⚀', '⚁', '⚂', '⚃', '⚄', '⚅'];
  const MAX_ROLLS = 3;
  const DICE_COUNT = 5;

  let state = initState();
  let gameStartTs = Date.now();
  let isProcessing = false;
  let aiDifficulty = 'normal';

  function initState(){
    const scoreObject = {};
    CATEGORIES.forEach(c => { scoreObject[c] = null; });

    return {
      currentPlayer:'player',
      dice:[1,1,1,1,1],
      held:[false,false,false,false,false],
      rollsLeft:MAX_ROLLS,
      round:1,
      score: {
        player: JSON.parse(JSON.stringify(scoreObject)),
        ai: JSON.parse(JSON.stringify(scoreObject)),
      },
      lastChoice: { who: null, category: null }
    };
  }
  
  function $(sel){return document.querySelector(sel)}
  function $all(sel){return document.querySelectorAll(sel)}
  const sleep = ms => new Promise(res => setTimeout(res, ms));
  
  function renderAll(){
      $('#currentPlayerLabel').textContent = state.currentPlayer === 'player' ? LOGGED_IN_USERNAME : 'AI';
      $('#rollsLeftLabel').textContent = state.rollsLeft;
      renderDice();
      renderScoreTables();
  }
  
  function renderDice() {
      const displayArea = $('#diceDisplayArea');
      const rollBtn = $('#rollBtn');
      const diceRow = $('#diceRow');
      diceRow.innerHTML = '';

      const isPlayer = state.currentPlayer === 'player';
      const hasRolled = state.rollsLeft < MAX_ROLLS;
      const canRoll = (isPlayer && state.rollsLeft > 0 && !isProcessing);
      
      if (isPlayer) {
          rollBtn.disabled = !canRoll;
          rollBtn.classList.remove('ai-turn');
          rollBtn.innerHTML = '🎲 굴리기';
      } else {
          rollBtn.disabled = true;
          rollBtn.classList.add('ai-turn');
          rollBtn.innerHTML = '<span style="font-size: 1.1em;">❌</span> AI 턴 진행중';
      }

      if (!hasRolled && isPlayer) {
          displayArea.classList.remove('has-dice');
      } else {
          displayArea.classList.add('has-dice');
          state.dice.forEach((v, idx) => {
              const die = document.createElement('div');
              die.className = 'die';
              die.dataset.index = idx;
              if (state.held[idx]) die.classList.add('held');
              die.classList.add(`face-${v}`);
      
              const pips = document.createElement('div');
              pips.className = 'pips';
              for (let i = 1; i <= v; i++) {
                  const pip = document.createElement('span');
                  pip.className = `pip p${i}`;
                  pips.appendChild(pip);
              }
              die.appendChild(pips);
      
              if (isPlayer && hasRolled && state.rollsLeft > 0 && !isProcessing) {
                  die.addEventListener('click', () => {
                      state.held[idx] = !state.held[idx];
                      renderDice();
                  });
              }
              diceRow.appendChild(die);
          });
      }
  }
  
  function renderScoreTables() {
      renderScoreTableFor('player', '#playerScoreTable');
      renderScoreTableFor('ai', '#aiScoreTable');
      updateTotals('player', '#playerUpperSum', '#playerUpperBonus', '#playerLowerSum', '#playerGrandTotal');
      updateTotals('ai', '#aiUpperSum', '#aiUpperBonus', '#aiLowerSum', '#aiGrandTotal');
  }
  
  function renderScoreTableFor(who, tableSel) {
      const tbl = $(tableSel);
      if (!tbl) return;
      
      const isPlayer = (who === 'player');
      const playerHasRolled = (state.rollsLeft < MAX_ROLLS);
  
      tbl.innerHTML = ''; 
  
      const allCats = [...UPPER, null, ...LOWER];
  
      allCats.forEach(key => {
          if (key === null) {
              const sep = document.createElement('tr');
              const td = document.createElement('td');
              td.colSpan = 2;
              td.innerHTML = '<hr style="border:0; border-top:1px solid #e5e7eb; margin: 4px 0;">';
              sep.appendChild(td);
              tbl.appendChild(sep);
              return;
          }
  
          const tr = document.createElement('tr');
          tr.dataset.category = key;
          
          const nameTd = document.createElement('td');
          const scoreTd = document.createElement('td');
          scoreTd.className = 'score';
  
          const scoreData = state.score[who][key];
          
          if (scoreData !== null) {
              tr.classList.add('used');
              nameTd.innerHTML = `
                  <span>${CATEGORY_LABELS[key]}</span>
                  <div class="score-dice">
                      ${scoreData.dice.map(d => `<span class="sd">${DICE_SYMBOLS[d-1]}</span>`).join('')}
                  </div>
              `;
              scoreTd.textContent = scoreData.score;
          } else {
              nameTd.textContent = CATEGORY_LABELS[key];
              if (isPlayer && playerHasRolled && state.currentPlayer === 'player') {
                  scoreTd.textContent = scoreCategory(key, state.dice);
              } else {
                  scoreTd.textContent = '-';
              }
          }
  
          tr.appendChild(nameTd);
          tr.appendChild(scoreTd);
  
          if (isPlayer && state.currentPlayer === 'player' && scoreData === null && playerHasRolled && !isProcessing) {
              tr.classList.add('clickable');
              tr.onclick = () => {
                  confirmCategoryChoice('player', key);
              };
          }

          if (state.lastChoice.who === who && state.lastChoice.category === key) {
              tr.classList.add('last-choice');
          }

          tbl.appendChild(tr);
      });
  }
  
  function updateTotals(who, upperSel, bonusSel, lowerSel, totalSel) {
      const s = state.score[who];
      const upperSum = UPPER.reduce((acc, k) => acc + (s[k]?.score ?? 0), 0);
      const upperBonus = upperSum >= 63 ? 35 : 0;
      const lowerSum = LOWER.reduce((acc, k) => acc + (s[k]?.score ?? 0), 0);
      const total = upperSum + upperBonus + lowerSum;
  
      $(upperSel).textContent = upperSum;
      $(bonusSel).textContent = upperBonus;
      $(lowerSel).textContent = lowerSum;
      $(totalSel).textContent = total;
  }

  function randomDie(){ return 1 + Math.floor(Math.random() * 6); }
  
  function rollDice(){
    for(let i = 0; i < DICE_COUNT; i++){
      if(!state.held[i]){
        state.dice[i] = randomDie();
      }
    }
  }

  async function handleRoll(){
    if(state.currentPlayer !== 'player' || state.rollsLeft <= 0 || isProcessing) return;
    
    isProcessing = true;
    $('#rollBtn').disabled = true;

    if(state.rollsLeft === MAX_ROLLS){
      state.held = [false,false,false,false,false];
    }

    $all('#diceRow .die:not(.held)').forEach(d => d.classList.add('rolling'));
    
    await sleep(500);

    rollDice();
    state.rollsLeft--;
    isProcessing = false;
    renderAll();
  }

  // ✅ [추가] 이스터에그 기능 함수
  async function easterEggRoll() {
    // 굴림 횟수가 남아있고, 첫 굴림 이후에만 작동
    if (state.currentPlayer !== 'player' || state.rollsLeft >= MAX_ROLLS || state.rollsLeft <= 0 || isProcessing) {
      return;
    }
    
    isProcessing = true;
    $('#rollBtn').disabled = true;

    $all('#diceRow .die:not(.held)').forEach(d => d.classList.add('rolling'));
    await sleep(500);

    rollDice();
    
    // 남은 굴림 횟수는 차감하지 않음!
    
    isProcessing = false;
    renderAll();
  }

  function confirmCategoryChoice(who, category){
    if(isProcessing) return;
    const points = scoreCategory(category, state.dice);
    
    showModal('confirm',
      '점수 확인',
      `<strong>${CATEGORY_LABELS[category]}</strong>에 <strong>${points}</strong>점을 기록하시겠습니까?`,
      () => chooseCategory(who, category)
    );
  }

  async function chooseCategory(who, category){
    if(state.score[who][category] !== null) return;

    state.score[who][category] = {
      score: scoreCategory(category, state.dice),
      dice: [...state.dice]
    };

    state.lastChoice = { who, category };
    
    await nextTurn();
  }

  async function nextTurn(){
    isProcessing = true;
    state.held = [false,false,false,false,false];
    state.rollsLeft = MAX_ROLLS;

    if(isGameOver()){
      setTimeout(endGame, 500);
      return;
    }
    
    if(state.currentPlayer === 'player'){
      state.currentPlayer = 'ai';
      renderAll();
      await sleep(500);
      await aiTurn();

    }else{
      state.currentPlayer = 'player';
      state.round = Object.values(state.score.player).filter(v=>v!==null).length + 1;
      isProcessing = false;
      renderAll();
    }
  }

  function isGameOver(){
    return Object.values(state.score.player).every(v=>v!==null);
  }

  function counts(dice){const c={1:0,2:0,3:0,4:0,5:0,6:0};dice.forEach(v=>c[v]++);return c}
  function sum(dice){return dice.reduce((a,b)=>a+b,0)}
  function isFullHouse(cnt){const v=Object.values(cnt);return v.includes(3)&&v.includes(2)}
  function hasStraight(dice,len){const set=Array.from(new Set(dice)).sort((a,b)=>a-b);if(set.length<len)return!1;let max=1,c=1;for(let i=1;i<set.length;i++){if(set[i]===set[i-1]+1){c++}else{c=1}max=Math.max(max,c)}return max>=len}
  function scoreCategory(cat,dice){const cnt=counts(dice);switch(cat){case'ones':return cnt[1]*1;case'twos':return cnt[2]*2;case'threes':return cnt[3]*3;case'fours':return cnt[4]*4;case'fives':return cnt[5]*5;case'sixes':return cnt[6]*6;case'threeKind':return Object.values(cnt).some(v=>v>=3)?sum(dice):0;case'fourKind':return Object.values(cnt).some(v=>v>=4)?sum(dice):0;case'fullHouse':return isFullHouse(cnt)?25:0;case'smallStraight':return hasStraight(dice,4)?30:0;case'largeStraight':return hasStraight(dice,5)?40:0;case'yahtzee':return Object.values(cnt).some(v=>v>=5)?50:0;case'chance':return sum(dice)}return 0}

  async function aiTurn(){
    isProcessing = true;
    state.rollsLeft = MAX_ROLLS;

    for(let rollNum = 0; rollNum < MAX_ROLLS; rollNum++){
      if(rollNum > 0){
        state.held = aiChooseHolds(state.dice, state.score.ai, aiDifficulty);
        renderAll();
        await sleep(1000);
        if (state.held.every(h => h === true)) break;
      }
      
      $all('#diceRow .die:not(.held)').forEach(d => d.classList.add('rolling'));
      await sleep(500);

      rollDice();
      state.rollsLeft--;
      renderAll();
      await sleep(1000);
    }

    const cat = aiChooseCategory(state.dice, state.score.ai, aiDifficulty);
    
    state.score.ai[cat] = {
      score: scoreCategory(cat, state.dice),
      dice: [...state.dice]
    };
    state.lastChoice = { who: 'ai', category: cat };

    const chosenRow = $(`#aiScoreTable tr[data-category="${cat}"]`);
    if(chosenRow){
      chosenRow.classList.add('ai-choosing');
      await sleep(1200);
    }
    
    await nextTurn();
  }

  function aiChooseHolds(dice, aiScore, difficulty) {
    if (difficulty === 'easy') {
      return dice.map(d => d >= 5);
    }
    const cnt = counts(dice);
    const entries = Object.entries(cnt).map(([k,v])=>[parseInt(k),v]).sort((a,b)=>b[1]-a[1]);
    if (entries[0][1] >= 4) return dice.map(d => d === entries[0][0]);
    if (aiScore.largeStraight === null && hasStraight(dice, 4)) return dice.map(()=>true);
    if (entries[0][1] === 3) return dice.map(d => d === entries[0][0]);
    const pairs = entries.filter(([, count]) => count === 2);
    if (pairs.length > 0) {
        const bestPairFace = Math.max(...pairs.map(([face]) => face));
        return dice.map(d => d === bestPairFace);
    }
    const upperSum = UPPER.reduce((acc,k)=>acc + (aiScore[k]?.score ?? 0),0);
    if(upperSum < 63) return dice.map(d => d >= 4);
    const maxDie = Math.max(...dice);
    return dice.map(d => d === maxDie);
  }

  function aiChooseCategory(dice, aiScore, difficulty) {
    const available = CATEGORIES.filter(c => aiScore[c]===null);
    let best = {cat:available[0], score:-1};

    for(const cat of available){
      const sc = scoreCategory(cat, dice);
      if(sc > best.score){
        best = {cat, score: sc};
      }
    }
    
    if (difficulty === 'easy') {
      if (best.score === 0) {
        const priorities = ['ones','twos','threes','fours','fives','sixes','chance','threeKind','fourKind','smallStraight','fullHouse','largeStraight','yahtzee'];
        for(const p of priorities){ if(available.includes(p)) return p; }
      }
      return best.cat;
    }

    for(const cat of available){
      const sc = scoreCategory(cat, dice);
      if(sc > best.score){
        best = {cat, score: sc};
      } else if (sc === best.score){
        const isCurrentBestRare = ['yahtzee', 'largeStraight'].includes(best.cat);
        const isNewCatRare = ['yahtzee', 'largeStraight'].includes(cat);
        if(!isCurrentBestRare && isNewCatRare) best = {cat, score: sc};
      }
    }
    if(best.score === 0){
      const priorities = ['ones','twos','threes','fours','fives','sixes','chance','threeKind','fourKind','smallStraight','fullHouse','largeStraight','yahtzee'];
      for(const p of priorities){ if(available.includes(p)) return p; }
    }
    return best.cat;
  }

  function grandTotal(who){const s=state.score[who];const upperSum=UPPER.reduce((acc,k)=>acc+(s[k]?.score??0),0);const upperBonus=upperSum>=63?35:0;const lowerSum=LOWER.reduce((acc,k)=>acc+(s[k]?.score??0),0);return upperSum+upperBonus+lowerSum}
  
  async function endGame() {
    isProcessing = true;
    $('#rollBtn').disabled = true;
    const p = grandTotal('player');
    const a = grandTotal('ai');
    
    const winner_for_db = p > a ? 'Player' : (a > p ? 'AI' : 'Draw');
    const winner_display_name = p > a ? LOGGED_IN_USERNAME : (a > p ? 'AI' : 'Draw');
    
    const durSec = Math.max(1, Math.round((Date.now() - gameStartTs) / 1000));
    
    showModal('endGame', '게임 종료', `${h(LOGGED_IN_USERNAME)}: ${p}점<br>AI: ${a}점<br><strong>${winner_display_name === 'Draw' ? '무승부' : (winner_display_name + ' 승')}</strong>`);

    const result = {
      player_score: p,
      ai_score: a,
      winner: winner_for_db,
      duration_seconds: durSec
    };

    if (BACKEND_ENABLED) {
      await saveResultAPI(result);
      await loadResults();
    } else {
      saveResultLocal(result);
      loadResults(); 
    }
  }

  function showModal(type, title, html, onConfirm = () => {}) {
    const modal = $('.yahtzee-modal-overlay .modal');
    $('#modalTitle').textContent = title;
    $('#modalBody').innerHTML = html;
    
    modal.classList.remove('modal-type-confirm', 'modal-type-endgame');
    modal.classList.add(type === 'endGame' ? 'modal-type-endgame' : 'modal-type-confirm');
    
    if (type === 'confirm') {
      const oldConfirmBtn = $('#modalConfirmBtn');
      const newConfirmBtn = oldConfirmBtn.cloneNode(true);
      oldConfirmBtn.parentNode.replaceChild(newConfirmBtn, oldConfirmBtn);
      
      newConfirmBtn.addEventListener('click', async () => {
        closeModal();
        await onConfirm();
      });
    }
  
    $('#modalOverlay').classList.remove('hidden');
  }
  function closeModal(){$('#modalOverlay').classList.add('hidden')}

  async function saveResultAPI(result) {
    try {
      const res = await fetch(API_BASE, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(result)
      });
      if (!res.ok) throw new Error('Server responded with an error');
    } catch (e) {
      console.error('Failed to save result to server:', e);
    }
  }

  async function loadResultsAPI() {
    try {
      const res = await fetch(`${API_BASE}?_=${new Date().getTime()}`);
      if (!res.ok) throw new Error('Server responded with an error');
      return await res.json();
    } catch (e) {
      console.error('Failed to load results from server:', e);
      return [];
    }
  }

  function saveResultLocal(res){ const key='yahtzee_results'; const arr=JSON.parse(localStorage.getItem(key)||'[]'); arr.unshift(res); localStorage.setItem(key, JSON.stringify(arr.slice(0,30))); }
  
  async function loadResults() {
    let results = [];
    if (BACKEND_ENABLED) {
      results = await loadResultsAPI();
    } else {
      results = JSON.parse(localStorage.getItem('yahtzee_results') || '[]');
    }
    renderResults(results);
  }

  function renderResults(rows){
    const tbody=$('#resultsTbody');
    if (!tbody) return;
    tbody.innerHTML='';
    (rows||[]).forEach(r=>{
        const tr=document.createElement('tr');
        const ts=r.ts||r.played_at;
        const winnerName = (BACKEND_ENABLED && r.winner !== 'AI' && r.winner !== 'Draw') ? h(r.winner) : r.winner;
        tr.innerHTML=`<td>${formatKST(ts)}</td><td>${r.player_score}</td><td>${r.ai_score}</td><td>${winnerName}</td><td>${r.duration_seconds}</td>`;
        tbody.appendChild(tr);
    });
  }
  function formatKST(iso){try{const d=new Date(iso);return d.toLocaleString('ko-KR',{timeZone:'Asia/Seoul',year:'numeric',month:'2-digit',day:'2-digit',hour:'2-digit',minute:'2-digit',hour12:!1}).replace(/\. /g,'-').replace(/\./,'')}catch(e){return iso}}
  function h(t){if(typeof t!=='string')return'';return t.replace(/</g,"&lt;").replace(/>/g,"&gt;")}

  function newGame() {
    state = initState();
    gameStartTs = Date.now();
    isProcessing = false;
    renderAll();
  }
  
  if($('#modalOverlay')){$('#modalOverlay').classList.add('hidden')}
  $('#rollBtn').addEventListener('click',handleRoll);
  $('#newGameBtn').addEventListener('click',newGame);
  $('#modalCloseBtn').addEventListener('click',closeModal);
  $('#modalCancelBtn').addEventListener('click',closeModal);
  $('#modalNewGameBtn').addEventListener('click',()=>{closeModal();newGame()});
  $('#aiDifficulty').addEventListener('change', (e) => {
    aiDifficulty = e.target.value;
  });
  
  // ✅ [추가] 이스터에그 이벤트 리스너
  $('#easterEggTrigger').addEventListener('click', easterEggRoll);
  
  renderAll();
  loadResults();
}