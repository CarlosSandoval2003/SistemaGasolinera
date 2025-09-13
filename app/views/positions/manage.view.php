
<div class="container-fluid">
  <form id="position-form">
    <input type="hidden" name="id" value="<?= $position['position_id'] ?? '' ?>">
    <div class="mb-2">
      <label class="form-label">Nombre</label>
      <input class="form-control form-control-sm" name="name" required
             value="<?= htmlspecialchars($position['name'] ?? '') ?>">
    </div>
    <div class="mb-2">
      <label class="form-label">Descripción</label>
      <textarea class="form-control form-control-sm" name="description"><?= htmlspecialchars($position['description'] ?? '') ?></textarea>
    </div>
<div class="mb-2">
  <label class="form-label">Rol sugerido</label>
  <select class="form-select form-select-sm" name="suggested_role" required>
    <option value="0" <?= (isset($position['suggested_role']) && (int)$position['suggested_role']===0)?'selected':'' ?>>Cajero</option>
    <option value="1" <?= (isset($position['suggested_role']) && (int)$position['suggested_role']===1)?'selected':'' ?>>Admin</option>
    <option value="2" <?= (isset($position['suggested_role']) && (int)$position['suggested_role']===2)?'selected':'' ?>>Mantenimiento</option>
    <option value="3" <?= (isset($position['suggested_role']) && (int)$position['suggested_role']===3)?'selected':'' ?>>Consulta</option>
    <option value="4" <?= (isset($position['suggested_role']) && (int)$position['suggested_role']===4)?'selected':'' ?>>Pedido</option>
    <option value="5" <?= (isset($position['suggested_role']) && (int)$position['suggested_role']===5)?'selected':'' ?>>Abastecimiento</option>
  </select>
</div>

    <div class="mb-3">
      <label class="form-label">Estado</label>
      <select class="form-select form-select-sm" name="status">
        <option value="1" <?= (!isset($position['status']) || $position['status']==1)?'selected':'' ?>>Activo</option>
        <option value="0" <?= (isset($position['status']) && $position['status']==0)?'selected':'' ?>>Inactivo</option>
      </select>
    </div>

    <!-- Botones -->
    <div class="d-flex justify-content-end gap-2">
      <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
      <button type="button" class="btn btn-dark btn-sm" data-bs-dismiss="modal">Cerrar</button>
    </div>
  </form>
</div>

<script>
$('#position-form').on('submit', function(e){
  e.preventDefault();
  $.ajax({
    url: 'index.php?url=position/save',
    method: 'POST',
    data: $(this).serialize(),
    dataType: 'json'
  })
  .done(resp=>{
    if(resp.status==='success'){
      $('#uni_modal').modal('hide');
      location.reload();
    } else {
      alert(resp.msg || 'Error al guardar');
    }
  })
  .fail((xhr)=>{
    // Para ver el error del servidor (p.ej. SQL/500) y no “nada”
    alert('Error del servidor:\n' + (xhr.responseText || xhr.statusText));
  });
});
</script>
