<div class="container-fluid">
  <form id="container-form">
    <input type="hidden" name="id" value="<?= $item['container_id'] ?? '' ?>">

    <div class="row g-2">
      <div class="col-md-6">
        <label class="form-label">Nombre</label>
        <input class="form-control form-control-sm"
               name="name"
               required
               maxlength="120"
               placeholder="Nombre del contenedor"
               value="<?= isset($item['name']) ? htmlspecialchars($item['name']) : '' ?>">
      </div>

      <div class="col-md-6">
        <label class="form-label">Tipo de Combustible</label>
        <select class="form-select form-select-sm" name="petrol_type_id" required>
          <option value="" disabled <?= empty($item['petrol_type_id'])?'selected':'' ?>>Seleccione…</option>
          <?php foreach($types as $t): ?>
            <option value="<?= $t['petrol_type_id'] ?>"
              <?= (isset($item['petrol_type_id']) && $item['petrol_type_id']==$t['petrol_type_id'])?'selected':'' ?>>
              <?= htmlspecialchars($t['name']) ?>
            </option>
          <?php endforeach ?>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Capacidad (gal)</label>
        <input type="number" step="0.001" min="0.001"
               class="form-control form-control-sm text-end"
               name="capacity_liters"
               required
               value="<?= isset($item['capacity_liters']) ? number_format($item['capacity_liters'],3,'.','') : '' ?>">
      </div>

      <div class="col-md-4">
        <label class="form-label">Mínimo (gal)</label>
        <input type="number" step="0.001" min="0"
               class="form-control form-control-sm text-end"
               name="min_level_liters"
               required
               value="<?= isset($item['min_level_liters']) ? number_format($item['min_level_liters'],3,'.','') : '' ?>">
      </div>

      <div class="col-md-4">
        <label class="form-label">Estado</label>
        <?php $st = isset($item['status']) ? (int)$item['status'] : 1; ?>
        <select class="form-select form-select-sm" name="status" required>
          <option value="1" <?= $st===1 ? 'selected':'' ?>>Activo</option>
          <option value="0" <?= $st===0 ? 'selected':'' ?>>Inactivo</option>
        </select>
      </div>

      <div class="col-12 mt-2">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" value="1" id="is_default" name="is_default"
                 <?= !empty($item['is_default'])?'checked':'' ?>>
          <label class="form-check-label" for="is_default">Usar como contenedor por defecto para este combustible</label>
        </div>
        
      </div>
    </div>
  </form>
</div>

<script>
$('#uni_modal .modal-footer').html(`
  <button type="submit" form="container-form" class="btn btn-primary rounded-0">
    <i class="fa fa-save"></i> Guardar
  </button>
  <button type="button" class="btn btn-secondary rounded-0" data-bs-dismiss="modal">
    <i class="fa fa-times"></i> Cancelar
  </button>
`);

$('#container-form').off('submit').on('submit', function(e){
  e.preventDefault();
  if (!this.reportValidity()) return;

  const cap = parseFloat(this.capacity_liters.value || '0');
  const min = parseFloat(this.min_level_liters.value || '0');
  if (cap <= 0) { alert('La capacidad debe ser mayor a 0.'); return; }
  if (min < 0)  { alert('El mínimo no puede ser negativo.'); return; }
  if (min > cap){ alert('El mínimo no puede ser mayor que la capacidad.'); return; }

  $('#uni_modal button').prop('disabled', true);

  $.ajax({
    url:'index.php?url=containers/save',
    method:'POST',
    data: $(this).serialize(),
    dataType:'json',
    success: resp=>{
      if(resp.status==='success'){
        $('#uni_modal').one('hidden.bs.modal', ()=> location.reload());
        setTimeout(()=> $('#uni_modal').modal('hide'), 120);
      } else {
        alert(resp.msg||'Error al guardar.');
        $('#uni_modal button').prop('disabled', false);
      }
    },
    error: ()=> {
      alert('Error del servidor');
      $('#uni_modal button').prop('disabled', false);
    }
  });
});
</script>
