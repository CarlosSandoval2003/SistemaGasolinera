<div class="container-fluid">
  <form id="link-user-form">
    <input type="hidden" name="employee_id" value="<?= $emp['employee_id'] ?>">
    <div class="mb-2">
      <label class="form-label">Empleado</label>
      <input class="form-control form-control-sm" value="<?= htmlspecialchars($emp['fullname']) ?>" readonly>
    </div>
    <div class="mb-2">
      <label class="form-label">Usuario existente</label>
      <select class="form-select form-select-sm" name="user_id" required>
        <option value="" disabled selected></option>
        <?php foreach($users as $u): ?>
          <option value="<?= $u['user_id'] ?>"><?= htmlspecialchars($u['username']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </form>
</div>
<script>
$('#link-user-form').submit(function(e){
  e.preventDefault();
  $.post('index.php?url=employee/doLinkUser', $(this).serialize(), resp=>{
    if(resp.status==='success'){ $('#uni_modal').modal('hide'); location.reload(); }
    else alert(resp.msg||'Error');
  },'json');
});
</script>
