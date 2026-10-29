document.querySelectorAll('form[data-confirm]').forEach(function (form) {
  form.addEventListener('submit', function (event) {
    if (!window.confirm(form.dataset.confirm)) {
      event.preventDefault();
    }
  });
});

document.querySelectorAll('[data-autosubmit]').forEach(function (field) {
  field.addEventListener('change', function () {
    field.form.submit();
  });
});

var photoInput = document.getElementById('photo');
if (photoInput) {
  photoInput.addEventListener('change', function () {
    var file = photoInput.files[0];
    var box = document.getElementById('photo-preview');
    if (!file || !box) { return; }
    var img = box.querySelector('img') || box.appendChild(document.createElement('img'));
    box.querySelectorAll('svg').forEach(function (svg) { svg.remove(); });
    img.src = URL.createObjectURL(file);
  });
}

var strengthInput = document.querySelector('[data-strength]');
if (strengthInput) {
  var bars = document.getElementById(strengthInput.dataset.strength).querySelectorAll('i');
  strengthInput.addEventListener('input', function () {
    var v = strengthInput.value;
    var score = 0;
    if (v.length >= 8) { score++; }
    if (/[a-z]/.test(v) && /[A-Z]/.test(v)) { score++; }
    if (/\d/.test(v)) { score++; }
    if (/[^A-Za-z0-9]/.test(v) || v.length >= 12) { score++; }
    if (v === '') { score = 0; }
    bars.forEach(function (bar, i) { bar.classList.toggle('on', i < score); });
  });
}
