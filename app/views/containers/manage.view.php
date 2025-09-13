<div class="container-fluid">
  <form id="container-form">
    <input type="hidden" name="id" value="<?= $item['container_id'] ?? '' ?>">
    <div class="row">
      <div class="col-md-6">
        <label class="form-label">Nombre</label>
        <input class="form-control form-control-sm" name="name" required
               value="<?= htmlspecialchars($item['name'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Tipo de Combustible</label>
        <select class="form-select form-select-sm" name="petrol_type_id" required>
          <option value="" disabled <?= empty($item['petrol_type_id'])?'selected':'' ?>></option>
          <?php foreach($types as $t): ?>
            <option value="<?= $t['petrol_type_id'] ?>"
              <?= (isset($item['petrol_type_id']) && $item['petrol_type_id']==$t['petrol_type_id'])?'selected':'' ?>>
              <?= htmlspecialchars($t['name']) ?>
            </option>
          <?php endforeach ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Capacidad (L)</label>
        <input type="number" step="any" class="form-control form-control-sm" name="capacity_liters" required
               value="<?= $item['capacity_liters'] ?? 0 ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Mínimo (L)</label>
        <input type="number" step="any" class="form-control form-control-sm" name="min_level_liters" required
               value="<?= $item['min_level_liters'] ?? 0 ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Estado</label>
        <select class="form-select form-select-sm" name="status">
          <option value="1" <?= (!isset($item['status']) || $item['status']==1)?'selected':'' ?>>Activo</option>
          <option value="0" <?= (isset($item['status']) && $item['status']==0)?'selected':'' ?>>Inactivo</option>
        </select>
      </div>
      <div class="col-md-6 mt-2">
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
$('#container-form').submit(function(e){
  e.preventDefault();
  $.ajax({
    url:'index.php?url=containers/save',
    method:'POST',
    data: $(this).serialize(),
    dataType:'json',
    success: resp=>{
      if(resp.status==='success'){ location.reload(); }
      else alert(resp.msg||'Error');
    },
    error: ()=> alert('Error')
  });
});
</script>
