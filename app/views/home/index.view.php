<h3>Estación de Gasolina</h3>
<hr>
<div class="col-12">
    <div class="row gx-3 row-cols-4">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <div class="w-100 d-flex align-items-center">
                        <div class="col-auto pe-1">
                            <span class="fa fa-th-list fs-3 text-primary"></span>
                        </div>
                        <div class="col-auto flex-grow-1">
                            <div class="fs-5"><b>Tipo de Gasolina o Petróleo</b></div>
                            <div class="fs-6 text-end fw-bold">
                                <?= number_format($data['petrol_count']) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card">
                <div class="card-body">
                    <div class="w-100 d-flex align-items-center">
                        <div class="col-auto pe-1">
                            <span class="fa fa-user-friends fs-3 text-dark"></span>
                        </div>
                        <div class="col-auto flex-grow-1">
                            <div class="fs-5"><b>Clientes</b></div>
                            <div class="fs-6 text-end fw-bold">
                                <?= number_format($data['customer_count']) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card">
                <div class="card-body">
                    <div class="w-100 d-flex align-items-center">
                        <div class="col-auto pe-1">
                            <span class="fa fa-hands fs-3 text-warning"></span>
                        </div>
                        <div class="col-auto flex-grow-1">
                            <div class="fs-5"><b>Cuentas por Cobrar</b></div>
                            <div class="fs-6 text-end fw-bold">
                                <?= number_format($data['balance'], 2) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card">
                <div class="card-body">
                    <div class="w-100 d-flex align-items-center">
                        <div class="col-auto pe-1">
                            <span class="fa fa-calendar-day fs-3 text-info"></span>
                        </div>
                        <div class="col-auto flex-grow-1">
                            <div class="fs-5"><b>Ventas de Hoy</b></div>
                            <div class="fs-6 text-end fw-bold">
                                <?= number_format($data['today_sales'], 2) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>