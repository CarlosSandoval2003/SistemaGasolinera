<div class="container-fluid">
  <form id="user-form">
    <input type="hidden" name="id" value="<?= isset($user['user_id']) ? (int)$user['user_id'] : '' ?>">

    <div class="row g-2">
      <div class="col-md-6">
        <label class="form-label">Nombre completo</label>
        <input type="text" name="fullname" required class="form-control form-control-sm"
               placeholder="Nombre y apellidos"
               value="<?= isset($user['fullname']) ? htmlspecialchars($user['fullname']) : '' ?>">
      </div>

      <div class="col-md-3">
        <label class="form-label">Usuario</label>
        <input type="text" name="username" required class="form-control form-control-sm"
               placeholder="usuario"
               value="<?= isset($user['username']) ? htmlspecialchars($user['username']) : '' ?>">
      </div>

      <div class="col-md-3">
        <label class="form-label">Rol</label>
        <?php $current = isset($user['type']) ? (int)$user['type'] : 0; ?>
        <select name="type" class="form-select form-select-sm" required>
          <?php foreach ($roles as $val => $label): ?>
            <option value="<?= $val ?>" <?= ($current === $val ? 'selected' : '') ?>>
              <?= htmlspecialchars($label) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-3">
        <label class="form-label">Estado</label>
        <?php $st = isset($user['status']) ? (int)$user['status'] : 1; ?>
        <select name="status" class="form-select form-select-sm" required>
          <option value="1" <?= $st===1 ? 'selected':'' ?>>Activo</option>
          <option value="0" <?= $st===0 ? 'selected':'' ?>>Inactivo</option>
        </select>
      </div>

      <div class="col-12">
        <small class="text-muted">
          * Si es un usuario nuevo, la contraseña por defecto será <b>123456</b>.
          Podrás reiniciarla desde la lista.
        </small>
      </div>
    </div>
  </form>
</div>

<script>
$(function(){
  $('#user-form').on('submit', function(e){
    e.preventDefault();
    const $f = $(this);
    $('.pop_msg').remove();
    const $msg = $('<div class="pop_msg mt-2">');
    $('#uni_modal button').prop('disabled', true);

    $.ajax({
      url: 'index.php?url=user/save',
      method: 'POST',
      data: $f.serialize(),
      dataType: 'json',
      error: err => {
        console.error(err);
        $msg.addClass('alert alert-danger').text('Ocurrió un error.');
        $f.append($msg);
        $('#uni_modal button').prop('disabled', false);
      },
      success: resp => {
        if (resp.status === 'success') {
          $msg.addClass('alert alert-success').text(resp.msg);
          $f.append($msg);
          // Cerrar modal automáticamente y luego refrescar la lista
          $('#uni_modal').one('hidden.bs.modal', ()=> location.reload());
          setTimeout(()=> $('#uni_modal').modal('hide'), 200);
        } else {
          $msg.addClass('alert alert-danger').text(resp.msg || 'Error al guardar.');
          $f.append($msg);
          $('#uni_modal button').prop('disabled', false);
        }
      }
    });
  });
});
</script>
