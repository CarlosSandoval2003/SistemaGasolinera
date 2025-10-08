<style>#uni_modal .modal-footer{display:none!important}</style>
<div class="container-fluid">
  <div class="mb-2">
    <div class="fw-bold">OC: <?= htmlspecialchars($h['codigo']) ?></div>
    <div>Proveedor: <?= htmlspecialchars($h['supplier_name'] ?? '') ?></div>
    <div>Fecha: <?= htmlspecialchars($h['fecha']) ?></div>
    <div>Estatus: <span class="badge bg-<?= ($h['estatus']==='RECIBIDO'?'success':($h['estatus']==='EN_TRANSITO'?'warning':($h['estatus']==='SOLICITADO'?'info':($h['estatus']==='CANCELADO'?'dark':'secondary')))) ?>">
      <?= htmlspecialchars($h['estatus']) ?>
    </span></div>
  </div>

  <table class="table table-sm table-bordered">
    <thead>
      <tr>
        <th>#</th>
<th>Gasolina</th>
<th class="text-end">Galones (gal)</th>
<th class="text-end">Costo (Q/gal)</th>
<th class="text-end">Total línea (Q)</th>

      </tr>
    </thead>
    <tbody>
      <?php $i=1; $subtotal=0; foreach($items as $it): 
        $line = (float)$it['cantidad_litros'] * (float)$it['precio_unitario']; $subtotal+=$line; ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><?= htmlspecialchars($it['petrol_name']) ?></td>
<td class="text-end"><?= number_format((float)$it['cantidad_litros'],3) /* tratada como galones */ ?></td>
<td class="text-end"><?= number_format((float)$it['precio_unitario'],4) /* Q/gal */ ?></td>
<td class="text-end"><?= number_format(((float)$it['cantidad_litros'] * (float)$it['precio_unitario']),2) ?></td>

          <td class="text-end"><?= number_format($line,2) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr><th colspan="4" class="text-end">SUBTOTAL</th><th class="text-end"><?= number_format($subtotal,2) ?></th></tr>
      <tr><th colspan="4" class="text-end">IMPUESTOS</th><th class="text-end"><?= number_format((float)($h['impuestos'] ?? 0),2) ?></th></tr>
      <tr><th colspan="4" class="text-end">TOTAL</th><th class="text-end"><?= number_format((float)($h['total'] ?? $subtotal),2) ?></th></tr>
    </tfoot>
  </table>

  <div class="text-end">
    <?php if(($h['estatus'] ?? '')!=='RECIBIDO'): ?>
      <button class="btn btn-sm btn-success" id="recibir"><i class="fa fa-check"></i> Confirmar recibido</button>
    <?php endif; ?>
    <button class="btn btn-sm btn-dark" data-bs-dismiss="modal">Cerrar</button>
  </div>
</div>
<script>
<?php if(($h['estatus'] ?? '')!=='RECIBIDO'): ?>
$('#recibir').click(function(){
  uni_modal('Confirmar recibido','index.php?url=purchase/reciboCreate/<?= (int)$h['id'] ?>','large');
});
<?php endif; ?>
</script>
