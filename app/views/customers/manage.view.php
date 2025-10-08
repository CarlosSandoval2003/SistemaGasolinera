<div class="container-fluid">
  <form id="customer-form">
    <input type="hidden" name="id" value="<?= $customer['customer_id'] ?? '' ?>">

    <?php
      $full  = $customer['fullname'] ?? '';
      $parts = preg_split('/\s+/', trim($full), 2);
      $first = $parts[0] ?? '';
      $last  = $parts[1] ?? '';
    ?>

    <div class="row g-2">
      <div class="col-md-4">
        <label class="form-label">Nombres</label>
        <input class="form-control form-control-sm" name="first_name" required
               value="<?= htmlspecialchars($first) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Apellidos</label>
        <input class="form-control form-control-sm" name="last_name" required
               value="<?= htmlspecialchars($last) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">NIT</label>
        <input class="form-control form-control-sm" name="nit" required
               value="<?= htmlspecialchars($customer['customer_code'] ?? '') ?>">
        <div class="form-text">Se usa como identificador del cliente.</div>
      </div>

      <div class="col-md-3">
        <label class="form-label">Estado</label>
        <select class="form-select form-select-sm" name="status">
          <option value="1" <?= (!isset($customer['status']) || (int)$customer['status']===1)?'selected':'' ?>>Activo</option>
          <option value="0" <?= (isset($customer['status']) && (int)$customer['status']===0)?'selected':'' ?>>Inactivo</option>
        </select>
      </div>
    </div>
  </form>
</div>

<script>
$('#customer-form').on('submit', function(e){
  e.preventDefault();
  $.ajax({
    url: 'index.php?url=customer/save',
    method: 'POST',
    data: $(this).serialize(),
    dataType: 'json',
    success: function(resp){
      if(resp.status==='success'){
        $('#uni_modal').modal('hide');
        location.reload();
      }else{
        alert(resp.msg||'Error');
      }
    },
    error: function(xhr){
      alert('Error del servidor:\n'+(xhr.responseText||'')); console.error(xhr.responseText);
    }
  });
});
</script>
