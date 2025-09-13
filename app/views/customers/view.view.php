<style>
    #uni_modal .modal-footer {
        display: none !important;
    }
</style>

<div class="container-fluid">
    <div class="col-12">
        <div class="w-100 mb-1">
            <div class="fs-6"><b>Código de Cliente:</b></div>
            <div class="fs-5 ps-4"><?= $customer['customer_code'] ?? '' ?></div>
        </div>
        <div class="w-100 mb-1">
            <div class="fs-6"><b>Cliente:</b></div>
            <div class="fs-5 ps-4"><?= $customer['fullname'] ?? '' ?></div>
        </div>
        <div class="w-100 mb-1">
            <div class="fs-6"><b>Email:</b></div>
            <div class="fs-6 ps-4"><?= $customer['email'] ?? '' ?></div>
        </div>
        <div class="w-100 mb-1">
            <div class="fs-6"><b>Contacto:</b></div>
            <div class="fs-6 ps-4"><?= $customer['contact'] ?? '' ?></div>
        </div>
        <div class="w-100 mb-1">
            <div class="fs-6"><b>Dirección:</b></div>
            <div class="fs-6 ps-4"><?= $customer['address'] ?? '' ?></div>
        </div>
        <div class="w-100 mb-1">
            <div class="fs-6"><b>Estado:</b></div>
            <div class="fs-5 ps-4">
                <?php if (isset($customer['status']) && $customer['status'] == 1): ?>
                    <small><span class='badge rounded-pill bg-success'>Activo</span></small>
                <?php else: ?>
                    <small><span class='badge rounded-pill bg-danger'>Inactivo</span></small>
                <?php endif; ?>
            </div>
        </div>
        <div class="w-100 d-flex justify-content-end">
            <button class="btn btn-sm btn-dark rounded-0" type="button" data-bs-dismiss="modal">Cerrar</button>
        </div>
    </div>
</div>
