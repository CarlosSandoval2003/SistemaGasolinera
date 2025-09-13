<div class="container-fluid">
  <form id="create-user-form">
    <input type="hidden" name="employee_id" value="<?= $emp['employee_id'] ?>">
    <div class="mb-2">
      <label class="form-label">Empleado</label>
      <input class="form-control form-control-sm" value="<?= htmlspecialchars($emp['fullname']) ?>" readonly>
    </div>
    <div class="mb-2">
      <label class="form-label">Usuario</label>
      <input class="form-control form-control-sm" name="username" required value="<?= htmlspecialchars($suggest) ?>">
    </div>
    <div class="mb-2">
      <label class="form-label">Contraseña temporal</label>
      <input class="form-control form-control-sm" name="password" required value="<?= htmlspecialchars(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'),0,8)) ?>">
    </div>
<div class="mb-2">
  <label class="form-label">Tipo</label>
  <select class="form-select form-select-sm" name="type" required>
    <option value="0" <?= (isset($emp['suggested_role']) && (int)$emp['suggested_role']===0)?'selected':'' ?>>Cajero</option>
    <option value="1" <?= (isset($emp['suggested_role']) && (int)$emp['suggested_role']===1)?'selected':'' ?>>Admin</option>
    <option value="2" <?= (isset($emp['suggested_role']) && (int)$emp['suggested_role']===2)?'selected':'' ?>>Mantenimiento</option>
    <option value="3" <?= (isset($emp['suggested_role']) && (int)$emp['suggested_role']===3)?'selected':'' ?>>Consulta</option>
    <option value="4" <?= (isset($emp['suggested_role']) && (int)$emp['suggested_role']===4)?'selected':'' ?>>Pedido</option>
    <option value="5" <?= (isset($emp['suggested_role']) && (int)$emp['suggested_role']===5)?'selected':'' ?>>Abastecimiento</option>
  </select>
</div>

  </form>
</div>
<script>
$('#create-user-form').submit(function(e){
  e.preventDefault();
  $.post('index.php?url=employee/createUser', $(this).serialize(), resp=>{
    if(resp.status==='success'){ $('#uni_modal').modal('hide'); location.reload(); }
    else alert(resp.msg||'Error');
  },'json');
});
</script>
