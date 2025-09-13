<h3>Administrar Cuenta</h3>
<hr>
<div class="col-md-6">
  <form action="" id="user-form">
    <div class="form-group">
      <label for="fullname" class="control-label">Nombre Completo</label>
      <input type="text" name="fullname" id="fullname" required
             class="form-control form-control-sm rounded-0"
             value="<?= htmlspecialchars($user['fullname'] ?? '') ?>">
    </div>
    <div class="form-group">
      <label for="username" class="control-label">Usuario</label>
      <input type="text" name="username" id="username" required
             class="form-control form-control-sm rounded-0"
             value="<?= htmlspecialchars($user['username'] ?? '') ?>">
    </div>
    <div class="form-group">
      <label for="password" class="control-label">Nueva Contraseña</label>
      <input type="password" name="password" id="password"
             class="form-control form-control-sm rounded-0" value="">
    </div>
    <div class="form-group">
      <label for="old_password" class="control-label">Vieja Contraseña</label>
      <input type="password" name="old_password" id="old_password"
             class="form-control form-control-sm rounded-0" value="">
    </div>
    <div class="form-group">
      <small>Deja el campo "Nueva Contraseña" vacío si no quieres cambiar tu contraseña.</small>
    </div>
    <div class="form-group d-flex w-100 justify-content-end">
      <button class="btn btn-sm btn-primary rounded-0 my-1">Update</button>
    </div>
  </form>
</div>

<script>
$(function(){
  $('#user-form').on('submit', function(e){
    e.preventDefault();
    $('.pop_msg').remove();
    const _this = $(this);
    const _el = $('<div>').addClass('pop_msg');

    $.ajax({
      url: 'index.php?url=account/update',
      method: 'POST',
      data: _this.serialize(),
      dataType: 'json',
      error: err => {
        console.error(err);
        _el.addClass('alert alert-danger').text("An error occurred.");
        _this.prepend(_el).hide().show('slow');
      },
      success: function(resp){
        if (resp.status === 'success') {
          _el.addClass('alert alert-success').text(resp.msg);
          _this.prepend(_el).hide().show('slow');
          // opcional: recargar para ver el nombre nuevo en el navbar
          setTimeout(()=>location.reload(), 800);
        } else {
          _el.addClass('alert alert-danger').text(resp.msg || 'Error');
          _this.prepend(_el).hide().show('slow');
        }
      }
    });
  });
});
</script>
