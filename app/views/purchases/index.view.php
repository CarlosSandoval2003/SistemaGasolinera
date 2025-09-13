<div class="card">
  <div class="card-header d-flex justify-content-between">
    <h3 class="card-title">Compras</h3>
    <div>
      <button class="btn btn-dark btn-sm" id="new_purchase">Nueva Compra</button>
    </div>
  </div>
  <div class="card-body">
    <div class="row g-2 align-items-end">
      <div class="col-md-3">
        <label class="form-label">Fecha Inicio</label>
        <input type="date" id="df" class="form-control" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Fecha Final</label>
        <input type="date" id="dt" class="form-control" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Proveedor</label>
        <select id="sid" class="form-select">
          <option value="">Todos</option>
          <?php foreach($suppliers as $s): ?>
            <option value="<?= $s['supplier_id'] ?>" <?= (!empty($filters['supplier_id']) && $filters['supplier_id']==$s['supplier_id'])?'selected':'' ?>>
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
    <table class="table table-bordered table-striped">
      <thead><tr>
        <th>#</th><th>Fecha</th><th>Recibo</th><th>Proveedor</th><th>Total</th><th>Acción</th>
      </tr></thead>
      <tbody>
        <?php $i=1; foreach($purchases as $p): ?>
          <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($p['date']) ?></td>
            <td><?= htmlspecialchars($p['receipt_no']) ?></td>
            <td><?= htmlspecialchars($p['supplier_name']) ?></td>
            <td class="text-end"><?= number_format($p['total_cost'],2) ?></td>
            <td><button class="btn btn-sm btn-info text-light view" data-id="<?= $p['purchase_id'] ?>">Ver</button></td>
          </tr>
        <?php endforeach; if(empty($purchases)): ?>
          <tr><td colspan="6" class="text-center">Sin datos.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<script>
$('#new_purchase').click(()=> uni_modal('New Purchase','index.php?url=purchase/manage','large'));
$('.view').click(function(){ uni_modal('Purchase','index.php?url=purchase/viewPurchase/'+$(this).data('id'),'large') });
$('#filter').click(function(){
  const qs = new URLSearchParams({
    date_from: $('#df').val()||'',
    date_to:   $('#dt').val()||'',
    supplier_id: $('#sid').val()||''
  }).toString();
  location.href = 'index.php?url=purchase/index&'+qs;
});
</script>
