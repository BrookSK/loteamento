(function () {
  var projectId = window.__PROJECT_ID__;
  var appUrl = window.__APP_URL__ || '';
  var csrf = window.__CSRF__ || '';
  if (!projectId) return;

  var svgWrap = document.getElementById('svg-wrap');
  var svgRoot = document.getElementById('svg-root');
  var progressEl = document.getElementById('progress');
  var selectedPolygonEl = document.getElementById('selected-polygon');
  var lotForm = document.getElementById('lot-form');
  var saveMsg = document.getElementById('save-msg');
  var btnClear = document.getElementById('btn-clear');
  var csvForm = document.getElementById('csv-form');

  var btnZoomIn = document.getElementById('btn-zoom-in');
  var btnZoomOut = document.getElementById('btn-zoom-out');
  var btnReset = document.getElementById('btn-reset');
  var btnToggleIds = document.getElementById('btn-toggle-ids');

  var selected = null;
  var lotsByPolygon = {};
  var scale = 1;
  var showIds = false;

  function q(id) { return document.getElementById(id); }

  function setMsg(text, isError) {
    if (!saveMsg) return;
    saveMsg.textContent = text || '';
    saveMsg.style.color = isError ? '#ef4444' : '#64748b';
  }

  function normalizeNumber(v) {
    if (v === null || v === undefined) return '';
    if (typeof v === 'number') return String(v);
    return String(v);
  }

  function updateProgress() {
    var total = 0;
    var associated = 0;
    if (!svgRoot) return;

    var el = svgRoot.querySelector('svg');
    if (!el) return;
    var nodes = el.querySelectorAll('path[id], polygon[id], rect[id]');
    total = nodes.length;
    nodes.forEach(function (n) {
      var id = n.getAttribute('id');
      if (id && lotsByPolygon[id]) associated++;
    });

    if (progressEl) {
      progressEl.textContent = associated + ' de ' + total + ' associados';
    }
  }

  function applyStyle(node) {
    if (!node) return;
    node.style.transition = 'fill 0.2s ease';
    var id = node.getAttribute('id');

    if (selected && selected.getAttribute('id') === id) {
      node.style.outline = '3px solid #3b82f6';
    } else {
      node.style.outline = '';
    }

    if (id && lotsByPolygon[id]) {
      node.style.fill = '#22c55e';
    } else {
      node.style.fill = '#6b7280';
    }
  }

  function repaintAll() {
    if (!svgRoot) return;
    var el = svgRoot.querySelector('svg');
    if (!el) return;

    el.querySelectorAll('path[id], polygon[id], rect[id]').forEach(function (n) {
      applyStyle(n);
      n.style.cursor = 'pointer';

      if (showIds) {
        n.setAttribute('data-show-id', '1');
      } else {
        n.removeAttribute('data-show-id');
      }
    });
    updateProgress();
  }

  function setSelected(node) {
    selected = node;
    repaintAll();
    if (!selectedPolygonEl) return;

    var id = node ? node.getAttribute('id') : null;
    selectedPolygonEl.textContent = id || 'Nenhum';

    var lot = id ? lotsByPolygon[id] : null;
    if (!lotForm) return;

    lotForm.numero_lote.value = lot ? (lot.numero_lote || '') : '';
    lotForm.quadra.value = lot ? (lot.quadra || '') : '';
    lotForm.area_m2.value = lot ? normalizeNumber(lot.area_m2 || '') : '';
    lotForm.frente_m.value = lot ? normalizeNumber(lot.frente_m || '') : '';
    lotForm.fundo_m.value = lot ? normalizeNumber(lot.fundo_m || '') : '';
    lotForm.lateral_esq_m.value = lot ? normalizeNumber(lot.lateral_esq_m || '') : '';
    lotForm.lateral_dir_m.value = lot ? normalizeNumber(lot.lateral_dir_m || '') : '';
    lotForm.valor.value = lot ? normalizeNumber(lot.valor || '') : '';
    lotForm.status.value = lot ? (lot.status || 'disponivel') : 'disponivel';
    lotForm.observacoes.value = lot ? (lot.observacoes || '') : '';
  }

  function wireSvgClicks() {
    if (!svgRoot) return;
    var el = svgRoot.querySelector('svg');
    if (!el) return;

    el.querySelectorAll('path[id], polygon[id], rect[id]').forEach(function (n) {
      n.addEventListener('click', function (e) {
        e.preventDefault();
        setSelected(n);
      });
    });
  }

  function loadLots() {
    return fetch(appUrl + '/api/lots.php?project_id=' + encodeURIComponent(projectId), {
      credentials: 'same-origin'
    })
      .then(function (r) { return r.json(); })
      .then(function (json) {
        if (!json || !json.success) throw new Error((json && json.error) || 'Falha ao carregar lotes');
        lotsByPolygon = {};
        (json.lots || []).forEach(function (lot) {
          if (lot && lot.polygon_id) lotsByPolygon[lot.polygon_id] = lot;
        });
      });
  }

  function saveCurrent() {
    if (!selected) {
      setMsg('Selecione um polígono primeiro.', true);
      return Promise.resolve();
    }
    var polygonId = selected.getAttribute('id');
    if (!polygonId) {
      setMsg('Polígono sem id.', true);
      return Promise.resolve();
    }

    var payload = {
      project_id: projectId,
      polygon_id: polygonId,
      numero_lote: lotForm.numero_lote.value,
      quadra: lotForm.quadra.value,
      area_m2: lotForm.area_m2.value,
      frente_m: lotForm.frente_m.value,
      fundo_m: lotForm.fundo_m.value,
      lateral_esq_m: lotForm.lateral_esq_m.value,
      lateral_dir_m: lotForm.lateral_dir_m.value,
      valor: lotForm.valor.value,
      status: lotForm.status.value,
      observacoes: lotForm.observacoes.value
    };

    setMsg('Salvando...', false);
    return fetch(appUrl + '/api/lot_update.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
      credentials: 'same-origin',
      body: JSON.stringify(payload)
    })
      .then(function (r) { return r.json(); })
      .then(function (json) {
        if (!json || !json.success) throw new Error((json && json.error) || 'Falha ao salvar');
        if (json.lot && json.lot.polygon_id) {
          lotsByPolygon[json.lot.polygon_id] = json.lot;
        }
        repaintAll();
        setMsg('Salvo com sucesso.', false);
      })
      .catch(function () {
        setMsg('Erro ao salvar.', true);
      });
  }

  function applyZoom() {
    if (!svgRoot) return;
    var el = svgRoot.querySelector('svg');
    if (!el) return;
    el.style.transformOrigin = '0 0';
    el.style.transform = 'scale(' + scale + ')';
  }

  if (btnZoomIn) btnZoomIn.addEventListener('click', function () { scale = Math.min(4, scale + 0.2); applyZoom(); });
  if (btnZoomOut) btnZoomOut.addEventListener('click', function () { scale = Math.max(0.4, scale - 0.2); applyZoom(); });
  if (btnReset) btnReset.addEventListener('click', function () { scale = 1; applyZoom(); svgWrap.scrollTop = 0; svgWrap.scrollLeft = 0; });
  if (btnToggleIds) btnToggleIds.addEventListener('click', function () { showIds = !showIds; repaintAll(); });

  if (btnClear) btnClear.addEventListener('click', function () {
    if (!lotForm) return;
    lotForm.reset();
    setMsg('', false);
  });

  if (lotForm) {
    lotForm.addEventListener('submit', function (e) {
      e.preventDefault();
      saveCurrent();
    });
  }

  if (csvForm) {
    csvForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var fileInput = csvForm.querySelector('input[type=file]');
      if (!fileInput || !fileInput.files || !fileInput.files[0]) return;
      var fd = new FormData();
      fd.append('project_id', String(projectId));
      fd.append('csv', fileInput.files[0]);

      setMsg('Importando CSV...', false);
      fetch(appUrl + '/api/lots_import_csv.php', { method: 'POST', body: fd, credentials: 'same-origin', headers: { 'X-CSRF-Token': csrf } })
        .then(function (r) { return r.json(); })
        .then(function (json) {
          if (!json || !json.success) throw new Error('Falha');
          return loadLots();
        })
        .then(function () {
          repaintAll();
          setMsg('CSV importado.', false);
        })
        .catch(function () {
          setMsg('Erro ao importar CSV.', true);
        });
    });
  }

  loadLots()
    .then(function () {
      wireSvgClicks();
      applyZoom();
      repaintAll();
    })
    .catch(function () {
      setMsg('Erro ao carregar lotes.', true);
    });
})();
