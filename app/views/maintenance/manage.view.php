<div class="container-fluid">
  <form action="" id="petrol_type-form">
    <input type="hidden" name="id" value="<?= $type['petrol_type_id'] ?? '' ?>">
    <div class="form-group">
      <label for="name" class="control-label">Nombre</label>
      <input type="text" name="name" autofocus id="name" required class="form-control form-control-sm rounded-0"
             value="<?= isset($type['name']) ? htmlspecialchars($type['name']) : '' ?>">
    </div>
    <div class="form-group">
      <label for="price" class="control-label">Precio</label>
      <input type="number" name="price" id="price" required step="any" class="form-control form-control-sm rounded-0 text-end"
             value="<?= isset($type['price']) ? $type['price'] : '' ?>">
    </div>
    <div class="form-group">
      <label for="status" class="control-label">Estado</label>
      <select name="status" id="status" class="form-select form-select-sm rounded-0" required>
        <option value="1" <?= (isset($type['status']) && $type['status'] == 1) ? 'selected' : '' ?>>Activo</option>
        <option value="0" <?= (isset($type['status']) && $type['status'] == 0) ? 'selected' : '' ?>>Inactivo</option>
      </select>
    </div>
  </form>
</div>

<script>
$(function(){
  $('#petrol_type-form').submit(function(e){
    e.preventDefault();
    $('.pop_msg').remove();
    const _this = $(this);
    const _el = $('<div>').addClass('pop_msg');

    $('#uni_modal button').attr('disabled', true);
    $('#uni_modal button[type="submit"]').text('Submitting form...');

    $.ajax({
      url: 'index.php?url=petroltype/save',
      method: 'POST',
      data: _this.serialize(),
      dataType: 'json',
      error: err => {
        console.error(err);
        _el.addClass('alert alert-danger').text("An error occurred.");
        _this.prepend(_el).hide().show('slow');
        $('#uni_modal button').attr('disabled', false);
        $('#uni_modal button[type="submit"]').text('Save');
      },
      success: function(resp){
        if(resp.status === 'success'){
          _el.addClass('alert alert-success').text(resp.msg);
          $('#uni_modal').on('hide.bs.modal', function(){ location.reload(); });
          _this.get(0).reset();
        } else {
          _el.addClass('alert alert-danger').text(resp.msg);
        }
        _this.prepend(_el).hide().show('slow');
        $('#uni_modal button').attr('disabled', false);
        $('#uni_modal button[type="submit"]').text('Save');
      }
    });
  });
});
</script>
