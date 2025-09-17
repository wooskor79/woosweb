/**
 * app.tasks.js
 * '할일' 페이지 전용 스크립트
 */

/**
 * 할일 완료 상태를 AJAX로 토글합니다.
 * @param {HTMLInputElement} checkbox - 클릭된 체크박스 요소
 */
async function toggleTaskBox(checkbox) {
  const form = checkbox.closest('form');
  const fd = new FormData(form);

  checkbox.disabled = true;

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
    if (data && data.ok) {
      const item = form.closest('.todo-item');
      if (checkbox.checked) item.classList.add('completed');
      else item.classList.remove('completed');
      return;
    }
    throw new Error('server');
  } catch (e) {
    form.submit(); // 실패 시 폴백
  } finally {
    checkbox.disabled = false;
  }
}

/**
 * 할일 삭제 확인 UI를 보여줍니다.
 * @param {HTMLElement} btn - 클릭된 '삭제' 버튼
 */
function showTaskDeleteConfirm(btn) {
  const box = btn.closest('.delete-box');
  if (!box) return;
  const delBtn = box.querySelector('.delete-btn') || btn;
  delBtn.classList.add('hidden');
  const c = box.querySelector('.confirm');
  if (c) c.classList.remove('hidden');
}

/**
 * 할일 삭제 확인을 취소합니다.
 * @param {HTMLElement} el - '아니오' 버튼
 */
function cancelTaskDeleteConfirm(el) {
  const box = el.closest('.delete-box');
  if (!box) return;
  const c = box.querySelector('.confirm');
  if (c) c.classList.add('hidden');
  const delBtn = box.querySelector('.delete-btn');
  if (delBtn) delBtn.classList.remove('hidden');
}