<div class="container-fluid">
  <form id="customer-form">
    <input type="hidden" name="id" value="<?= isset($customer['customer_id']) ? (int)$customer['customer_id'] : '' ?>">

    <?php
      $full  = $customer['fullname'] ?? '';
      // Intento simple de separar nombres/apellidos (no destructivo si hay 1 palabra)
      $parts = preg_split('/\s+/', trim($full), 2);
      $first = $parts[0] ?? '';
      $last  = $parts[1] ?? '';
      $editing = isset($customer['customer_id']);
    ?>

    <div class="row g-2">
      <div class="col-md-4">
        <label class="form-label">Nombres</label>
        <input class="form-control form-control-sm"
               name="first_name"
               required
               maxlength="80"
               placeholder="Nombres"
               value="<?= htmlspecialchars($first) ?>">
      </div>

      <div class="col-md-4">
        <label class="form-label">Apellidos</label>
        <input class="form-control form-control-sm"
               name="last_name"
               required
               maxlength="80"
               placeholder="Apellidos"
               value="<?= htmlspecialchars($last) ?>">
      </div>

      <div class="col-md-4">
        <label class="form-label">NIT</label>
        <input class="form-control form-control-sm"
               name="nit"
               <?= $editing ? 'readonly disabled' : 'required' ?>
               maxlength="30"
               pattern="[A-Za-z0-9-]{3,30}"
               placeholder="Ej. CF / 1234567-8"
               value="<?= htmlspecialchars($customer['customer_code'] ?? '') ?>">
        <div class="form-text">
          <?= $editing ? 'El NIT no puede modificarse.' : 'Usa letras, números y guiones (3 a 30).' ?>
        </div>
      </div>

      <div class="col-md-3">
        <label class="form-label">Estado</label>
        <?php $st = isset($customer['status']) ? (int)$customer['status'] : 1; ?>
        <select class="form-select form-select-sm" name="status" required>
          <option value="1" <?= $st===1 ? 'selected':'' ?>>Activo</option>
          <option value="0" <?= $st===0 ? 'selected':'' ?>>Inactivo</option>
        </select>
      </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-3">
      <button type="submit" class="btn btn-primary btn-sm">
        <i class="fa fa-save"></i> Guardar
      </button>
      <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
        <i class="fa fa-times"></i> Cancelar
      </button>
    </div>
  </form>
</div>

<script>
$(function(){
  $('#customer-form').off('submit').on('submit', function(e){
    e.preventDefault();

    // Validación HTML5
    if (!this.reportValidity()) return;

    // Evitar doble submit
    $('#uni_modal button').prop('disabled', true);

    $.ajax({
      url: 'index.php?url=customer/save',
      method: 'POST',
      data: $(this).serialize(),
      dataType: 'json'
    })
    .done(function(resp){
      if(resp.status==='success'){
        $('#uni_modal').one('hidden.bs.modal', ()=> location.reload());
        setTimeout(()=> $('#uni_modal').modal('hide'), 120);
      }else{
        alert(resp.msg || 'Error al guardar');
        $('#uni_modal button').prop('disabled', false);
      }
    })
    .fail(function(xhr){
      alert('Error del servidor:\n'+(xhr.responseText||xhr.statusText));
      $('#uni_modal button').prop('disabled', false);
    });
  });
});
</script>
