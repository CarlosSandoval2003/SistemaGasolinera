<style>#uni_modal .modal-footer{display:none!important}</style>
<div class="container-fluid">
  <div class="mb-2">
    <div class="fw-bold">Recibo: <?= htmlspecialchars($h['receipt_no']) ?></div>
    <div>Proveedor: <?= htmlspecialchars($h['supplier_code'] . ' - ' . $h['supplier_name']) ?></div>
    <div>Fecha: <?= htmlspecialchars($h['date']) ?></div>
    <div>Pago: <?= $h['payment_type']==1?'Efectivo':'Credito' ?></div>
  </div>
  <table class="table table-sm table-bordered">
    <thead><tr><th>#</th><th>Gasolina</th><th>Contenedor</th><th class="text-end">Litros</th><th class="text-end">Costo Unidad</th><th class="text-end">Total Linea</th></tr></thead>
    <tbody>
      <?php $i=1; foreach($items as $it): ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><?= htmlspecialchars($it['petrol_name']) ?></td>
          <td><?= htmlspecialchars($it['container_name']) ?></td>
          <td class="text-end"><?= number_format($it['qty_liters'],2) ?></td>
          <td class="text-end"><?= number_format($it['unit_cost'],2) ?></td>
          <td class="text-end"><?= number_format($it['line_total'],2) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr><th colspan="5" class="text-end">TOTAL</th><th class="text-end"><?= number_format($h['total_cost'],2) ?></th></tr>
    </tfoot>
  </table>
  <div class="text-end">
    <button class="btn btn-sm btn-success" id="print"><i class="fa fa-print"></i> Imprimir</button>
    <button class="btn btn-sm btn-dark" data-bs-dismiss="modal">Cerrar</button>
  </div>
</div>
<script>
(function(){
  $('#print').click(function(){
    const h = $('head').clone();
    const p = $('.container-fluid').clone();
    const el = $('<div>').append(h);
    el.append("<h4 class='text-center'>Purchase</h4>");
    el.append(p.find('.text-end').remove()); // quita botones
    const nw = window.open("","","width=1200,height=900,left=150");
    nw.document.write(el.html());
    nw.document.close();
    setTimeout(()=>{ nw.print(); setTimeout(()=>nw.close(),150); }, 200);
  });
})();
</script>
