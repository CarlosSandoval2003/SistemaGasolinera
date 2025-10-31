<h3>Cambiar contraseña (primer uso)</h3>
<hr>
<div class="col-md-5">
  <div class="alert alert-warning">
    Debes cambiar tu contraseña antes de usar el sistema.
  </div>

  <form id="first-change-form">
    <div class="mb-2">
      <label class="form-label">Nueva contraseña</label>
      <input type="password" name="new_password" class="form-control form-control-sm" required minlength="6">
    </div>
    <div class="mb-3">
      <label class="form-label">Confirmar contraseña</label>
      <input type="password" name="confirm_password" class="form-control form-control-sm" required minlength="6">
    </div>

    <div class="d-flex justify-content-end gap-2">
      <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
      <a class="btn btn-secondary btn-sm disabled" tabindex="-1" aria-disabled="true">Cancelar</a>
    </div>
  </form>

  <div id="resp" class="mt-2" style="display:none"></div>
</div>

<script>
$(function(){
  $('#first-change-form').on('submit', function(e){
    e.preventDefault();
    const $btns = $(this).find('button');
    $btns.prop('disabled',true);
    $('#resp').hide().removeClass('alert alert-danger alert-success');

    $.ajax({
      url: 'index.php?url=account/updateFirst',
      method: 'POST',
      data: $(this).serialize(),
      dataType: 'json'
    })
    .done(resp=>{
      if(resp.status==='success'){
        $('#resp').addClass('alert alert-success').text(resp.msg||'OK').show();
        const to = resp.redirect || 'index.php?url=home/index';
        setTimeout(()=> location.href = to, 700);
      } else {
        $('#resp').addClass('alert alert-danger').text(resp.msg||'Error').show();
        $btns.prop('disabled',false);
      }
    })
    .fail(()=> {
      $('#resp').addClass('alert alert-danger').text('Error de red.').show();
      $btns.prop('disabled',false);
    });
  });
});
</script>
