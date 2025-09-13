<?php
// $rows, $dfrom, $dto llegan del controlador
?>
<div class="card">
  <div class="card-header d-flex justify-content-between">
    <h3 class="card-title">Sales Report</h3>
  </div>
  <div class="card-body">
    <h5>Filter</h5>
    <div class="row align-items-end">
      <div class="form-group col-md-2">
        <label for="date_from" class="control-label">Date From</label>
        <input type="date" id="date_from" value="<?= htmlspecialchars($dfrom) ?>" class="form-control rounded-0">
      </div>
      <div class="form-group col-md-2">
        <label for="date_to" class="control-label">Date To</label>
        <input type="date" id="date_to" value="<?= htmlspecialchars($dto) ?>" class="form-control rounded-0">
      </div>
      <div class="form-group col-md-4 d-flex">
        <div class="col-auto">
          <button class="btn btn-primary rounded-0" id="filter" type="button"><i class="fa fa-filter"></i> Filter</button>
          <button class="btn btn-success rounded-0" id="print" type="button"><i class="fa fa-print"></i> Print</button>
        </div>
      </div>
    </div>
    <hr>
    <div class="clear-fix mb-2"></div>

    <div id="outprint">
      <table class="table table-hover table-striped table-bordered">
        <colgroup>
          <col width="5%"><col width="20%"><col width="25%">
          <col width="25%"><col width="25%"><col width="25%">
        </colgroup>
        <thead>
          <tr>
            <th class="text-center p-0">#</th>
            <th class="text-center p-0">Fecha</th>
            <th class="text-center p-0">Recibo Numero</th>
            <th class="text-center p-0">Info</th>
            <th class="text-center p-0">Detalle de Venta</th>
            <th class="text-center p-0">Monto Total</th>
          </tr>
        </thead>
        <tbody>
        <?php if (!empty($rows)): $i=1; foreach ($rows as $row): ?>
          <tr>
            <td class="text-center p-1"><?= $i++ ?></td>
            <td class="py-1 px-2"><?= date("Y-m-d", strtotime($row['date_added'])) ?></td>
            <td class="py-1 px-2">
              <a href="javascript:void(0)" class="view_data" data-id="<?= $row['transaction_id'] ?>">
                <?= htmlspecialchars($row['receipt_no']) ?>
              </a>
            </td>
            <td class="py-1 px-2">
              <div class="lh-1">
                <span><?= htmlspecialchars($row['petrol']) ?></span><br>
                <span><?= number_format($row['price'],2) ?></span>
              </div>
            </td>
            <td class="py-1 px-2">
              <div class="lh-1">
                <span><span class="text-muted">Liters: </span><?= number_format($row['liter'],2) ?></span><br>
                <span><span class="text-muted">Monto: </span><?= number_format($row['amount'],2) ?></span><br>
                <span><span class="text-muted">Descuento: </span><?= number_format($row['amount'] * ($row['discount'] / 100),2) ?> <small>(<?= $row['discount'] ?>%)</small></span><br>
                <span><span class="text-muted">Tipo: </span><?= ((int)$row['type']===1?'Cash':'Credit') ?></span>
              </div>
            </td>
            <td class="py-1 px-2 text-end"><?= number_format($row['total'],2) ?></td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="6" class="text-center">No Transaction listed in selected date.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
$(function(){
  $('.view_data').click(function(){
    // Reusamos el recibo del POS
    uni_modal('Receipt', "index.php?url=sales/receipt/" + $(this).data('id') + "&view_only=1", '');
  });

  $('#filter').click(function(){
    location.href = "index.php?url=sales_report/index&date_from=" + $('#date_from').val() + "&date_to=" + $('#date_to').val();
  });

  $('#print').click(function(){
    var h = $('head').clone();
    var p = $('#outprint').clone();
    var el = $('<div>').append(h);

    let from = $('#date_from').val();
    let to   = $('#date_to').val();
    let range = from === to ? new Date(from).toDateString() : (new Date(from).toDateString() + " - " + new Date(to).toDateString());

    el.append("<div class='text-center lh-1 fw-bold'>Petrol Station Sales Report<br/>As of<br/>"+range+"</div><hr/>");
    p.find('a').addClass('text-decoration-none');
    el.append(p);

    var nw = window.open("", "", "width=1200,height=900,left=150");
    nw.document.write(el.html());
    nw.document.close();
    setTimeout(() => { nw.print(); setTimeout(()=>nw.close(),150); }, 200);
  });
});
</script>
