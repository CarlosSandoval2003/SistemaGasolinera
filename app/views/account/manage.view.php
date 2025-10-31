<h3>Administrar Cuenta</h3>
<hr>
<div class="col-md-6">
  <form action="" id="passwd-form" autocomplete="off">
    <!-- Solo lectura: NO se envía al backend -->
    <div class="form-group mb-2">
      <label class="control-label">Nombre Completo</label>
      <input type="text"
             class="form-control form-control-sm rounded-0"
             value="<?= htmlspecialchars($user['fullname'] ?? '') ?>"
             readonly disabled>
    </div>
    <div class="form-group mb-3">
      <label class="control-label">Usuario</label>
      <input type="text"
             class="form-control form-control-sm rounded-0"
             value="<?= htmlspecialchars($user['username'] ?? '') ?>"
             readonly disabled>
    </div>

    <!-- Cambio de contraseña -->
    <div class="form-group mb-2">
      <label for="old_password" class="control-label">Contraseña Actual</label>
      <input type="password" name="old_password" id="old_password" required
             class="form-control form-control-sm rounded-0" autocomplete="current-password">
    </div>
    <div class="form-group mb-2">
      <label for="password" class="control-label">Nueva Contraseña</label>
      <input type="password" name="password" id="password" required
             class="form-control form-control-sm rounded-0" autocomplete="new-password"
             minlength="6" maxlength="128">
      <div class="form-text">Mínimo 6 caracteres.</div>
    </div>
    <div class="form-group mb-3">
      <label for="password_confirm" class="control-label">Confirmar Nueva Contraseña</label>
      <input type="password" name="password_confirm" id="password_confirm" required
             class="form-control form-control-sm rounded-0" autocomplete="new-password">
    </div>

    <div class="form-group d-flex w-100 justify-content-end">
      <button class="btn btn-sm btn-primary rounded-0 my-1">Actualizar contraseña</button>
    </div>
  </form>
</div>

<script>
$(function(){
  $('#passwd-form').on('submit', function(e){
    e.preventDefault();
    $('.pop_msg').remove();
    const _this = $(this);
    const _el = $('<div>').addClass('pop_msg');

    const np = $('#password').val();
    const pc = $('#password_confirm').val();
    if(np !== pc){
      _el.addClass('alert alert-danger').text('La confirmación no coincide.');
      _this.prepend(_el).hide().show('slow');
      return;
    }

    $.ajax({
      url: 'index.php?url=account/update',
      method: 'POST',
      data: _this.serialize(), // solo envía old_password, password, password_confirm
      dataType: 'json',
      error: err => {
        console.error(err);
        _el.addClass('alert alert-danger').text("Ocurrió un error.");
        _this.prepend(_el).hide().show('slow');
      },
      success: function(resp){
        if (resp.status === 'success') {
          _el.addClass('alert alert-success').text(resp.msg);
          _this.prepend(_el).hide().show('slow');
          $('#old_password,#password,#password_confirm').val('');
        } else {
          _el.addClass('alert alert-danger').text(resp.msg || 'Error');
          _this.prepend(_el).hide().show('slow');
        }
      }
    });
  });
});
</script>
