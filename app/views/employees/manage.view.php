<div class="container-fluid">
  <form id="employee-form">
    <input type="hidden" name="id" value="<?= $employee['employee_id'] ?? '' ?>">
    <div class="row g-2">
      <div class="col-md-4">
        <label class="form-label">Nombres</label>
        <input class="form-control form-control-sm" name="first_name" required value="<?= htmlspecialchars($employee['first_name'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Apellidos</label>
        <input class="form-control form-control-sm" name="last_name" required value="<?= htmlspecialchars($employee['last_name'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Puesto</label>
        <select class="form-select form-select-sm" name="position_id" required>
          <option value="" disabled <?= empty($employee['position_id'])?'selected':'' ?>></option>
          <?php foreach($positions as $p): ?>
            <option value="<?= $p['position_id'] ?>" <?= (isset($employee['position_id']) && $employee['position_id']==$p['position_id'])?'selected':'' ?>>
              <?= htmlspecialchars($p['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">DPI</label>
        <input class="form-control form-control-sm" name="dpi" required value="<?= htmlspecialchars($employee['dpi'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Email</label>
        <input type="email" class="form-control form-control-sm" name="email" required value="<?= htmlspecialchars($employee['email'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Teléfono</label>
        <input class="form-control form-control-sm" name="phone" value="<?= htmlspecialchars($employee['phone'] ?? '') ?>">
      </div>
      <div class="col-md-8">
        <label class="form-label">Dirección</label>
        <input class="form-control form-control-sm" name="address" value="<?= htmlspecialchars($employee['address'] ?? '') ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label">Fecha ingreso</label>
        <input type="date" class="form-control form-control-sm" name="hire_date" value="<?= htmlspecialchars($employee['hire_date'] ?? '') ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label">Salario</label>
        <input type="number" step="0.01" class="form-control form-control-sm text-end" name="salary" value="<?= htmlspecialchars($employee['salary'] ?? '0.00') ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Estado</label>
        <select class="form-select form-select-sm" name="status">
          <option value="1" <?= (!isset($employee['status']) || $employee['status']==1)?'selected':'' ?>>Activo</option>
          <option value="0" <?= (isset($employee['status']) && $employee['status']==0)?'selected':'' ?>>Inactivo</option>
        </select>
      </div>
    </div>
  </form>
</div>
<script>
$('#employee-form').submit(function(e){
  e.preventDefault();
  $.ajax({
    url: 'index.php?url=employee/save',
    method: 'POST',
    data: $(this).serialize(),
    dataType: 'json',
    success: function(resp){
      if(resp.status==='success'){ $('#uni_modal').modal('hide'); location.reload(); }
      else alert(resp.msg||'Error');
    },
    error: function(xhr){
      // útil para ver el PHP error si aparece
      alert('Error del servidor:\n' + (xhr.responseText || ''));
      console.error(xhr.responseText);
    }
  });
});

</script>
