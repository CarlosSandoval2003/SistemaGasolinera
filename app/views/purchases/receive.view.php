<style>#uni_modal .modal-footer{display:none!important}</style>
<div class="container-fluid">
  <div class="mb-2">
    <div class="fw-bold">OC: <?= htmlspecialchars($oc['codigo']) ?></div>
    <div>Proveedor: <?= htmlspecialchars($oc['proveedor']) ?></div>
    <div>Fecha OC: <?= htmlspecialchars($oc['fecha']) ?></div>
  </div>

  <form id="recibo-form">
    <div class="row g-2">
      <div class="col-md-4">
        <label class="form-label">Fecha recibido</label>
        <input type="date" name="fecha" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>" required>
      </div>
      <div class="col-md-8">
        <label class="form-label">Documento proveedor</label>
        <input name="documento_proveedor" class="form-control form-control-sm" placeholder="Factura/Remisión (opcional)">
      </div>
      <div class="col-12">
        <label class="form-label">Observaciones</label>
        <input name="observaciones" class="form-control form-control-sm">
      </div>
    </div>

    <hr>
    <div class="table-responsive">
      <table class="table table-sm table-bordered align-middle">
        <thead class="table-light">
          <tr>
            <th>Gasolina</th>
            <th class="text-end">Solicitado (L)</th>
            <th>Contenedor</th>
            <th class="text-end">Recibido (L)</th>
            <th class="text-end">Costo (Q/L)</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($items as $it): ?>
          <tr>
            <td><?= htmlspecialchars($it['petrol_name']) ?></td>
            <td class="text-end"><?= number_format((float)$it['cantidad_litros'],3) ?></td>
            <td>
              <select name="contenedor_id[]" class="form-select form-select-sm" required>
                <option value="" disabled selected></option>
                <?php foreach(($contenedoresPorTipo[$it['petrol_type_id']] ?? []) as $c): ?>
                  <option value="<?= $c['container_id'] ?>">
                    <?= htmlspecialchars($c['name']) ?><?= isset($c['qty_liters']) ? (' (stock '.number_format((float)$c['qty_liters'],2).'L)') : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </td>
            <td><input type="number" step="any" name="recibido_l[]" class="form-control form-control-sm text-end" value="<?= number_format((float)$it['cantidad_litros'],3,'.','') ?>"></td>
            <td><input type="number" step="any" name="costo_u[]" class="form-control form-control-sm text-end" value="<?= number_format((float)$it['precio_unitario'],4,'.','') ?>"></td>
            <input type="hidden" name="orden_item_id[]" value="<?= (int)$it['id'] ?>">
            <input type="hidden" name="petrol_type_id[]" value="<?= (int)$it['petrol_type_id'] ?>">
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="text-end">
      <button type="submit" class="btn btn-primary btn-sm">Confirmar recibido</button>
      <button type="button" class="btn btn-dark btn-sm" data-bs-dismiss="modal">Cerrar</button>
    </div>
  </form>
</div>
<script>
$('#recibo-form').on('submit', function(e){
  e.preventDefault();
  $.ajax({
    url: 'index.php?url=purchase/reciboStore/<?= (int)$oc['id'] ?>',
    method:'POST',
    data: $(this).serialize(),
    dataType:'json',
    success: resp=>{
      if(resp.status==='success'){
        alert('Recibido confirmado');
        $('#uni_modal').modal('hide');
        location.reload();
      }else alert(resp.msg||'Error');
    },
    error: ()=> alert('Error')
  });
});
</script>
