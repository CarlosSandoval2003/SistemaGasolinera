<?php
// $rows, $dfrom, $dto, $date_error, $filters llegan del controlador
$nitFil  = htmlspecialchars($filters['nit'] ?? '');
$rcptFil = htmlspecialchars($filters['receipt'] ?? '');
$__dfrom = $dfrom ?: date('Y-m-d', strtotime('-7 days'));
$__dto   = $dto   ?: date('Y-m-d');
?>
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title mb-0">Reporte de Ventas</h3>
  </div>

  <div class="card-body">
    <h5 class="mb-3">Filtros</h5>

    <?php if (!empty($date_error)): ?>
      <div class="alert alert-danger" role="alert">
        <?= htmlspecialchars($date_error) ?>
      </div>
    <?php endif; ?>

    <div class="row g-3 align-items-end">
      <div class="col-md-2">
        <label for="date_from" class="form-label">Desde</label>
        <input type="date" id="date_from" value="<?= htmlspecialchars($__dfrom) ?>" class="form-control form-control-sm rounded-0">
      </div>
      <div class="col-md-2">
        <label for="date_to" class="form-label">Hasta</label>
        <input type="date" id="date_to" value="<?= htmlspecialchars($__dto) ?>" class="form-control form-control-sm rounded-0">
      </div>

      <div class="col-md-3">
        <label for="fil_nit" class="form-label">NIT</label>
        <input type="search" id="fil_nit" class="form-control form-control-sm rounded-0"
               placeholder="Buscar por NIT" value="<?= $nitFil ?>">
      </div>

      <div class="col-md-3">
        <label for="fil_receipt" class="form-label">Recibo</label>
        <input type="search" id="fil_receipt" class="form-control form-control-sm rounded-0"
               placeholder="Buscar por No. de recibo" value="<?= $rcptFil ?>">
      </div>

      <div class="col-md-2 d-flex gap-2">
        <button class="btn btn-primary btn-sm rounded-0" id="filter" type="button" data-bs-toggle="tooltip" title="Aplicar filtros">
          <i class="fa fa-filter"></i> Filtrar
        </button>
        <button class="btn btn-success btn-sm rounded-0" id="print" type="button" data-bs-toggle="tooltip" title="Imprimir reporte">
          <i class="fa fa-print"></i> Imprimir
        </button>
      </div>
    </div>

    <hr>
    <div class="clear-fix mb-2"></div>

    <div id="outprint">
      <table class="table table-hover table-striped table-bordered align-middle" id="tbl-sales">
        <colgroup>
          <col width="5%"><col width="12%"><col width="14%"><col width="14%">
          <col width="24%"><col width="17%"><col width="14%">
        </colgroup>
        <thead>
          <tr>
            <th class="text-center p-0">#</th>
            <th class="text-center p-0">Fecha</th>
            <th class="text-center p-0">Recibo</th>
            <th class="text-center p-0">NIT</th>
            <th class="text-center p-0">Información</th>
            <th class="text-center p-0">Detalle</th>
            <th class="text-center p-0">Total (Q)</th>
          </tr>
        </thead>
        <tbody id="sales-tbody">
        <?php if (!empty($rows)): $i=1; foreach ($rows as $row): ?>
          <tr>
            <td class="text-center p-1 idx"><?= $i++ ?></td>
            <td class="py-1 px-2"><?= htmlspecialchars(date("Y-m-d H:i", strtotime($row['date_added']))) ?></td>

            <!-- Recibo: iconitos con tooltip (ver / reimprimir) -->
            <td class="py-1 px-2 text-center recibo">
              <div class="d-flex justify-content-center gap-2">
                <button
                  class="btn btn-outline-primary btn-sm rounded-0 preview-receipt"
                  data-id="<?= (int)$row['transaction_id'] ?>"
                  data-bs-toggle="tooltip"
                  title="Vista previa del recibo">
                  <i class="fa fa-eye"></i>
                </button>
                <button
                  class="btn btn-outline-secondary btn-sm rounded-0 reprint-receipt"
                  data-id="<?= (int)$row['transaction_id'] ?>"
                  data-bs-toggle="tooltip"
                  title="Reimprimir recibo">
                  <i class="fa fa-print"></i>
                </button>
              </div>
              <div class="small text-muted mt-1 rcpt-text"><?= htmlspecialchars($row['receipt_no']) ?></div>
            </td>

            <td class="py-1 px-2 text-center nit"><?= htmlspecialchars($row['nit'] ?? 'CF') ?></td>

            <td class="py-1 px-2">
              <div class="lh-1">
                <div><b><?= htmlspecialchars($row['petrol']) ?></b></div>
                <div class="text-muted">Precio (Q/gal): <?= number_format((float)$row['price'], 4) ?></div>
              </div>
            </td>

            <td class="py-1 px-2">
              <div class="lh-1">
                <div><span class="text-muted">Galones: </span><?= number_format((float)$row['liter'], 3) ?></div>
                <div><span class="text-muted">Importe (Q): </span><?= number_format((float)$row['amount'], 2) ?></div>
                <div>
                  <span class="text-muted">Tipo: </span>
                  <?php
                    $t = (int)$row['type'];
                    echo $t===1 ? 'Efectivo' : ($t===3 ? 'Tarjeta' : 'Crédito');
                  ?>
                </div>
              </div>
            </td>

            <td class="py-1 px-2 text-end"><?= number_format((float)$row['total'], 2) ?></td>
          </tr>
        <?php endforeach; else: ?>
          <tr class="no-data"><td colspan="7" class="text-center">No hay transacciones en el rango seleccionado.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
(function(){
  // Tooltips Bootstrap
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map(el => new bootstrap.Tooltip(el));

  // Validación cliente de fechas
  function isValidRange() {
    const from = $('#date_from').val();
    const to   = $('#date_to').val();
    if(!from || !to) return false;
    const f = new Date(from + 'T00:00:00');
    const t = new Date(to   + 'T00:00:00');
    return (f.toString() !== 'Invalid Date' && t.toString() !== 'Invalid Date' && f.getTime() <= t.getTime());
  }
  function showDateError() {
    alert('Rango de fechas inválido. Verifica "Desde" y "Hasta".');
  }

  // Armar QS de filtros (incluye NIT y Recibo)
  function qs(){
    const q = new URLSearchParams();
    const df = $('#date_from').val();  if(df) q.set('date_from', df);
    const dt = $('#date_to').val();    if(dt) q.set('date_to', dt);
    const nit = $('#fil_nit').val().trim();        if(nit) q.set('nit', nit);
    const rcp = $('#fil_receipt').val().trim();    if(rcp) q.set('receipt', rcp);
    return q.toString();
  }

  $('#filter').click(function(){
    if(!isValidRange()) { showDateError(); return; }
    location.href = "index.php?url=salesReport/index" + (qs() ? '&'+qs() : '');
  });

  // Impresión (usa la tabla renderizada)
  $('#print').click(function(){
    if(!isValidRange()) { showDateError(); return; }
    const h  = $('head').clone();
    const p  = $('#outprint').clone();
    const el = $('<div>').append(h);

    const from = $('#date_from').val();
    const to   = $('#date_to').val();
    const range = (from === to)
      ? new Date(from).toLocaleDateString()
      : (new Date(from).toLocaleDateString() + " - " + new Date(to).toLocaleDateString());

    el.append("<div class='text-center lh-1 fw-bold'>REPORTE DE VENTAS<br/><small>Rango: "+range+"</small></div><hr/>");
    p.find('button').remove(); // no imprimir botones
    el.append(p);

    const nw = window.open("", "", "width=1200,height=900,left=150");
    nw.document.write(el.html());
    nw.document.close();
    setTimeout(() => { nw.print(); setTimeout(()=>nw.close(), 150); }, 200);
  });

  // === Vista previa y reimpresión ===
  $(document).on('click', '.preview-receipt', function(){
    const id = $(this).data('id');
    uni_modal('Recibo', "index.php?url=sales/receipt/" + id + "&view_only=1", '');
  });

  $(document).on('click', '.reprint-receipt', function(){
    const id = $(this).data('id');
    uni_modal('Recibo', "index.php?url=sales/receipt/" + id + "&view_only=1", '');
    const tryPrint = setInterval(function(){
      const $btn = $('#uni_modal').find('#print_receipt');
      if($btn.length){
        $btn.trigger('click');
        clearInterval(tryPrint);
      }
    }, 200);
    setTimeout(()=>clearInterval(tryPrint), 3000);
  });

  // === Búsqueda en vivo por NIT y Recibo (cliente) ===
  const $nit  = document.getElementById('fil_nit');
  const $rcpt = document.getElementById('fil_receipt');
  const $tbody= document.getElementById('sales-tbody');

  function norm(s){ return (s||'').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''); }
  function applyFilter(){
    const tNit  = norm($nit.value.trim());
    const tRcpt = norm($rcpt.value.trim());
    const rows  = Array.from($tbody.querySelectorAll('tr'));
    let visible = 0;

    rows.forEach(tr=>{
      if (tr.classList.contains('no-data')) return;
      const nit  = norm(tr.querySelector('.nit')?.textContent || '');
      const rtxt = norm(tr.querySelector('.rcpt-text')?.textContent || '');
      const okNit = tNit==='' || nit.includes(tNit);
      const okRc  = tRcpt==='' || rtxt.includes(tRcpt);
      const show  = okNit && okRc;
      tr.style.display = show ? '' : 'none';
      if (show) visible++;
    });

    let noData = $tbody.querySelector('tr.no-data');
    if (!noData) {
      noData = document.createElement('tr');
      noData.className = 'no-data';
      noData.innerHTML = '<td colspan="7" class="text-center">Sin resultados</td>';
      $tbody.appendChild(noData);
    }
    noData.style.display = (visible===0) ? '' : 'none';

    // Reindexar
    let i = 1;
    rows.forEach(tr=>{
      if (!tr.classList.contains('no-data') && tr.style.display!=='none') {
        const idx = tr.querySelector('td.idx');
        if (idx) idx.textContent = i++;
      }
    });
  }
  let t1=null, t2=null;
  $nit.addEventListener('input', ()=>{ clearTimeout(t1); t1=setTimeout(applyFilter,150); });
  $rcpt.addEventListener('input',()=>{ clearTimeout(t2); t2=setTimeout(applyFilter,150); });
})();
</script>
