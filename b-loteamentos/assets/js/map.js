(function () {
  var projectId = window.__PROJECT_ID__;
  var appUrl = window.__APP_URL__ || '';
  var csrf = window.__CSRF__ || '';
  if (!projectId) return;

  var container = document.getElementById('map-container');
  if (!container) return;

  var msgEl = document.getElementById('map-msg');
  var lotTitle = document.getElementById('lot-title');
  var lotSubtitle = document.getElementById('lot-subtitle');
  var lotDetails = document.getElementById('lot-details');
  var btnReserve = document.getElementById('btn-reserve');
  var btnCancelReservation = document.getElementById('btn-cancel-reservation');
  var btnSell = document.getElementById('btn-sell');

  var lotsByPolygon = {};
  var selectedPolygonId = null;
  var currentFilter = 'all';

  function setMsg(t, isErr) {
    if (!msgEl) return;
    msgEl.textContent = t || '';
    msgEl.style.color = isErr ? '#ef4444' : '#64748b';
  }

  if (btnCancelReservation) {
    btnCancelReservation.addEventListener('click', function () {
      if (!selectedPolygonId) return;
      var lot = lotsByPolygon[selectedPolygonId];
      if (!lot || !lot.reservation_id) return;

      if (!confirm('Cancelar reserva ativa deste lote?')) return;

      setMsg('Cancelando reserva...', false);
      postJson(appUrl + '/api/reservation_cancel.php', {
        reservation_id: lot.reservation_id
      }).then(function (json) {
        if (!json || !json.success) throw new Error((json && json.error) || 'Falha');
        return refresh();
      }).then(function () {
        setMsg('Reserva cancelada.', false);
      }).catch(function (e) {
        setMsg(e.message || 'Erro', true);
      });
    });
  }

  function colorByStatus(el, status) {
    var fill = '#6b7280';
    if (status === 'disponivel') fill = '#22c55e';
    if (status === 'reservado') fill = '#eab308';
    if (status === 'vendido') fill = '#ef4444';
    if (status === 'indisponivel') fill = '#6b7280';
    el.style.transition = 'fill 0.2s ease';
    el.style.fill = fill;
  }

  function loadLots() {
    return fetch(appUrl + '/api/lots.php?project_id=' + encodeURIComponent(projectId), { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (json) {
        if (!json || !json.success) throw new Error('Falha');
        return json.lots || [];
      });
  }

  function paint(lots) {
    var svg = container.querySelector('svg');
    if (!svg) return;
    var byId = {};
    lots.forEach(function (l) { if (l && l.polygon_id) byId[l.polygon_id] = l; });

    lotsByPolygon = byId;

    svg.querySelectorAll('path[id], polygon[id], rect[id]').forEach(function (el) {
      var id = el.getAttribute('id');
      var lot = id ? byId[id] : null;
      var status = lot ? lot.status : 'indisponivel';
      colorByStatus(el, status);
      el.style.cursor = 'pointer';

      var visible = (currentFilter === 'all' || status === currentFilter);
      el.style.opacity = visible ? '1' : '0.15';

      if (selectedPolygonId && id === selectedPolygonId) {
        el.style.outline = '3px solid #3b82f6';
      } else {
        el.style.outline = '';
      }

      el.onclick = function (e) {
        e.preventDefault();
        selectedPolygonId = id;
        renderPanel();
        paint(Object.values(lotsByPolygon));
      };
    });
  }

  function renderPanel() {
    if (!lotTitle || !lotDetails) return;
    if (!selectedPolygonId) {
      lotTitle.textContent = 'Nenhum';
      if (lotSubtitle) lotSubtitle.textContent = '';
      lotDetails.textContent = '';
      return;
    }

    var lot = lotsByPolygon[selectedPolygonId];
    if (!lot) {
      lotTitle.textContent = selectedPolygonId;
      if (lotSubtitle) lotSubtitle.textContent = 'Sem cadastro';
      lotDetails.textContent = '';
      return;
    }

    lotTitle.textContent = 'Lote ' + (lot.numero_lote || '-') + ' — Quadra ' + (lot.quadra || '-');
    if (lotSubtitle) {
      lotSubtitle.textContent = 'Status: ' + lot.status;
    }

    var html = '';
    html += 'Área: ' + (lot.area_m2 || '-') + ' m²<br>';
    html += 'Frente: ' + (lot.frente_m || '-') + ' | Fundo: ' + (lot.fundo_m || '-') + '<br>';
    html += 'Valor: R$ ' + (lot.valor || '-') + '<br>';
    if (lot.reservation_id) {
      html += '<br><strong>Reserva ativa</strong><br>';
      html += 'Cliente: ' + (lot.reservation_buyer_name || '-') + '<br>';
      html += 'Expira: ' + (lot.reservation_expires_at || '-') + '<br>';
      html += 'Corretor: ' + (lot.corretor_name || '-') + '<br>';
    }
    lotDetails.innerHTML = html;

    if (btnReserve) btnReserve.disabled = lot.status !== 'disponivel';
    if (btnCancelReservation) btnCancelReservation.disabled = !lot.reservation_id;
    if (btnSell) btnSell.disabled = (lot.status !== 'disponivel' && lot.status !== 'reservado');
  }

  function refresh() {
    return loadLots().then(function (lots) {
      paint(lots);
      renderPanel();
    });
  }

  function postJson(url, payload) {
    return fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
      body: JSON.stringify(payload)
    }).then(function (r) { return r.json(); });
  }

  if (btnReserve) {
    btnReserve.addEventListener('click', function () {
      if (!selectedPolygonId) return;
      var lot = lotsByPolygon[selectedPolygonId];
      if (!lot) return;

      var buyerName = prompt('Nome do comprador (obrigatório):');
      if (!buyerName) return;
      var buyerPhone = prompt('Telefone do comprador (obrigatório):');
      if (!buyerPhone) return;

      setMsg('Reservando...', false);
      postJson(appUrl + '/api/reservation_create.php', {
        lot_id: lot.id,
        buyer_name: buyerName,
        buyer_phone: buyerPhone
      }).then(function (json) {
        if (!json || !json.success) throw new Error((json && json.error) || 'Falha');
        return refresh();
      }).then(function () {
        setMsg('Reserva criada.', false);
      }).catch(function (e) {
        setMsg(e.message || 'Erro', true);
      });
    });
  }

  if (btnSell) {
    btnSell.addEventListener('click', function () {
      if (!selectedPolygonId) return;
      var lot = lotsByPolygon[selectedPolygonId];
      if (!lot) return;

      var buyerName = prompt('Nome do comprador:');
      if (!buyerName) return;
      var buyerDocument = prompt('CPF/CNPJ do comprador:');
      if (!buyerDocument) return;
      var buyerPhone = prompt('Telefone do comprador:');
      if (!buyerPhone) return;
      var saleDate = prompt('Data da venda (YYYY-MM-DD):');
      if (!saleDate) return;
      var finalValue = prompt('Valor final (ex: 85000.00):');
      if (!finalValue) return;

      setMsg('Registrando venda...', false);
      postJson(appUrl + '/api/sale.php', {
        lot_id: lot.id,
        buyer_name: buyerName,
        buyer_document: buyerDocument,
        buyer_phone: buyerPhone,
        sale_date: saleDate,
        final_value: finalValue
      }).then(function (json) {
        if (!json || !json.success) throw new Error((json && json.error) || 'Falha');
        return refresh();
      }).then(function () {
        setMsg('Venda registrada.', false);
      }).catch(function (e) {
        setMsg(e.message || 'Erro', true);
      });
    });
  }

  document.querySelectorAll('[data-filter]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      currentFilter = btn.getAttribute('data-filter') || 'all';
      paint(Object.values(lotsByPolygon));
    });
  });

  refresh().catch(function () {});
})();
