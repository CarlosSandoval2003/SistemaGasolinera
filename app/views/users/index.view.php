<div class="card">
  <div class="card-header d-flex justify-content-between">
    <h3 class="card-title">Users List</h3>
    <div class="card-tools align-middle">
      <button class="btn btn-dark btn-sm py-1 rounded-0" type="button" id="create_new">Add New</button>
    </div>
  </div>
  <div class="card-body">
    <table class="table table-hover table-striped table-bordered">
      <colgroup>
        <col width="5%"><col width="30%"><col width="25%"><col width="25%"><col width="15%">
      </colgroup>
      <thead>
        <tr>
          <th class="text-center p-0">#</th>
          <th class="text-center p-0">Name</th>
          <th class="text-center p-0">Username</th>
          <th class="text-center p-0">Type</th>
          <th class="text-center p-0">Action</th>
        </tr>
      </thead>
      <tbody>
      <?php if (!empty($users)): $i=1; foreach ($users as $row): ?>
        <tr>
          <td class="text-center p-0"><?= $i++ ?></td>
          <td class="py-0 px-1"><?= htmlspecialchars($row['fullname']) ?></td>
          <td class="py-0 px-1"><?= htmlspecialchars($row['username']) ?></td>
          <td class="py-0 px-1">
  <?= htmlspecialchars($roles[(int)$row['type']] ?? 'Desconocido') ?>
</td>


          <td class="text-center py-0 px-1">
            <div class="btn-group" role="group">
              <button type="button" class="btn btn-primary dropdown-toggle btn-sm rounded-0 py-0" data-bs-toggle="dropdown">Action</button>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item edit_data" data-id="<?= $row['user_id'] ?>" href="javascript:void(0)">Edit</a></li>
                <li><a class="dropdown-item delete_data" data-id="<?= $row['user_id'] ?>" data-name="<?= htmlspecialchars($row['fullname']) ?>" href="javascript:void(0)">Delete</a></li>
              </ul>
            </div>
          </td>
        </tr>
      <?php endforeach; else: ?>
        <tr><td class="text-center p-0" colspan="5">No data display.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
$(function(){
  $('#create_new').click(function(){
    uni_modal('Add New User', "index.php?url=user/manage");
  });
  $('.edit_data').click(function(){
    uni_modal('Edit User Details', "index.php?url=user/manage&id=" + $(this).data('id'));
  });
  $('.delete_data').click(function(){
    _conf("Are you sure to delete <b>"+$(this).data('name')+"</b> from list?", 'delete_user', [$(this).data('id')]);
  });
});

function delete_user(id){
  $('#confirm_modal button').attr('disabled', true);
  $.ajax({
    url: 'index.php?url=user/delete',
    method: 'POST',
    data: { id },
    dataType: 'json',
    error: err => {
      console.error(err);
      alert("An error occurred.");
      $('#confirm_modal button').attr('disabled', false);
    },
    success: function(resp){
      if (resp.status === 'success') {
        location.reload();
      } else {
        alert(resp.msg || "An error occurred.");
        $('#confirm_modal button').attr('disabled', false);
      }
    }
  });
}
</script>
