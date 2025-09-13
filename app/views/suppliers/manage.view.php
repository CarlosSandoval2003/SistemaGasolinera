<div class="container-fluid">
  <form id="supplier-form">
    <input type="hidden" name="id" value="<?= $supplier['supplier_id'] ?? '' ?>">
    <div class="row g-2">
      <div class="col-md-8">
        <label class="form-label">Name</label>
        <input class="form-control form-control-sm" name="name" required value="<?= htmlspecialchars($supplier['name'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Contact</label>
        <input class="form-control form-control-sm" name="contact" value="<?= htmlspecialchars($supplier['contact'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Email</label>
        <input class="form-control form-control-sm" name="email" value="<?= htmlspecialchars($supplier['email'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Status</label>
        <select class="form-select form-select-sm" name="status">
          <option value="1" <?= (!isset($supplier['status']) || $supplier['status']==1)?'selected':'' ?>>Active</option>
          <option value="0" <?= (isset($supplier['status']) && $supplier['status']==0)?'selected':'' ?>>Inactive</option>
        </select>
      </div>
      <div class="col-12">
        <label class="form-label">Address</label>
        <input class="form-control form-control-sm" name="address" value="<?= htmlspecialchars($supplier['address'] ?? '') ?>">
      </div>
    </div>
  </form>
</div>
<script>
  // Pintar botones en el footer del modal y vincularlos al form por atributo "form"
  $('#uni_modal .modal-footer').html(`
    <button type="submit" form="supplier-form" class="btn btn-primary rounded-0">Save</button>
    <button type="button" class="btn btn-secondary rounded-0" data-bs-dismiss="modal">Close</button>
  `);

  $('#supplier-form').off('submit').on('submit', function(e){
    e.preventDefault();
    $.post('index.php?url=supplier/save', $(this).serialize(), function(resp){
      if(resp.status==='success'){
        $('#uni_modal').modal('hide');
        location.reload();
      }else{
        alert(resp.msg||'Error');
      }
    }, 'json').fail(()=>alert('Error de red'));
  });
</script>

