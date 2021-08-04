// ----------------------------------------------------------------------
// Used as a callback function for CAPTCHA.
// Disables button if CAPTCHA expires.
// ----------------------------------------------------------------------
function disableBtn() {
  var btn = document.getElementById('submit-btn');
  btn.setAttribute('disable', '');
  btn.classList.add('disable');
}
// ----------------------------------------------------------------------
// Used as a callback function for CAPTCHA.
// Enables button after CAPTCHA is completed.
// ----------------------------------------------------------------------
function enableBtn() {
  var btn = document.getElementById('submit-btn');
  btn.removeAttribute('disabled');
  btn.classList.remove('disable');
}
