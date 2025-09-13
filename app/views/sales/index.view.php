<div class="w-100 h-100 d-flex flex-column">
  <div class="row">
    <div class="col-8"><h3>Transacción</h3></div>
    <div class="col-4 d-flex justify-content-end">
      <button class="btn btn-sm btn-primary rounded-0" id="transaction-save-btn" type="button">Guardar Transacción</button>
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
                          <?= number_format($row['price'],2) ?>
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
              <div class="form-group">
                <label for="customer_id" class="control-label fw-bold text-light">Cliente</label>
                <select name="customer_id" id="customer_id" class="form-select form-select-sm rounded-0" required>
                  <option value="" disabled selected></option>
                  <?php foreach($customers as $c): ?>
                    <option value="<?= $c['customer_id'] ?>"><?= $c['customer_code'].' - '.$c['fullname'] ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label for="liter" class="control-label fw-bold text-light">Litros</label>
                <input type="number" value="0" step="any" name="liter" id="liter" class="form-control form-control-sm rounded-0 text-end" required>
              </div>
              <div class="form-group">
                <label for="amount" class="control-label fw-bold text-light">Cantidad</label>
                <input type="number" value="0" step="any" name="amount" id="amount" class="form-control form-control-sm rounded-0 text-end" required>
              </div>
              <div class="form-group">
                <label for="discount" class="control-label fw-bold text-light">Descuento %</label>
                <input type="number" min="0" max="100" step="any" name="discount" id="discount" class="form-control form-control-sm rounded-0 text-end" value="0" required>
              </div>
              <div class="form-group">
                <label for="total" class="control-label fw-bold text-light">Total</label>
                <input type="number" value="0" step="any" name="total" id="total" class="form-control form-control-sm rounded-0 text-end" readonly>
              </div>
              <div class="form-group">
                <label for="type" class="control-label fw-bold text-light">Tipo de Pago</label>
                <select name="type" id="type" class="form-select form-select-sm rounded-0" required>
                  <option value="1" selected>Efectivo</option>
                  <option value="2">Credito</option>
                </select>
              </div>
              <div class="form-group">
                <label for="tendered_amount" class="control-label fw-bold text-light">Pago Recibido</label>
                <input type="number" step="any" name="tendered_amount" id="tendered_amount" class="form-control form-control-sm rounded-0 text-end" value="0">
              </div>
              <div class="form-group">
                <label for="change" class="control-label fw-bold text-light">Cambio</label>
                <input type="number" step="any" name="change" id="change" class="form-control form-control-sm rounded-0 text-end" value="0" readonly>
              </div>
            </div>
          </div>
        </div>
      </div>

      <input type="hidden" name="petrol_type_id" value="">
      <input type="hidden" name="price" value="">
    </form>
  </div>
</div>

<script>
function calc_total(){
  var amount =  parseFloat($('#amount').val()||0)
  var discount =  parseFloat($('#discount').val()||0)
  var total = discount > 0 ? amount - (amount * (discount / 100)) : amount
  $('#total').val(total)
}
$(function(){
  $('input,select').each(function(){
    if($(this).attr('id') != "search") $(this).attr('disabled',true)
  })
  $('#search').on('input',function(){
    var _search = $(this).val().toLowerCase()
    $('.petrol-item').each(function(){
      var _text = $(this).text().toLowerCase()
      $(this).parent().toggle(_text.includes(_search))
    })
  })
  $('.petrol-item').click(function(){
    $('.petrol-item .bcheck').hide()
    $(this).find('.bcheck').show()
    $('[name="petrol_type_id"]').val($(this).data('id'))
    $('[name="price"]').val($(this).data('price'))
    $('input,select').removeAttr('disabled')
    if($('#type').val() == 2){
      $('#tendered_amount,#change').parent().hide()
      $('#tendered_amount,#change').val('0')
    }
  })
  $('#type').change(function(){
    if($(this).val() == 2){
      $('#tendered_amount,#change').parent().hide().find('input').val('0')
    }else{
      $('#tendered_amount,#change').parent().show()
      $('#tendered_amount,#change').val('0')
    }
  })
  $('#liter').on('input',function(){
    if(!$(this).is(":focus")) return;
    var liter = parseFloat($(this).val()||0);
    var price = parseFloat($('[name="price"]').val()||0);
    $('#amount').val(price * liter)
    calc_total()
  })
  $('#amount').on('input',function(){
    if(!$(this).is(":focus")) return;
    var amount = parseFloat($(this).val()||0);
    var price = parseFloat($('[name="price"]').val()||0);
    $('#liter').val(price > 0 ? (amount / price) : 0)
    calc_total()
  })
  $('#discount').on('input',calc_total)
  $('#tendered_amount').on('input',function(){
    var total = parseFloat($('#total').val()||0)
    var tendered = parseFloat($(this).val()||0)
    $('#change').val(tendered-total)
  })
  $('#transaction-save-btn').click(function(){
    if(($('[name="petrol_type_id"]').val()||0) <= 0){
      alert("Please Fill the form First.")
      return false;
    }
    $('#transaction-form').submit()
  })
  $('#transaction-form').submit(function(e){
    e.preventDefault()
    if(parseFloat($('#change').val()||0) < 0 && $('#type').val() == '1'){
      alert("Tendered Amount is invalid."); $('#tendered_amount').focus(); return false;
    }
    $('#transaction-save-btn').attr('disabled',true)
    $('.pop_msg').remove()
    var _this = $(this)
    var _el = $('<div>').addClass('pop_msg')
    $.ajax({
      url:'index.php?url=sales/save',
      data: new FormData(_this[0]),
      cache: false,
      contentType: false,
      processData: false,
      method: 'POST',
      dataType: 'json',
      error:err=>{
        console.log(err)
        _el.addClass('alert alert-danger').text("An error occurred.")
        _this.prepend(_el).hide().show('slow')
        $('#transaction-save-btn').attr('disabled',false)
      },
      success:function(resp){
        if(resp.status == 'success'){
          setTimeout(() => {
            uni_modal("RECEIPT","index.php?url=sales/receipt/"+resp.transaction_id)
          }, 600);
        }else{
          _el.addClass('alert alert-danger')
        }
        _el.text(resp.msg || 'Error')
        _this.prepend(_el).hide().show('slow')
        $('#transaction-save-btn').attr('disabled',false)
      }
    })
  })
})
</script>
