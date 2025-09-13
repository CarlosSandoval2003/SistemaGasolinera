<style>
  #uni_modal .modal-footer{ display:none; }
  .rline { display:flex; justify-content:space-between; }
</style>

<div class="container-fluid">
  <?php if(!$tx): ?>
    <div class="alert alert-danger">Transacción no encontrada.</div>
  <?php else: ?>
    <div id="outprint_receipt">
      <h4 class="text-center mb-3">RECIBO</h4>
      <div class="rline"><span>Recibo No:</span><b><?= htmlspecialchars($tx['receipt_no']) ?></b></div>
      <div class="rline"><span>Fecha:</span><span><?= htmlspecialchars($tx['date_added']) ?></span></div>
      <div class="rline"><span>Cliente:</span><span><?= htmlspecialchars($tx['customer_code'].' - '.$tx['fullname']) ?></span></div>
      <hr>
      <div class="rline"><span>Gasolina:</span><span><?= htmlspecialchars($tx['petrol_name']) ?></span></div>
      <div class="rline"><span>Precio:</span><span><?= number_format($tx['price'],2) ?></span></div>
      <div class="rline"><span>Litros:</span><span><?= number_format($tx['liter'],3) ?></span></div>
      <div class="rline"><span>Cantidad:</span><span><?= number_format($tx['amount'],2) ?></span></div>
      <div class="rline"><span>Descuento %:</span><span><?= number_format($tx['discount'],2) ?></span></div>
      <div class="rline"><span>Total:</span><b><?= number_format($tx['total'],2) ?></b></div>
      <div class="rline"><span>Tipo:</span><span><?= ((int)$tx['type']===1?'Efectivo':'Credito') ?></span></div>
      <?php if((int)$tx['type']===1): ?>
      <div class="rline"><span>Recibido:</span><span><?= number_format($tx['tendered_amount'],2) ?></span></div>
      <div class="rline"><span>Cambio:</span><span><?= number_format($tx['change'],2) ?></span></div>
      <?php endif; ?>
      <hr>
      <div class="rline"><span>Cajero:</span><span><?= htmlspecialchars($tx['cashier'] ?? '') ?></span></div>
    </div>
    <div class="text-end mt-3">
      <button class="btn btn-sm btn-success rounded-0" type="button" id="print_receipt"><i class="fa fa-print"></i> Imprimir </button>
      <button class="btn btn-sm btn-dark rounded-0" type="button" data-bs-dismiss="modal">Cerrar</button>
    </div>
  <?php endif; ?>
</div>

<script>
$(function(){
  $("#print_receipt").click(function(){
    var h = $('head').clone()
    var p = $('#outprint_receipt').clone()
    var el = $('<div>')
    el.append(h)
    el.append(p)
    var nw = window.open("","","width=1200,height=900,left=150")
    nw.document.write(el.html())
    nw.document.close()
    setTimeout(() => { nw.print(); nw.close(); }, 200);
  })
})
</script>
