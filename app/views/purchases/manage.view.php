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
      <div class="col-md-5">
        <label class="form-label">Notas</label>
        <input name="notes" class="form-control form-control-sm">
      </div>
    </div>

    <hr>
    <div class="table-responsive">
      <table class="table table-sm table-bordered align-middle" id="items">
        <thead class="table-light">
  <tr>
    <th style="width:45%">Gasolina</th>
    <th class="text-end" style="width:20%">Galones (gal)</th>
    <th class="text-end" style="width:20%">Costo (Q/gal)</th>
    <th class="text-end" style="width:15%">Total línea (Q)</th>
    <th style="width:5%"></th>
  </tr>
</thead>

        <tbody></tbody>
        <tfoot>
          <tr>
            <th colspan="3" class="text-end">TOTAL</th>
            <th class="text-end" id="gt">0.00</th>
            <th></th>
          </tr>
        </tfoot>
      </table>
    </div>

    <button type="button" class="btn btn-secondary btn-sm" id="add-line">Agregar ítem</button>
    <button type="submit" class="btn btn-primary btn-sm" id="save-purchase">Guardar orden</button>
    <button type="button" class="btn btn-dark btn-sm" data-bs-dismiss="modal">Cerrar</button>
  </form>
</div>

<script>
(function(){
  // 👉 ahora 'types' debe incluir purchase_price_gal
  const types = <?= json_encode($types ?? []) ?>; // cada item: { petrol_type_id, name, purchase_price_gal, ... }
  const costByType = {};
  (types||[]).forEach(t => costByType[t.petrol_type_id] = parseFloat(t.purchase_price_gal || 0));

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
      <td><input type="number" step="any" name="qty_liters[]" class="form-control form-control-sm qty text-end" value="0" min="0.0001"></td>
      <td><input type="number" step="0.0001" class="form-control form-control-sm cost text-end" value="0" readonly tabindex="-1"></td>
      <td class="text-end lt">0.00</td>
      <td class="text-center"><button type="button" class="btn btn-sm btn-danger del">&times;</button></td>
    </tr>`;
  }

  function recalc(){
    let gt=0;
    $tb.find('tr').each(function(){
      const q = parseFloat($(this).find('.qty').val()||0);        // GALONES
      const c = parseFloat($(this).find('.cost').val()||0);       // Q/gal (desde BD)
      const lt = q*c;
      $(this).find('.lt').text(isFinite(lt)?lt.toFixed(2):'0.00');
      gt += (isFinite(lt)?lt:0);
    });
    $('#gt').text(gt.toFixed(2));
  }

  function setCostFromDB($row){
    const pt = parseInt($row.find('.fuel').val()||0,10);
    const cost = costByType[pt] || 0;
    $row.find('.cost').val(cost.toFixed(4));  // mostrar Q/gal
  }

  function addRow(focus=true){
    $tb.append(rowTpl());
    if(focus){
      $tb.find('tr:last .fuel').focus();
    }
  }

  // init
  addRow(false);

  // events
  $('#items').on('change','.fuel', function(){
    const $tr = $(this).closest('tr');
    setCostFromDB($tr);
    recalc();
  });
  $('#items').on('input','.qty', recalc);

  $('#items').on('click','.del', function(){
    $(this).closest('tr').remove();
    if($tb.find('tr').length===0) addRow();
    recalc();
  });

  $('#add-line').click(()=> addRow());

  // auto-add last row on complete
  $('#items').on('change blur', '.fuel, .qty', function(){
    const $tr = $(this).closest('tr');
    const ok = $tr.find('.fuel').val() && parseFloat($tr.find('.qty').val()||0) > 0;
    if(ok && $tr.is(':last-child')) addRow();
    recalc();
  });

  // submit
  $('#purchase-form').on('submit', function(e){
    e.preventDefault();
    $.ajax({
      url: 'index.php?url=purchase/save',
      method:'POST',
      data: $(this).serialize(),   // el backend IGNORA cualquier costo y usa BD
      dataType:'json',
      success: resp=>{
        if(resp.status==='success'){
          uni_modal('Orden de compra', resp.redirect, 'large');
          $('#uni_modal').on('hidden.bs.modal',()=> location.reload());
        }else alert(resp.msg||'Error');
      },
      error: ()=> alert('Error')
    });
  });
})();
</script>
