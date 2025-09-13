
<div class="container-fluid">
  <form action="" id="user-form">
    <input type="hidden" name="id" value="<?= $user['user_id'] ?? '' ?>">
    <div class="form-group">
      <label for="fullname" class="control-label">Full Name</label>
      <input type="text" name="fullname" id="fullname" required class="form-control form-control-sm rounded-0"
             value="<?= isset($user['fullname']) ? htmlspecialchars($user['fullname']) : '' ?>">
    </div>
    <div class="form-group">
      <label for="username" class="control-label">Username</label>
      <input type="text" name="username" id="username" required class="form-control form-control-sm rounded-0"
             value="<?= isset($user['username']) ? htmlspecialchars($user['username']) : '' ?>">
    </div>
    <?php $current = isset($user['type']) ? (int)$user['type'] : 0; ?>
<div class="form-group">
  <label for="type" class="control-label">Type</label>
  <select name="type" id="type" class="form-select form-select-sm rounded-0" required>
    <?php foreach ($roles as $val => $label): ?>
      <option value="<?= $val ?>" <?= ($current === $val ? 'selected' : '') ?>>
        <?= htmlspecialchars($label) ?>
      </option>
    <?php endforeach; ?>
  </select>
</div>

    <small class="text-muted">* Si es nuevo, contraseña por defecto: <b>123456</b>.</small>
  </form>
</div>

<script>
$(function(){
  $('#user-form').submit(function(e){
    e.preventDefault();
    $('.pop_msg').remove();
    const _this = $(this);
    const _el = $('<div>').addClass('pop_msg');

    $('#uni_modal button').attr('disabled', true);
    $('#uni_modal button[type="submit"]').text('Submitting form...');

    $.ajax({
      url: 'index.php?url=user/save',
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
        if (resp.status === 'success') {
          _el.addClass('alert alert-success').text(resp.msg);
          $('#uni_modal').on('hide.bs.modal', function(){ location.reload(); });
          if (!"<?= isset($user['user_id']) ?>") _this.get(0).reset();
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
