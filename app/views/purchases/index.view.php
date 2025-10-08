<div class="card">
  <div class="card-header d-flex justify-content-between">
    <h3 class="card-title">Órdenes de compra</h3>
    <div>
      <button class="btn btn-dark btn-sm" id="new_purchase">Nueva orden de compra</button>
    </div>
  </div>
  <div class="card-body">

    <?php if(!empty($error)): ?>
      <div class="alert alert-warning py-2 mb-3"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="row g-2 align-items-end">
      <div class="col-md-3">
        <label class="form-label">Fecha inicio</label>
        <input type="date" id="df" class="form-control" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Fecha final</label>
        <input type="date" id="dt" class="form-control" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Proveedor</label>
        <select id="sid" class="form-select">
          <option value="">Todos</option>
          <?php foreach($suppliers as $s): ?>
            <option value="<?= $s['supplier_id'] ?>" <?= (!empty($filters['supplier_id']) && (int)$filters['supplier_id']==(int)$s['supplier_id'])?'selected':'' ?>>
              <?= htmlspecialchars($s['name']) ?>
            </option>
          <?php endforeach ?>
        </select>
      </div>
      <div class="col-md-2">
        <button class="btn btn-primary w-100" id="filter">Filtrar</button>
      </div>
    </div>

    <hr>
    <table class="table table-bordered table-striped align-middle">
      <thead>
        <tr>
          <th>#</th>
          <th>Fecha</th>
          <th>Código</th>
          <th>Proveedor</th>
          <th>Estatus</th>
          <th class="text-end">Total (Q)</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php $i=1; foreach($purchases as $p): ?>
          <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($p['fecha']) ?></td>
            <td><?= htmlspecialchars($p['codigo']) ?></td>
            <td><?= htmlspecialchars($p['supplier_name'] ?? '') ?></td>
            <td>
              <?php
                $st = $p['estatus'] ?? 'BORRADOR';
                $badge = 'secondary';
                if ($st==='BORRADOR') $badge='secondary';
                elseif ($st==='SOLICITADO') $badge='info';
                elseif ($st==='EN_TRANSITO') $badge='warning';
                elseif ($st==='RECIBIDO') $badge='success';
                elseif ($st==='CANCELADO') $badge='dark';
              ?>
              <span class="badge bg-<?= $badge ?>"><?= htmlspecialchars($st) ?></span>
            </td>
            <td class="text-end"><?= number_format((float)($p['total'] ?? 0),2) ?></td>
            <td class="d-flex gap-1">
  <button class="btn btn-sm btn-info text-light view" data-id="<?= $p['id'] ?>" title="Ver OC">
    <i class="fa fa-eye"></i>
  </button>

  <!-- Botón imprimir directo (abre ventana con formato del original) -->
  <button class="btn btn-sm btn-success print" data-id="<?= $p['id'] ?>" title="Imprimir">
    <i class="fa fa-print"></i>
  </button>

  <?php if(($p['estatus'] ?? '') !== 'RECIBIDO'): ?>
  <button class="btn btn-sm btn-primary recibir" data-id="<?= $p['id'] ?>" title="Confirmar recibido">
    <i class="fa fa-check"></i>
  </button>
  <?php endif; ?>
</td>

          </tr>
        <?php endforeach; if(empty($purchases)): ?>
          <tr><td colspan="7" class="text-center">Sin datos.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
$('#new_purchase').click(()=> uni_modal('Nueva orden de compra','index.php?url=purchase/manage','large'));

$('.view').click(function(){
  uni_modal('Orden de compra','index.php?url=purchase/viewOrder/'+$(this).data('id'),'large')
});

$('.recibir').click(function(){
  uni_modal('Confirmar recibido','index.php?url=purchase/reciboCreate/'+$(this).data('id'),'large')
});

$('.print').click(function(){
  const id = $(this).data('id');
  window.open('index.php?url=purchase/printOrder/'+id, '', 'width=1200,height=900,left=150');
});

$('#filter').click(function(){
  const qs = new URLSearchParams({
    date_from: $('#df').val()||'',
    date_to:   $('#dt').val()||'',
    supplier_id: $('#sid').val()||''
  }).toString();
  location.href = 'index.php?url=purchase/index&'+qs;
});
</script>
