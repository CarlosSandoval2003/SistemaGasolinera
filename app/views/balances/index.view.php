<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h3 class="card-title">Balance de Empleados</h3>
    </div>
    <div class="card-body">
        <table class="table table-hover table-striped table-bordered">
            <colgroup>
                <col width="5%">
                <col width="25%">
                <col width="25%">
                <col width="25%">
                <col width="20%">
            </colgroup>
            <thead>
                <tr>
                    <th class="text-center p-0">#</th>
                    <th class="text-center p-0">Codigo de Cliente</th>
                    <th class="text-center p-0">Cliente</th>
                    <th class="text-center p-0">Balance Restante</th>
                    <th class="text-center p-0">Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($customers as $row): ?>
                    <tr>
                        <td class="text-center p-0"><?= $i++ ?></td>
                        <td class="py-1 px-2"><?= htmlspecialchars($row['customer_code']) ?></td>
                        <td class="py-1 px-2"><?= htmlspecialchars($row['fullname']) ?></td>
                        <td class="py-1 px-2 text-end"><?= number_format($row['balance'], 2) ?></td>
                        <td class="text-center py-1 px-2">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-primary dropdown-toggle btn-sm rounded-0 py-0" data-bs-toggle="dropdown">
                                    Acción
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item view_data"
                                           data-id="<?= $row['customer_id'] ?>"
                                           data-title="<?= $row['customer_code'] . ' - ' . $row['fullname'] ?>"
                                           href="javascript:void(0)">Ver Detalles</a></li>
                                    <li><a class="dropdown-item payment_data"
                                           data-id="<?= $row['customer_id'] ?>"
                                           data-title="<?= $row['customer_code'] . ' - ' . $row['fullname'] ?>"
                                           href="javascript:void(0)">Añadir Pago</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    $(function(){
        $('.view_data').click(function(){
            uni_modal('Payable Balances of <b>' + $(this).data('title') + '</b>',
                "index.php?url=balance/viewBalance/" + $(this).data('id'),
                'modal-lg');
        });

        $('.payment_data').click(function(){
            uni_modal('New Payment of <b>' + $(this).data('title') + '</b>',
                "index.php?url=balance/manage/" + $(this).data('id'),
                '');
        });

        $('table td,table th').addClass('align-middle');
        $('table').dataTable({
            columnDefs: [
                { orderable: false, targets: 4 }
            ]
        });
    });
</script>
