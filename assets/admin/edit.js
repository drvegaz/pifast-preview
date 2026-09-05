(function () {
  var body = document.body;
  if (!body.classList.contains('admin-mode')) return;

  var csrfToken = body.dataset.csrf;

  function apiFetch(url, options) {
    options = options || {};
    options.headers = Object.assign({ 'X-CSRF-Token': csrfToken }, options.headers || {});
    return fetch(url, options).then(function (res) {
      return res.json().then(function (data) {
        if (!res.ok || !data.ok) {
          throw new Error(data.error || 'Något gick fel.');
        }
        return data;
      });
    });
  }

  function toast(message) {
    var el = document.createElement('div');
    el.className = 'pf-toast';
    el.textContent = message;
    document.body.appendChild(el);
    requestAnimationFrame(function () {
      el.classList.add('visible');
    });
    setTimeout(function () {
      el.classList.remove('visible');
      setTimeout(function () {
        el.remove();
      }, 300);
    }, 2200);
  }

  function attachPencil(el, key) {
    var pencil = document.createElement('button');
    pencil.type = 'button';
    pencil.className = 'pf-pencil';
    pencil.setAttribute('aria-label', 'Redigera text');
    pencil.textContent = '✎';
    pencil.addEventListener('click', function (e) {
      e.stopPropagation();
      e.preventDefault();
      openEditor(key);
    });
    el.appendChild(pencil);
  }

  function renderTextInto(el, value, multiline) {
    var pencil = el.querySelector('.pf-pencil');
    el.textContent = '';
    if (multiline) {
      var lines = value.split('\n');
      lines.forEach(function (line, i) {
        if (i > 0) el.appendChild(document.createElement('br'));
        el.appendChild(document.createTextNode(line));
      });
    } else {
      el.appendChild(document.createTextNode(value));
    }
    el.setAttribute('data-raw-value', value);
    if (pencil) {
      el.appendChild(pencil);
    } else {
      attachPencil(el, el.getAttribute('data-edit-key'));
    }
  }

  document.querySelectorAll('[data-edit-key]').forEach(function (el) {
    attachPencil(el, el.getAttribute('data-edit-key'));
  });

  function openEditor(key) {
    var instances = Array.prototype.slice.call(
      document.querySelectorAll('[data-edit-key="' + key + '"]')
    );
    if (!instances.length) return;
    var first = instances[0];
    var current = first.getAttribute('data-raw-value') || '';
    var multiline = first.hasAttribute('data-multiline');

    var overlay = document.createElement('div');
    overlay.className = 'pf-modal-overlay';
    var modal = document.createElement('div');
    modal.className = 'pf-modal';
    var label = document.createElement('label');
    label.textContent = 'Redigera text';
    var input = document.createElement(multiline ? 'textarea' : 'input');
    if (!multiline) input.type = 'text';
    input.value = current;
    input.className = 'pf-modal-input';
    var actions = document.createElement('div');
    actions.className = 'pf-modal-actions';
    var saveBtn = document.createElement('button');
    saveBtn.type = 'button';
    saveBtn.textContent = 'Spara';
    saveBtn.className = 'pf-btn pf-btn-primary';
    var cancelBtn = document.createElement('button');
    cancelBtn.type = 'button';
    cancelBtn.textContent = 'Avbryt';
    cancelBtn.className = 'pf-btn';

    actions.appendChild(saveBtn);
    actions.appendChild(cancelBtn);
    modal.appendChild(label);
    modal.appendChild(input);
    modal.appendChild(actions);
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
    input.focus();
    input.select();

    function close() {
      document.removeEventListener('keydown', onKeydown);
      overlay.remove();
    }

    function onKeydown(e) {
      if (e.key === 'Escape') close();
      if (e.key === 'Enter' && !multiline) {
        e.preventDefault();
        save();
      }
    }

    document.addEventListener('keydown', onKeydown);
    cancelBtn.addEventListener('click', close);
    overlay.addEventListener('click', function (e) {
      if (e.target === overlay) close();
    });

    function save() {
      var value = input.value;
      saveBtn.disabled = true;
      apiFetch('/api/save.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ key: key, value: value }),
      })
        .then(function (data) {
          instances.forEach(function (el) {
            renderTextInto(el, data.value, multiline);
          });
          toast('Sparat ✓');
          close();
        })
        .catch(function (err) {
          alert(err.message);
          saveBtn.disabled = false;
        });
    }

    saveBtn.addEventListener('click', save);
  }

  document.querySelectorAll('[data-edit-image]').forEach(function (el) {
    var key = el.getAttribute('data-edit-image');
    var cssVar = '--' + key.replace(/\./g, '-');
    var overlay = document.createElement('div');
    overlay.className = 'pf-image-overlay';
    var btn = document.createElement('button');
    btn.type = 'button';
    btn.textContent = 'Byt bild';
    btn.className = 'pf-btn pf-btn-primary';
    var fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.accept = 'image/jpeg,image/png,image/webp';
    fileInput.style.display = 'none';

    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      fileInput.click();
    });

    fileInput.addEventListener('change', function () {
      var file = fileInput.files[0];
      if (!file) return;
      var formData = new FormData();
      formData.append('key', key);
      formData.append('file', file);
      btn.disabled = true;
      btn.textContent = 'Laddar upp...';
      apiFetch('/api/upload.php', { method: 'POST', body: formData })
        .then(function (data) {
          var img = el.matches('img') ? el : el.querySelector('img');
          if (img) {
            img.src = data.url;
          } else {
            el.style.setProperty(cssVar, "url('" + data.url + "')");
          }
          toast('Bild uppdaterad ✓');
        })
        .catch(function (err) {
          alert(err.message);
        })
        .finally(function () {
          btn.disabled = false;
          btn.textContent = 'Byt bild';
          fileInput.value = '';
        });
    });

    overlay.appendChild(btn);
    overlay.appendChild(fileInput);
    el.appendChild(overlay);
  });

  var toggleBtn = document.createElement('button');
  toggleBtn.type = 'button';
  toggleBtn.className = 'pf-edit-toggle';
  toggleBtn.textContent = 'Redigera sidan';
  document.body.appendChild(toggleBtn);

  var editing = false;
  toggleBtn.addEventListener('click', function () {
    editing = !editing;
    body.classList.toggle('pf-editing', editing);
    toggleBtn.textContent = editing ? 'Avsluta redigering' : 'Redigera sidan';
  });

  var logoutLink = document.createElement('a');
  logoutLink.href = '/admin/logout.php';
  logoutLink.className = 'pf-logout';
  logoutLink.textContent = 'Logga ut';
  document.body.appendChild(logoutLink);
})();
