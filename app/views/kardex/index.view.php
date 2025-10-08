<?php
// Mapa: código → etiqueta en español
$KIND_LABELS = [
  'IN'           => 'Entrada',
  'OUT'          => 'Salida',
  'TRANSFER_IN'  => 'Transferencia (entrada)',
  'TRANSFER_OUT' => 'Transferencia (salida)',
  'ADJUST'       => 'Ajuste',
];
?>
<div class="card">
  <div class="card-header d-flex justify-content-between">
    <h3 class="card-title">Kardex de Contenedores</h3>
    <div class="card-tools">
      <button class="btn btn-success btn-sm" id="btn-export"><i class="fa fa-file-excel-o"></i> Exportar CSV</button>
      <button class="btn btn-primary btn-sm" id="btn-print"><i class="fa fa-print"></i> Imprimir</button>
    </div>
  </div>

  <div class="card-body">
    <h6>Filtros</h6>
    <div class="row g-2 align-items-end">
      <div class="col-md-2">
        <label class="form-label">Fecha desde</label>
        <input type="date" id="date_from" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label">Fecha hasta</label>
        <input type="date" id="date_to" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Contenedor</label>
        <select id="container_id" class="form-select form-select-sm">
          <option value="">-- Todos --</option>
          <?php foreach($containers as $c): ?>
            <option value="<?= $c['container_id'] ?>" <?= (!empty($filters['container_id']) && $filters['container_id']==$c['container_id'])?'selected':'' ?>>
              <?= htmlspecialchars($c['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Combustible</label>
        <select id="petrol_type_id" class="form-select form-select-sm">
          <option value="">-- Todos --</option>
          <?php foreach($types as $t): ?>
            <option value="<?= $t['petrol_type_id'] ?>" <?= (!empty($filters['petrol_type_id']) && $filters['petrol_type_id']==$t['petrol_type_id'])?'selected':'' ?>>
              <?= htmlspecialchars($t['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">Movimiento</label>
        <select id="kind" class="form-select form-select-sm">
          <option value="">-- Todos --</option>
          <?php foreach(['IN','OUT','TRANSFER_IN','TRANSFER_OUT','ADJUST'] as $k): ?>
            <option value="<?= $k ?>" <?= (!empty($filters['kind']) && $filters['kind']===$k)?'selected':'' ?>>
              <?= $KIND_LABELS[$k] ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">Usuario</label>
        <select id="user_id" class="form-select form-select-sm">
          <option value="">-- Todos --</option>
          <?php foreach($users as $u): ?>
            <option value="<?= $u['user_id'] ?>" <?= (!empty($filters['user_id']) && $filters['user_id']==$u['user_id'])?'selected':'' ?>>
              <?= htmlspecialchars($u['fullname']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <button class="btn btn-primary btn-sm w-100" id="btn-filter"><i class="fa fa-filter"></i> Filtrar</button>
      </div>
    </div>

    <hr>

    <div id="outprint">
      <?php $withSaldo = !empty($filters['container_id']); ?>
      <div class="mb-2 text-center fw-bold">
        Kardex
        <?php if(!empty($filters['date_from'])): ?>
          &nbsp; <small>Del <?= htmlspecialchars($filters['date_from']) ?> al <?= htmlspecialchars($filters['date_to'] ?? $filters['date_from']) ?></small>
        <?php endif; ?>
      </div>

      <table class="table table-sm table-bordered table-striped align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Fecha</th>
            <th>Contenedor</th>
            <th>Combustible</th>
            <th>Movimiento</th>
            <th class="text-end">Entrada (gal)</th>
            <th class="text-end">Salida (gal)</th>
            <th class="text-end">Delta (gal)</th>
            <?php if($withSaldo): ?><th class="text-end">Saldo (gal)</th><?php endif; ?>
            <th>Nota</th>
            <th>Usuario</th>
            <th class="text-center">Detalle</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          $i=1; 
          $saldo = $withSaldo ? (float)$open_bal : 0;
          if ($withSaldo): ?>
            <tr class="table-secondary">
              <td colspan="8" class="text-end"><b>Saldo inicial</b></td>
              <td class="text-end"><b><?= number_format($saldo,2) ?></b></td>
              <td colspan="2"></td>
            </tr>
          <?php endif; ?>

          <?php if(!empty($rows)): foreach($rows as $r): 
            // Mostrar cantidades “tal cual” (BD en litros), solo etiquetamos como (gal) de forma visual.
            $entrada = 0.0; $salida = 0.0;
            if ((float)$r['delta'] >= 0) $entrada = (float)$r['qty_liters']; else $salida = abs((float)$r['qty_liters']);
            if ($withSaldo) $saldo += (float)$r['delta'];

            // badge por tipo
            $badge = 'secondary';
            if ($r['kind']==='IN' || $r['kind']==='TRANSFER_IN') $badge='success';
            if ($r['kind']==='OUT' || $r['kind']==='TRANSFER_OUT') $badge='danger';
            if ($r['kind']==='ADJUST') $badge='warning';

            // Etiqueta en español
            $kind_es = $KIND_LABELS[$r['kind']] ?? $r['kind'];
          ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= htmlspecialchars($r['created_at']) ?></td>
              <td><?= htmlspecialchars($r['container_name']) ?></td>
              <td><?= htmlspecialchars($r['petrol_name']) ?></td>
              <td><span class="badge bg-<?= $badge ?>"><?= htmlspecialchars($kind_es) ?></span></td>
              <td class="text-end"><?= number_format($entrada,2) ?></td>
              <td class="text-end"><?= number_format($salida,2) ?></td>
              <td class="text-end"><?= number_format($r['delta'],2) ?></td>
              <?php if($withSaldo): ?><td class="text-end"><?= number_format($saldo,2) ?></td><?php endif; ?>
              <td><?= htmlspecialchars($r['note'] ?? '') ?></td>
              <td><?= htmlspecialchars($r['user_fullname'] ?? '') ?></td>
              <td class="text-center">
<button class="btn btn-outline-primary btn-sm btn-detail"
                        data-kind="<?= htmlspecialchars($r['kind']) ?>"
                        data-ref="<?= (int)($r['ref_id'] ?? 0) ?>"
                        data-tx="<?= (int)$r['tx_id'] ?>"
                        title="Ver detalle">
                  <i class="fa fa-eye"></i>
                </button>
              </td>
            </tr>
          <?php endforeach; else: ?>
            <tr><td colspan="<?= $withSaldo?12:11 ?>" class="text-center">Sin movimientos</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
(function(){
  function qs(){
    const q = new URLSearchParams();
    const df = $('#date_from').val(); if(df) q.set('date_from', df);
    const dt = $('#date_to').val();   if(dt) q.set('date_to', dt);
    const cid= $('#container_id').val(); if(cid) q.set('container_id', cid);
    const pt = $('#petrol_type_id').val(); if(pt) q.set('petrol_type_id', pt);
    const kd = $('#kind').val(); if(kd) q.set('kind', kd); // valor crudo (IN/OUT/…)
    const uid= $('#user_id').val(); if(uid) q.set('user_id', uid);
    return q.toString();
  }

  $('#btn-filter').click(function(){
    location.href = 'index.php?url=kardex/index' + (qs() ? '&'+qs() : '');
  });

  $('#btn-export').click(function(){
    const url = 'index.php?url=kardex/export' + (qs() ? '&'+qs() : '');
    window.location = url;
  });

  $('#btn-print').click(function(){
    const h = $('head').clone();
    const p = $('#outprint').clone();
    const el = $('<div>').append(h);
    el.append("<div class='text-center fw-bold'>KARDEX</div><hr/>");
    el.append(p);
    const nw = window.open("","","width=1200,height=900,left=150");
    nw.document.write(el.html());
    nw.document.close();
    setTimeout(()=>{ nw.print(); setTimeout(()=>nw.close(), 150); }, 200);
  });

  // === Botón Detalle ===
  $(document).off('click.kx','.btn-detail').on('click.kx','.btn-detail', function(){
    const kind = $(this).data('kind');           // IN / OUT / TRANSFER_IN / TRANSFER_OUT / ADJUST
    const ref  = Number($(this).data('ref')||0);
    const tx   = Number($(this).data('tx'));

    if (kind === 'OUT' && ref) {
      uni_modal('Recibo de venta', 'index.php?url=sales/receipt/' + ref + '&view_only=1', '');
      return;
    }
    if (kind === 'IN' && ref) {
      uni_modal('Orden de compra', 'index.php?url=purchase/viewOrder/' + ref, 'large');
      return;
    }
    // Transferencias / Ajustes
    uni_modal('Detalle de movimiento', 'index.php?url=kardex/detail/' + tx, 'mid-large');
  });
})();
</script>
