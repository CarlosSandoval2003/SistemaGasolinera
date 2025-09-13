<style>#uni_modal .modal-footer{display:none!important}</style>
<div class="container-fluid">
  <div class="mb-2">
    <div><b><?= htmlspecialchars($item['name']) ?></b> • Stock: <?= number_format($item['qty_liters']??0,2) ?> L</div>
  </div>
  <div class="row g-2 mb-2">
    <div class="col-md-4">
      <input type="date" id="df" class="form-control form-control-sm">
    </div>
    <div class="col-md-4">
      <input type="date" id="dt" class="form-control form-control-sm">
    </div>
    <div class="col-md-4">
      <button class="btn btn-primary btn-sm" id="filtrar">Filtrar</button>
    </div>
  </div>
  <table class="table table-sm table-bordered">
    <thead><tr><th>Fecha</th><th>Tipo</th><th>Litros</th><th>Nota</th></tr></thead>
    <tbody>
      <?php if(!empty($rows)): foreach($rows as $r): ?>
        <tr>
          <td><?= htmlspecialchars($r['created_at']) ?></td>
          <td><?= htmlspecialchars($r['kind']) ?></td>
          <td class="text-end"><?= number_format($r['qty_liters'],2) ?></td>
          <td><?= htmlspecialchars($r['note']) ?></td>
        </tr>
      <?php endforeach; else: ?>
        <tr><td colspan="4" class="text-center">Sin movimientos</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
  <div class="text-end">
    <button class="btn btn-dark btn-sm" data-bs-dismiss="modal">Cerrar</button>
  </div>
</div>
<script>
$('#filtrar').click(function(){
  uni_modal('Kardex','index.php?url=containers/movements/<?= $item['container_id']?>&date_from='+$('#df').val()+'&date_to='+$('#dt').val(),'large');
});
</script>
