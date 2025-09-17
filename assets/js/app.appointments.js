/**
 * app.appointments.js
 * '약속' 페이지 전용 스크립트
 */

/**
 * 약속 확인 상태를 AJAX로 토글합니다.
 * @param {Event} ev - 클릭 이벤트
 * @param {HTMLFormElement} form - 전송할 form 요소
 */
async function toggleConfirm(ev, form) {
  ev.preventDefault();
  const btn = form.querySelector('.confirm-btn');
  const apptId = btn?.dataset?.apptId;
  const whoBox = document.getElementById('who-' + apptId);
  const fd = new FormData(form);

  btn.disabled = true;

  try {
    const res = await fetch(form.action, {
      method: 'POST',
      body: fd,
      credentials: 'same-origin',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    });
    if (!res.ok) throw new Error('http:' + res.status);

    const data = await res.json().catch(() => { throw new Error('json'); });
    if (!data.ok) throw new Error(data.reason || 'server');

    // 버튼 상태/텍스트 업데이트
    if (data.confirmed) {
      btn.classList.add('on');
      btn.textContent = '확인됨';
    } else {
      btn.classList.remove('on');
      btn.textContent = '미확인';
    }

    // 확인자 목록 업데이트
    if (whoBox) {
      if (data.who && data.who.length) {
        whoBox.textContent = data.who.map(n => `${n}(확인함)`).join(', ');
      } else {
        whoBox.innerHTML = '<span class="muted">확인한 사람이 없습니다.</span>';
      }
    }
  } catch (e) {
    form.submit(); // 실패 시 폴백
  } finally {
    btn.disabled = false;
  }
  return false;
}

/**
 * 약속 삭제 확인 UI를 보여줍니다.
 * @param {number|string} id - 약속 ID
 */
function showApptDeleteConfirm(id) {
  const box = document.getElementById('delbox-' + id);
  if (!box) return;
  const firstBtn = box.querySelector('.danger.small');
  if (firstBtn) firstBtn.classList.add('hidden');
  const c = box.querySelector('.confirm');
  if (c) c.classList.remove('hidden');
}

/**
 * 약속 삭제 확인을 취소하고 원래 상태로 되돌립니다.
 * @param {number|string} id - 약속 ID
 */
function cancelApptDeleteConfirm(id) {
  const box = document.getElementById('delbox-' + id);
  if (!box) return;
  const c = box.querySelector('.confirm');
  if (c) c.classList.add('hidden');
  const firstBtn = box.querySelector('.danger.small');
  if (firstBtn) firstBtn.classList.remove('hidden');
}