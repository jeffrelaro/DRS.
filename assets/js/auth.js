document.querySelectorAll('.toggle-pw').forEach(function (btn) {
  btn.addEventListener('click', function () {
    var input = btn.parentElement.querySelector('input');
    var show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    btn.textContent = show ? 'Hide' : 'Show';
  });
});
(function () {
  var input = document.getElementById('password');
  var meter = document.getElementById('meter');
  var label = document.getElementById('meterLabel');
  if (!input || !meter || !label) return;

  var names = ['', 'Weak', 'Fair', 'Good', 'Strong'];
  input.addEventListener('input', function () {
    var pw = input.value, s = 0;
    if (pw.length >= 8) s++;
    if (/[A-Z]/.test(pw) && /[a-z]/.test(pw)) s++;
    if (/\d/.test(pw)) s++;
    if (/[^A-Za-z0-9]/.test(pw)) s++;
    if (pw.length) s = Math.max(s, 1); else s = 0;
    meter.dataset.s = s;
    label.textContent = s ? 'Password strength: ' + names[s] : '';
  });
})();
