<div class="container-fluid">
  <form id="purchase-form">
    <div class="row g-2">
      <div class="col-md-4">
        <label class="form-label">Proveedor</label>
        <select name="supplier_id" class="form-select form-select-sm" required>
          <option value="" disabled selected></option>
          <?php foreach($suppliers as $s): ?>
            <option value="<?= $s['supplier_id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
          <?php endforeach ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Fecha</label>
        <input type="date" name="date" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Pago</label>
        <select name="payment_type" class="form-select form-select-sm">
          <option value="1">Efectivo</option>
          <option value="2">Credito</option>
        </select>
      </div>
      <div class="col-md-12">
        <label class="form-label">Notas</label>
        <input name="notes" class="form-control form-control-sm">
      </div>
    </div>

    <hr>
    <div class="table-responsive">
      <table class="table table-sm table-bordered align-middle" id="items">
        <thead class="table-light">
          <tr>
            <th style="width:24%">Gasolina</th>
            <th style="width:26%">Contenedor</th>
            <th class="text-end" style="width:15%">Litros</th>
            <th class="text-end" style="width:15%">Costo Unidad</th>
            <th class="text-end" style="width:15%">Total Linea</th>
            <th style="width:5%"></th>
          </tr>
        </thead>
        <tbody></tbody>
        <tfoot>
          <tr>
            <th colspan="4" class="text-end">TOTAL</th>
            <th class="text-end" id="gt">0.00</th>
            <th></th>
          </tr>
        </tfoot>
      </table>
    </div>
<button type="button" class="btn btn-secondary btn-sm" id="add-line">Agregar ítem</button>
<button type="submit" class="btn btn-primary btn-sm" id="save-purchase">Guardar compra</button>
<button type="button" class="btn btn-dark btn-sm" data-bs-dismiss="modal">Cerrar</button>

  </form>
</div>

<script>
(function(){
  const types = <?= json_encode($types ?? []) ?>;
  const containersAll = <?= json_encode($containers ?? []) ?>;

  const $tb = $('#items tbody');
  function rowTpl(){
    const fuelOptions = ['<option value="" disabled selected></option>']
      .concat(types.map(t=>`<option value="${t.petrol_type_id}">${t.name}</option>`))
      .join('');
    return `<tr>
      <td>
        <select name="petrol_type_id[]" class="form-select form-select-sm fuel" required>
          ${fuelOptions}
        </select>
      </td>
      <td>
        <select name="container_id[]" class="form-select form-select-sm cont" required>
          <option value="" disabled selected></option>
        </select>
      </td>
      <td><input type="number" step="any" name="qty_liters[]" class="form-control form-control-sm qty text-end" value="0"></td>
      <td><input type="number" step="any" name="unit_cost[]" class="form-control form-control-sm cost text-end" value="0"></td>
      <td class="text-end lt">0.00</td>
      <td class="text-center"><button type="button" class="btn btn-sm btn-danger del">&times;</button></td>
    </tr>`;
  }

  function recalc(){
    let gt=0;
    $tb.find('tr').each(function(){
      const q = parseFloat($(this).find('.qty').val()||0);
      const c = parseFloat($(this).find('.cost').val()||0);
      const lt = q*c;
      $(this).find('.lt').text(lt.toFixed(2));
      gt += lt;
    });
    $('#gt').text(gt.toFixed(2));
  }

  function fillContainers($row){
    const pt = parseInt($row.find('.fuel').val()||0,10);
    const list = containersAll.filter(c => c.petrol_type_id==pt && c.status==1);
    const html = ['<option value="" disabled selected></option>']
      .concat(list.map(c=>`<option value="${c.container_id}">${c.name} (stock ${Number(c.qty_liters).toFixed(2)}L)</option>`))
      .join('');
    $row.find('.cont').html(html);
  }

  function addRow(focus=true){
    $tb.append(rowTpl());
    if(focus){
      const $last = $tb.find('tr:last');
      $last.find('.fuel').focus();
    }
  }

  // init
  addRow(false);

  // events de líneas
  $('#items').on('input','.qty,.cost', recalc);
  $('#items').on('change','.fuel', function(){ fillContainers($(this).closest('tr')); });
  $('#items').on('click','.del', function(){
    $(this).closest('tr').remove();
    if($tb.find('tr').length===0) addRow();
    recalc();
  });

  // botón “Agregar ítem” (NO guarda la compra)
  $('#add-line').click(function(){
    addRow();
  });

  // (Opcional) cuando una fila quede “completa”, agrega otra automáticamente
  $('#items').on('change blur', '.cont, .qty, .cost', function(){
    const $tr = $(this).closest('tr');
    const ok = $tr.find('.fuel').val() && $tr.find('.cont').val() &&
               parseFloat($tr.find('.qty').val()||0) > 0;
    if(ok){
      // si es la última fila visible y está completa, agrega una nueva
      if($tr.is(':last-child')) addRow();
    }
    recalc();
  });

  // submit = Guardar compra (igual que antes)
  $('#purchase-form').on('submit', function(e){
    e.preventDefault();
    $.ajax({
      url: 'index.php?url=purchase/save',
      method:'POST',
      data: $(this).serialize(),
      dataType:'json',
      success: resp=>{
        if(resp.status==='success'){
          uni_modal('Purchase', resp.redirect, 'large');
          $('#uni_modal').on('hidden.bs.modal',()=> location.reload());
        }else alert(resp.msg||'Error');
      },
      error: ()=> alert('Error')
    });
  });
})();

</script>
