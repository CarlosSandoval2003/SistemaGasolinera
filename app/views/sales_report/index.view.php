<?php
// $rows, $dfrom, $dto, $date_error llegan del controlador
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
        <input type="date" id="date_from" value="<?= htmlspecialchars($dfrom) ?>" class="form-control form-control-sm rounded-0">
      </div>
      <div class="col-md-2">
        <label for="date_to" class="form-label">Hasta</label>
        <input type="date" id="date_to" value="<?= htmlspecialchars($dto) ?>" class="form-control form-control-sm rounded-0">
      </div>

      <div class="col-md-4 d-flex gap-2">
        <button class="btn btn-primary btn-sm rounded-0" id="filter" type="button" data-bs-toggle="tooltip" title="Aplicar filtro de fechas">
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
      <table class="table table-hover table-striped table-bordered align-middle">
        <colgroup>
          <col width="5%"><col width="14%"><col width="18%">
          <col width="28%"><col width="20%"><col width="15%">
        </colgroup>
        <thead>
          <tr>
            <th class="text-center p-0">#</th>
            <th class="text-center p-0">Fecha</th>
            <th class="text-center p-0">Recibo</th>
            <th class="text-center p-0">Información</th>
            <th class="text-center p-0">Detalle</th>
            <th class="text-center p-0">Total (Q)</th>
          </tr>
        </thead>
        <tbody>
        <?php if (!empty($rows)): $i=1; foreach ($rows as $row): ?>
          <tr>
            <td class="text-center p-1"><?= $i++ ?></td>
            <td class="py-1 px-2"><?= htmlspecialchars(date("Y-m-d H:i", strtotime($row['date_added']))) ?></td>

            <!-- Recibo: iconitos con tooltip (ver / reimprimir) -->
            <td class="py-1 px-2 text-center">
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
              <div class="small text-muted mt-1"><?= htmlspecialchars($row['receipt_no']) ?></div>
            </td>

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
          <tr><td colspan="6" class="text-center">No hay transacciones en el rango seleccionado.</td></tr>
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

  // Validación cliente de fechas (cambio #7)
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

  $('#filter').click(function(){
    if(!isValidRange()) { showDateError(); return; }
    location.href = "index.php?url=sales_report/index&date_from=" + $('#date_from').val() + "&date_to=" + $('#date_to').val();
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
    p.find('button').remove();         // no imprimir botones
    el.append(p);

    const nw = window.open("", "", "width=1200,height=900,left=150");
    nw.document.write(el.html());
    nw.document.close();
    setTimeout(() => { nw.print(); setTimeout(()=>nw.close(), 150); }, 200);
  });

  // Funcionales #2 y #8: vista previa y reimpresión con iconos + tooltip
  $(document).on('click', '.preview-receipt', function(){
    const id = $(this).data('id');
    uni_modal('Recibo', "index.php?url=sales/receipt/" + id + "&view_only=1", '');
  });

  $(document).on('click', '.reprint-receipt', function(){
    const id = $(this).data('id');
    // Abrimos el recibo y disparamos impresión automáticamente cuando cargue
    uni_modal('Recibo', "index.php?url=sales/receipt/" + id + "&view_only=1", '');
    // intentar auto-click al botón de imprimir dentro del modal
    const tryPrint = setInterval(function(){
      const $btn = $('#uni_modal').find('#print_receipt');
      if($btn.length){
        $btn.trigger('click');
        clearInterval(tryPrint);
      }
    }, 200);
    // seguridad: cortar el intervalo después de 3s
    setTimeout(()=>clearInterval(tryPrint), 3000);
  });
})();
</script>
