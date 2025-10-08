<div class="container-fluid">
  <form id="employee-form">
    <input type="hidden" name="id" value="<?= $employee['employee_id'] ?? '' ?>">
    <div class="row g-2">
      <div class="col-md-4">
        <label class="form-label">Nombres</label>
        <input class="form-control form-control-sm" name="first_name" required
               value="<?= htmlspecialchars($employee['first_name'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Apellidos</label>
        <input class="form-control form-control-sm" name="last_name" required
               value="<?= htmlspecialchars($employee['last_name'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Puesto</label>
        <select class="form-select form-select-sm" name="position_id" required>
          <option value="" disabled <?= empty($employee['position_id'])?'selected':'' ?>>Seleccione…</option>
          <?php foreach($positions as $p): ?>
            <option value="<?= $p['position_id'] ?>" <?= (isset($employee['position_id']) && $employee['position_id']==$p['position_id'])?'selected':'' ?>>
              <?= htmlspecialchars($p['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">DPI (13 dígitos)</label>
        <input class="form-control form-control-sm" name="dpi" required
               pattern="\d{13}" maxlength="13"
               value="<?= htmlspecialchars($employee['dpi'] ?? '') ?>">
        <div class="form-text">Solo números, 13 dígitos.</div>
      </div>
      <div class="col-md-4">
        <label class="form-label">Email</label>
        <input type="email" class="form-control form-control-sm" name="email" required
               value="<?= htmlspecialchars($employee['email'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Teléfono (8 dígitos)</label>
        <input class="form-control form-control-sm" name="phone"
               pattern="\d{8}" maxlength="8"
               value="<?= htmlspecialchars($employee['phone'] ?? '') ?>">
        <div class="form-text">Solo números, 8 dígitos.</div>
      </div>

      <div class="col-md-8">
  <label class="form-label">Dirección</label>
  <input class="form-control form-control-sm" name="address"
         value="<?= htmlspecialchars($employee['address'] ?? '') ?>">
</div>

<div class="col-md-2">
  <label class="form-label">Fecha ingreso</label>
  <input type="date" class="form-control form-control-sm" name="hire_date"
         value="<?= htmlspecialchars($employee['hire_date'] ?? '') ?>">
</div>

<!-- SALARIO: más amplio (col-md-6) y EN BLANCO por defecto -->
<div class="col-md-6">
  <label class="form-label">Salario (Q)</label>
  <input
    type="number"
    step="0.01"
    min="0"
    class="form-control form-control-sm"
    name="salary"
    placeholder="0.00"
    value="<?= isset($employee['salary']) && $employee['salary'] !== null ? htmlspecialchars($employee['salary']) : '' ?>"
  >
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
// Validación cliente extra (consistente con el servidor)
$('#employee-form').on('submit', function(e){
  e.preventDefault();

  const dpi = $('[name="dpi"]').val().trim();
  const phone = $('[name="phone"]').val().trim();
  const email = $('[name="email"]').val().trim();

  if(!/^\d{13}$/.test(dpi)){ alert('DPI inválido: deben ser 13 dígitos.'); return; }
  if(phone!=='' && !/^\d{8}$/.test(phone)){ alert('Teléfono inválido: deben ser 8 dígitos.'); return; }
  if(email==='' ){ alert('Email requerido.'); return; }

  $.ajax({
    url: 'index.php?url=employee/save',
    method: 'POST',
    data: $(this).serialize(),
    dataType: 'json',
    success: function(resp){
      if(resp.status==='success'){
        $('#uni_modal').modal('hide');
        location.reload();
      } else {
        alert(resp.msg || 'Error');
      }
    },
    error: function(xhr){
      alert('Error del servidor:\n' + (xhr.responseText || ''));
      console.error(xhr.responseText);
    }
  });
});
</script>
