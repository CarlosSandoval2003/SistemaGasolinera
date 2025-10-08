<style>#uni_modal .modal-footer{display:none!important}</style>
<div class="container-fluid">
  <?php if (!$tx): ?>
    <div class="alert alert-danger">Movimiento no encontrado.</div>
  <?php else: ?>
    <div class="mb-2">
      <div><b>Fecha:</b> <?= htmlspecialchars($tx['created_at']) ?></div>
      <div><b>Usuario:</b> <?= htmlspecialchars($tx['user_fullname'] ?? '—') ?></div>
      <div><b>Combustible:</b> <?= htmlspecialchars($tx['petrol_name']) ?></div>
      <div><b>Cantidad (gal):</b> <?= number_format((float)$tx['qty_liters'], 3) ?></div>
      <div><b>Nota:</b> <?= htmlspecialchars($tx['note'] ?? '') ?></div>
    </div>

    <?php if (in_array($tx['kind'], ['TRANSFER_IN','TRANSFER_OUT'], true) && !empty($transfer)): ?>
      <hr>
      <h6>Resumen de transferencia</h6>
      <?php
        $from = null; $to = null;
        foreach($transfer as $t){
          if ($t['kind']==='TRANSFER_OUT') $from = $t;
          if ($t['kind']==='TRANSFER_IN')  $to   = $t;
        }
      ?>
      <div class="row">
        <div class="col-md-6">
          <div class="card card-body p-2">
            <div class="fw-bold mb-1">Desde</div>
            <div><b>Contenedor:</b> <?= htmlspecialchars($from['container_name'] ?? '—') ?></div>
            <div><b>Fecha:</b> <?= htmlspecialchars($from['created_at'] ?? '—') ?></div>
            <div><b>Cantidad (gal):</b> <?= number_format((float)($from['qty_liters'] ?? 0), 3) ?></div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card card-body p-2">
            <div class="fw-bold mb-1">Hacia</div>
            <div><b>Contenedor:</b> <?= htmlspecialchars($to['container_name'] ?? '—') ?></div>
            <div><b>Fecha:</b> <?= htmlspecialchars($to['created_at'] ?? '—') ?></div>
            <div><b>Cantidad (gal):</b> <?= number_format((float)($to['qty_liters'] ?? 0), 3) ?></div>
          </div>
        </div>
      </div>
    <?php else: ?>
      <?php if ($tx['kind'] === 'ADJUST'): ?>
        <hr>
        <div class="alert alert-warning mb-0">
          <i class="fa fa-exclamation-triangle"></i>
          Ajuste de inventario aplicado al contenedor <b><?= htmlspecialchars($tx['container_name']) ?></b>.
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <div class="text-end mt-3">
      <button class="btn btn-dark btn-sm" data-bs-dismiss="modal">Cerrar</button>
    </div>
  <?php endif; ?>
</div>
