<div class="container-fluid">
  <form id="position-form">
    <input type="hidden" name="id" value="<?= isset($position['position_id']) ? (int)$position['position_id'] : '' ?>">

    <div class="mb-2">
      <label class="form-label">Nombre</label>
      <input class="form-control form-control-sm"
             name="name"
             required
             maxlength="100"
             placeholder="Nombre del puesto"
             value="<?= isset($position['name']) ? htmlspecialchars($position['name']) : '' ?>">
    </div>

    <div class="mb-2">
      <label class="form-label">Descripción</label>
      <textarea class="form-control form-control-sm"
                name="description"
                maxlength="500"
                placeholder="Descripción (opcional)"><?= isset($position['description']) ? htmlspecialchars($position['description']) : '' ?></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Estado</label>
      <?php $st = isset($position['status']) ? (int)$position['status'] : 1; ?>
      <select class="form-select form-select-sm" name="status" required>
        <option value="1" <?= $st===1 ? 'selected':'' ?>>Activo</option>
        <option value="0" <?= $st===0 ? 'selected':'' ?>>Inactivo</option>
      </select>
    </div>

    <div class="d-flex justify-content-end gap-2">
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
  // --- Quitar botones globales del footer SOLO para este modal ---
  const $modal   = $('#uni_modal');
  const $footer  = $modal.find('.modal-footer');
  const prevHTML = $footer.html();           // guarda lo que tenía
  $footer.empty().addClass('d-none');        // oculta/limpia para evitar el duplicado
  // Al cerrar el modal, restaurar su footer para que otros modales no se afecten
  $modal.one('hidden.bs.modal', function(){
    $footer.removeClass('d-none').html(prevHTML);
  });

  // --- Submit del formulario ---
  $('#position-form').off('submit').on('submit', function(e){
    e.preventDefault();
    if (!this.reportValidity()) return;

    // Deshabilita SOLO los botones del formulario actual
    const $form = $(this);
    $form.find('button').prop('disabled', true);

    $.ajax({
      url: 'index.php?url=position/save',
      method: 'POST',
      data: $form.serialize(),
      dataType: 'json'
    })
    .done(resp=>{
      if(resp.status==='success'){
        // cerrar modal y recargar
          sessionStorage.setItem('pos_saved_msg', 'Registro guardado con éxito.');
$('#uni_modal').one('hidden.bs.modal', ()=> location.reload());
setTimeout(()=> $('#uni_modal').modal('hide'), 120);
      }else{
        alert(resp.msg || 'Error al guardar');
        $form.find('button').prop('disabled', false);
      }
    })
    .fail((xhr)=>{
      alert('Error del servidor:\n' + (xhr.responseText || xhr.statusText));
      $form.find('button').prop('disabled', false);
    });
  });
});
</script>
