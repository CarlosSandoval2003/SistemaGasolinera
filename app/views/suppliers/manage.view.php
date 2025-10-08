<div class="container-fluid">
  <form id="supplier-form">
    <input type="hidden" name="id" value="<?= $supplier['supplier_id'] ?? '' ?>">

    <div class="row g-2">
      <!-- Código (si no hay, lo genera backend) -->
      <input type="hidden" name="code" value="<?= isset($supplier['code']) ? htmlspecialchars($supplier['code']) : '' ?>">

      <div class="col-md-8">
        <label class="form-label">Nombre</label>
        <input class="form-control form-control-sm"
               name="name"
               required
               maxlength="120"
               placeholder="Razón social o nombre comercial"
               value="<?= isset($supplier['name']) ? htmlspecialchars($supplier['name']) : '' ?>">
      </div>

      <div class="col-md-4">
        <label class="form-label">Contacto</label>
        <input class="form-control form-control-sm"
               name="contact"
               inputmode="numeric"
               maxlength="8"
               pattern="\d{8}"
               placeholder="8 dígitos"
               value="<?= isset($supplier['contact']) ? htmlspecialchars($supplier['contact']) : '' ?>">
        <div class="form-text">Debe contener exactamente 8 dígitos.</div>
      </div>

      <div class="col-md-4">
        <label class="form-label">Email</label>
        <input type="email"
               class="form-control form-control-sm"
               name="email"
               maxlength="120"
               placeholder="correo@dominio.com"
               value="<?= isset($supplier['email']) ? htmlspecialchars($supplier['email']) : '' ?>">
      </div>

      <div class="col-md-4">
        <label class="form-label">Estado</label>
        <?php $st = isset($supplier['status']) ? (int)$supplier['status'] : 1; ?>
        <select class="form-select form-select-sm" name="status" required>
          <option value="1" <?= $st===1 ? 'selected':'' ?>>Activo</option>
          <option value="0" <?= $st===0 ? 'selected':'' ?>>Inactivo</option>
        </select>
      </div>

      <div class="col-12">
        <label class="form-label">Dirección</label>
        <input class="form-control form-control-sm"
               name="address"
               maxlength="200"
               placeholder="Dirección (máx 200 caracteres)"
               value="<?= isset($supplier['address']) ? htmlspecialchars($supplier['address']) : '' ?>">
      </div>
    </div>
  </form>
</div>

<script>
// Footer del modal con acciones
$('#uni_modal .modal-footer').html(`
  <button type="submit" form="supplier-form" class="btn btn-primary rounded-0">
    <i class="fa fa-save"></i> Guardar
  </button>
  <button type="button" class="btn btn-secondary rounded-0" data-bs-dismiss="modal">
    <i class="fa fa-times"></i> Cancelar
  </button>
`);

$('#supplier-form').off('submit').on('submit', function(e){
  e.preventDefault();

  if (!this.reportValidity()) return;

  $('#uni_modal button').prop('disabled', true);

  $.post('index.php?url=supplier/save', $(this).serialize(), function(resp){
    if(resp.status==='success'){
      $('#uni_modal').one('hidden.bs.modal', ()=> location.reload());
      setTimeout(()=> $('#uni_modal').modal('hide'), 120);
    }else{
      alert(resp.msg||'Error al guardar.');
      $('#uni_modal button').prop('disabled', false);
    }
  }, 'json').fail(()=>{
    alert('Error de red');
    $('#uni_modal button').prop('disabled', false);
  });
});
</script>
