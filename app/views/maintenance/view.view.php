<style>
  #uni_modal .modal-footer { display:none !important; }
</style>
<div class="container-fluid">
  <div class="col-12">
    <div class="w-100 mb-1">
      <div class="fs-6 text-info"><b>Nombre:</b></div>
      <div class="fs-5 ps-4"><?= isset($type['name']) ? htmlspecialchars($type['name']) : '' ?></div>
    </div>
    <div class="w-100 mb-1">
      <div class="fs-6 text-info"><b>Precio:</b></div>
      <div class="fs-6 ps-4"><?= isset($type['price']) ? number_format($type['price'], 2) : '' ?></div>
    </div>
    <div class="w-100 mb-1">
      <div class="fs-6 text-info"><b>Estado:</b></div>
      <div class="fs-5 ps-4">
        <?php if (isset($type['status']) && $type['status'] == 1): ?>
          <small><span class='badge rounded-pill bg-success'>Activo</span></small>
        <?php else: ?>
          <small><span class='badge rounded-pill bg-danger'>Inactivo</span></small>
        <?php endif; ?>
      </div>
    </div>
    <div class="w-100 d-flex justify-content-end">
      <button class="btn btn-sm btn-dark rounded-0" type="button" data-bs-dismiss="modal">Close</button>
    </div>
  </div>
</div>
