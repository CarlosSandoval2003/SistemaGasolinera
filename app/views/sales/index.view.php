<div class="w-100 h-100 d-flex flex-column">
  <div class="row">
    <div class="col-8"><h3>Transacción</h3></div>
    <div class="col-4 d-flex justify-content-end">
      <button class="btn btn-sm btn-primary rounded-0" id="transaction-save-btn" type="button">Guardar Venta</button>
    </div>
    <div class="clear-fix mb-1"></div><hr>
  </div>

  <style>
    #plist .item,#item-list tr{ cursor:pointer }
    .petrol-item{ transition: transform 10s easein; }
    .petrol-item:hover{ transform:scale(.98); }
  </style>

  <div class="col-12 flex-grow-1">
    <form action="" class="h-100" id="transaction-form">
      <div class="w-100 h-100 mx-0 row row-cols-2 bg-dark">
        <div class="col-8 h-100 pb-2 d-flex flex-column">
          <div><h3 class="text-light">POS</h3></div>
          <div class="flex-grow-1 d-flex flex-column bg-light bg-opacity-50">
            <div class="form-group py-2 d-flex border-bottom col-auto pb-1">
              <label for="search" class="col-auto px-2 fw-bolder text-light">Buscar</label>
              <div class="flex-grow-1 col-auto pe-2">
                <input type="search" autocomplete="off" class="form-control form-control-sm rounded-0" id="search">
              </div>
            </div>
            <div class="col-auto flex-grow-1 overflow-auto">
              <div class="row row-cols-sm-1 row-cols-md-2 row-cols-xl-3 gx-2 gy-2 my-2 mx-1">
                <?php foreach($fuels as $row): ?>
                  <div class="col">
                    <a class="card h-100 rounded-0 petrol-item text-dark text-decoration-none"
                       href="javascript:void(0)"
                       data-id="<?= $row['petrol_type_id'] ?>"
                       data-price="<?= $row['price'] ?>">
                      <div class="card-body rounded-0 d-flex flex-column position-relative">
                        <span class="badge bcheck bottom-0 bg-transparent border border-info rounded-circle position-absolute" style="display:none">
                          <i class="fa fa-check text-info"></i>
                        </span>
                        <div class="fw-bold petrol-item-name col-auto flex-grow-1">
                          <?= htmlspecialchars($row['name']) ?>
                        </div>
                        <div class="text-end fw-bold petrol-item-price col-auto">
                          Q<?= number_format($row['price'],4) ?>/gal
                        </div>
                      </div>
                    </a>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>

        <div class="col-4 h-100 py-2">
          <div class="h-100 d-flex">
            <div class="w-100">

              <!-- CF / NIT -->
              <div class="form-group mb-2">
                <label class="control-label fw-bold text-light d-block">Cliente</label>
                <div class="btn-group" role="group" aria-label="CF o NIT">
                  <input type="radio" class="btn-check" name="cf_or_nit" id="optCF" value="CF" autocomplete="off" checked>
                  <label class="btn btn-sm btn-outline-light" for="optCF">CF</label>

                  <input type="radio" class="btn-check" name="cf_or_nit" id="optNIT" value="NIT" autocomplete="off">
                  <label class="btn btn-sm btn-outline-light" for="optNIT">NIT</label>
                </div>
                <div id="nitRow" class="mt-2" style="display:none">
                  <div class="input-group input-group-sm">
                    <span class="input-group-text">NIT</span>
                    <input type="text" class="form-control" id="nit" name="nit" placeholder="Ingrese NIT">
                    <button class="btn btn-secondary" type="button" id="btnFindNit">Buscar NIT</button>
                  </div>
                  <div class="form-text text-light" id="nitResult" style="min-height:18px;"></div>
                </div>
              </div>

              <!-- Cantidades -->
              <div class="form-group">
                <label for="gallons" class="control-label fw-bold text-light">Galones (gal)</label>
                <input type="number" placeholder="0.000" step="any" min="0.0001" name="gallons" id="gallons" class="form-control form-control-sm rounded-0 text-end" required>
              </div>

              <div class="form-group">
                <label for="amount" class="control-label fw-bold text-light">Importe (Q)</label>
                <input type="number" placeholder="0.00" step="any" name="amount" id="amount" class="form-control form-control-sm rounded-0 text-end" required>
              </div>

              <div class="form-group">
                <label for="total" class="control-label fw-bold text-light">Total (Q)</label>
                <input type="number" value="0" step="any" name="total" id="total" class="form-control form-control-sm rounded-0 text-end" readonly>
              </div>

              <div class="form-group">
                <label for="type" class="control-label fw-bold text-light">Tipo de Pago</label>
                <select name="type" id="type" class="form-select form-select-sm rounded-0" required>
                  <option value="1" selected>Efectivo</option>
                  <option value="3">Tarjeta</option>
                </select>
              </div>

              <!-- Solo efectivo -->
              <div class="form-group" id="cashRow">
                <label for="tendered_amount" class="control-label fw-bold text-light">Pago Recibido (Q)</label>
                <input type="number" step="any" name="tendered_amount" id="tendered_amount"
                       class="form-control form-control-sm rounded-0 text-end" placeholder="0.00">
              </div>
              <div class="form-group" id="changeRow">
                <label for="change" class="control-label fw-bold text-light">Cambio (Q)</label>
                <input type="number" step="any" name="change" id="change"
                       class="form-control form-control-sm rounded-0 text-end" placeholder="0.00" readonly>
              </div>

              <!-- Solo tarjeta -->
              <div class="mt-2" id="btnPayCard" style="display:none">
                <button type="button" class="btn btn-sm btn-outline-info w-100">
                  Pagar con tarjeta
                </button>
              </div>

              <input type="hidden" name="card_auth" id="card_auth" value="">
            </div>
          </div>
        </div>
      </div>

      <input type="hidden" name="petrol_type_id" value="">
      <input type="hidden" name="price" value="">
      <input type="hidden" name="src" id="src" value="gallons">
    </form>
  </div>
</div>

<!-- Modal OK previo a recibo -->
<div class="modal fade" id="ok_sale_modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h6 class="modal-title">Confirmación</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div id="ok_sale_msg" class="lh-sm">
          Venta realizada con éxito.
        </div>
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-primary btn-sm" id="ok_sale_btn">OK</button>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  const round2 = v => isFinite(v) ? Math.round((+v + Number.EPSILON) * 100) / 100 : 0;
  const round3 = v => isFinite(v) ? Math.round((+v + Number.EPSILON) * 1000) / 1000 : 0;

  let lastEdited = null;

  $('input,select,button').not('#search').prop('disabled', true);
  $('#transaction-save-btn').prop('disabled', true);
  $('#btnFindNit').prop('disabled', true);

  $('#search').on('input', function(){
    const q = $(this).val().toLowerCase();
    $('.petrol-item').each(function(){
      $(this).parent().toggle($(this).text().toLowerCase().includes(q));
    });
  });

  $('.petrol-item').click(function(){
    $('.petrol-item .bcheck').hide();
    $(this).find('.bcheck').show();

    $('[name="petrol_type_id"]').val($(this).data('id'));
    $('[name="price"]').val($(this).data('price'));

    $('input,select,button').prop('disabled', false);
    $('#transaction-save-btn').prop('disabled', false);

    $('#optCF').prop('checked', true);
    $('#nitRow').hide();
    $('#btnFindNit').prop('disabled', true);

    applyPaymentVisibility();

    lastEdited = null;
    $('#src').val('gallons');
    $('#gallons,#amount,#total,#tendered_amount,#change').val('');
  });

  $('input[name="cf_or_nit"]').change(function(){
    if($(this).val()==='NIT'){
      $('#nitRow').show();
      $('#btnFindNit').prop('disabled', false);
      $('#nit').focus();
    } else {
      $('#nitRow').hide();
      $('#btnFindNit').prop('disabled', true);
      $('#nitResult').text('');
      $('#nit').val('');
    }
  });

  $('#btnFindNit').click(function(){
    const nit = $('#nit').val().trim();
    if(!nit){ $('#nitResult').text('Ingrese NIT.'); return; }
    $('#nitResult').text('Buscando...');
    $.get('index.php?url=sales/nitLookup', { nit }, function(resp){
      if(resp.status==='ok'){
        $('#nitResult').text(`Encontrado: ${resp.data.fullname}`);
      }else if(resp.status==='not_found'){
        $('#nitResult').text('NIT no registrado en SAT simulado.');
      }else{
        $('#nitResult').text(resp.msg || 'Error al buscar NIT.');
      }
    }, 'json').fail(()=> $('#nitResult').text('Error de red.'));
  });

  function applyPaymentVisibility(){
    const t = $('#type').val();
    if(t === '1'){
      $('#cashRow,#changeRow').show();
      $('#btnPayCard').hide();
    } else if (t === '3'){
      $('#cashRow,#changeRow').hide();
      $('#tendered_amount,#change').val('');
      $('#btnPayCard').show();
    }
  }

  $('#type').on('change', function(){
    applyPaymentVisibility();
    if($('#type').val()==='1'){
      recalcChange();
    }
  });

  $('#gallons').on('input', function(){
    lastEdited = 'gallons';
    $('#src').val('gallons');
    recalcFromGallons();
  });

  $('#amount').on('input', function(){
    lastEdited = 'amount';
    $('#src').val('amount');
    recalcFromAmount();
  });

  $('#tendered_amount').on('input', function(){ recalcChange(); });

  function getPrice(){ return parseFloat($('[name="price"]').val() || 0); }
  function getGallons(){ return parseFloat($('#gallons').val() || 0); }
  function getAmount(){ return parseFloat($('#amount').val() || 0); }

  function recalcFromGallons(){
    const price = getPrice();
    const gal   = getGallons();
    if(gal > 0 && price > 0){
      const amount = round2(gal * price);
      $('#amount').val(amount ? amount.toFixed(2) : '');
      $('#total').val($('#amount').val());
    }else{
      $('#amount,#total').val('');
    }
    if($('#type').val()==='1') recalcChange();
  }

  function recalcFromAmount(){
    const price  = getPrice();
    const amount = getAmount();
    if(amount > 0 && price > 0){
      const gal = round3(amount / price);
      $('#gallons').val(gal ? gal.toFixed(3) : '');
      $('#total').val($('#amount').val());
    }else{
      $('#gallons,#total').val('');
    }
    if($('#type').val()==='1') recalcChange();
  }

  function recalcChange(){
    const t = $('#type').val();
    if(t !== '1') return;
    const total    = parseFloat($('#total').val() || 0);
    const tendered = parseFloat($('#tendered_amount').val() || 0);
    const change   = round2(tendered - total);
    $('#change').val(Number.isFinite(change) ? change.toFixed(2) : '');
  }

  $(document).on('click', '#btnPayCard', function(){
    const gal = parseFloat($('#gallons').val()||0);
    const total = parseFloat($('#total').val()||0);
    if(gal <= 0 || total <= 0){
      alert('Ingrese galones o importe antes de pagar.');
      return;
    }
    uni_modal('Pago con Tarjeta','index.php?url=sales/cardModal','');
  });

  // Botón guardar
  $('#transaction-save-btn').click(function(){
    if(($('[name="petrol_type_id"]').val()||0) <= 0){
      alert("Seleccione gasolina.");
      return false;
    }
    $('#transaction-form').submit();
  });

  // Submit
  $('#transaction-form').submit(function(e){
    e.preventDefault();

    if (lastEdited === 'amount') $('#src').val('amount');
    else if (lastEdited === 'gallons') $('#src').val('gallons');
    else $('#src').val('gallons');

    const gal   = parseFloat($('#gallons').val()||0);
    const total = parseFloat($('#total').val()||0);
    if(gal <= 0){ alert("Galones debe ser > 0."); return; }
    if(total <= 0){ alert("Total debe ser > 0."); return; }

    const payType = $('#type').val();
    if(payType === '1'){
      const change = parseFloat($('#change').val()||0);
      if(change < 0){ alert("Pago recibido menor al total."); $('#tendered_amount').focus(); return; }
    }
    if($('input[name="cf_or_nit"]:checked').val()==='NIT' && !$('#nit').val().trim()){
      alert("Ingrese NIT o cambie a CF."); return;
    }

    $('#transaction-save-btn').attr('disabled',true);
    $('.pop_msg').remove();
    const _this = $(this);
    const _el = $('<div>').addClass('pop_msg');

    $.ajax({
      url:'index.php?url=sales/save',
      data: new FormData(_this[0]),
      cache:false, contentType:false, processData:false,
      method:'POST', dataType:'json',
      error: err=>{
        console.error(err);
        _el.addClass('alert alert-danger').text("Ocurrió un error.");
        _this.prepend(_el).hide().show('slow');
        $('#transaction-save-btn').attr('disabled',false);
      },
      success: resp=>{
        if(resp.status=='success'){
          // 1) Armar mensaje
          let msg = 'Venta realizada con éxito.';
          if (resp.new_customer === true && resp.customer_fullname) {
            msg += '<br><small>Cliente <b>'+ $('<div>').text(resp.customer_fullname).html() + '</b> registrado.</small>';
          }

          // 2) Mostrar modal OK. Al OK => abrir recibo
          $('#ok_sale_msg').html(msg);
          const m = new bootstrap.Modal(document.getElementById('ok_sale_modal'));
          $('#ok_sale_btn').off('click').on('click', function(){
            m.hide();
            setTimeout(()=>{ uni_modal("RECIBO","index.php?url=sales/receipt/"+resp.transaction_id) }, 150);
          });
          m.show();
        }else{
          _el.addClass('alert alert-danger').text(resp.msg || 'Error');
          _this.prepend(_el).hide().show('slow');
          $('#transaction-save-btn').attr('disabled',false);
        }
      }
    });
  });
})();
</script>
