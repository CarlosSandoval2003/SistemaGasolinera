<style>
    #uni_modal .modal-footer {
        display: none;
    }
</style>

<div class="container-fluid" id="customer-balance_details">
    <?php if (isset($_GET['paid'])): ?>
        <div class="alert alert-success">
            <p class="m-0">Pago agregado con éxito.</p>
        </div>
    <?php endif; ?>

    <div class="col-12">
        <div id="outprint_receipt">
            <div class="row">
                <div class="col-md-6 col-12">
                    <fieldset>
                        <legend class="text-info">Lista de Deudas</legend>
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr class="bg-primary bg-gradient text-light">
                                    <th class="py-1 text-center">Fecha y Hora</th>
                                    <th class="py-1 text-center">Codigo de Transacción</th>
                                    <th class="py-1 text-center">Cantidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $dtotal = 0; ?>
                                <?php if (!empty($debts)): ?>
                                    <?php foreach ($debts as $row): ?>
                                        <tr>
                                            <td class="px-2 py-1"><?= htmlspecialchars($row['formatted_date']) ?></td>
                                            <td class="px-2 py-1"><?= htmlspecialchars($row['receipt_no']) ?></td>
                                            <td class="px-2 py-1 text-end"><?= number_format($row['amount'], 2) ?></td>
                                        </tr>
                                        <?php $dtotal += $row['amount']; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td class="text-center py-1" colspan="3">Sin Datos</td></tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr class='bg-dark bg-gradient text-light'>
                                    <th class="py-1 text-center" colspan="2">Total</th>
                                    <th class="py-1 text-end"><?= number_format($dtotal, 2) ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </fieldset>
                </div>

                <div class="col-md-6 col-12">
                    <fieldset>
                        <legend class="text-info">Lista de Pagos</legend>
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr class="bg-primary bg-gradient text-light">
                                    <th class="py-1 text-center">Fecha y Hora</th>
                                    <th class="py-1 text-center">Código de Pago</th>
                                    <th class="py-1 text-center">Cantidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $ptotal = 0; ?>
                                <?php if (!empty($payments)): ?>
                                    <?php foreach ($payments as $row): ?>
                                        <tr>
                                            <td class="px-2 py-1"><?= htmlspecialchars($row['formatted_date']) ?></td>
                                            <td class="px-2 py-1"><?= htmlspecialchars($row['payment_code']) ?></td>
                                            <td class="px-2 py-1 text-end"><?= number_format($row['amount'], 2) ?></td>
                                        </tr>
                                        <?php $ptotal += $row['amount']; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td class="text-center py-1" colspan="3">Sin Datos</td></tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr class='bg-dark bg-gradient text-light'>
                                    <th class="py-1 text-center" colspan="2">Total</th>
                                    <th class="py-1 text-end"><?= number_format($ptotal, 2) ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </fieldset>
                </div>
            </div>

            <div class="row">
                <div class="fs-5 fw-bold text-center">Balance Restante</div>
                <center><span class="fs-4 fw-bold"><?= number_format(($dtotal - $ptotal), 2) ?></span></center>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-md-12 text-end">
                <button class="btn btn-sm btn-primary me-2 rounded-0" type="button" id="payment"><i class="fa fa-plus"></i> Agregar Nuevo Pago </button>
                <button class="btn btn-sm btn-success me-2 rounded-0" type="button" id="print_receipt"><i class="fa fa-print"></i> Imprimir </button>
                <button class="btn btn-sm btn-dark rounded-0" type="button" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(function(){
        $('#payment').click(function(){
  uni_modal(
    'New Payment of <b><?= $customer['customer_code'] . ' - ' . $customer['fullname'] ?></b>',
    "index.php?url=balance/manage/<?= $customer['customer_id'] ?>",
    ''
  );
});

        $("#print_receipt").click(function(){
            let h = $('head').clone();
            let p = $('#outprint_receipt').clone();
            let el = $('<div>');
            p.find(".col-md-6").css({width:"49%", margin:"0.5%"});
            el.append(h);
            el.append('<h3 class="text-center"><?= $customer['customer_code'] . ' - ' . $customer['fullname'] ?>\'s Balance Report</h3>');
            el.append(p);
            let nw = window.open("","","width=1200,height=900,left=150");
            nw.document.write(el.html());
            nw.document.close();
            setTimeout(() => {
                nw.print();
                nw.close();
            }, 200);
        });
    });
</script>
