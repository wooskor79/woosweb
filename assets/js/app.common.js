/**
 * app.common.js
 * 모든 페이지에서 공통으로 사용되는 유틸리티 함수들
 */

/**
 * 입력된 전화번호에 자동으로 하이픈(-)을 추가합니다.
 * @param {HTMLInputElement} el - 전화번호를 입력하는 input 요소
 */
function autoHyphenPhone(el) {
  let v = el.value.replace(/\D/g, '').slice(0, 11);
  if (v.length < 4) el.value = v;
  else if (v.length < 8) el.value = v.replace(/(\d{3})(\d+)/, '$1-$2');
  else el.value = v.replace(/(\d{3})(\d{4})(\d{0,4}).*/, '$1-$2-$3');
}