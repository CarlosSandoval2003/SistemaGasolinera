<style>#uni_modal .modal-footer{display:none!important}</style>
<div class="container-fluid">
  <div class="col-12">
    <div class="w-100 mb-2">
      <div class="fs-6"><b>NIT:</b></div>
      <div class="fs-5 ps-4"><?= htmlspecialchars($customer['customer_code'] ?? '') ?></div>
    </div>
    <div class="w-100 mb-2">
      <div class="fs-6"><b>Cliente:</b></div>
      <div class="fs-5 ps-4"><?= htmlspecialchars($customer['fullname'] ?? '') ?></div>
    </div>
    <div class="w-100 mb-2">
      <div class="fs-6"><b>Estado:</b></div>
      <div class="ps-4">
        <?= (isset($customer['status']) && (int)$customer['status']===1)
            ? "<span class='badge bg-success'>Activo</span>"
            : "<span class='badge bg-secondary'>Inactivo</span>" ?>
      </div>
    </div>
    <div class="text-end">
      <button class="btn btn-sm btn-dark rounded-0" data-bs-dismiss="modal">Cerrar</button>
    </div>
  </div>
</div>
