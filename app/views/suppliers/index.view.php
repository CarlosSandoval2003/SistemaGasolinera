<div class="card">
  <div class="card-header d-flex justify-content-between">
    <h3 class="card-title">Suppliers</h3>
    <div><button class="btn btn-dark btn-sm" id="create_new">Add New</button></div>
  </div>
  <div class="card-body">
    <table class="table table-bordered table-striped">
      <thead><tr><th>#</th><th>Code</th><th>Name</th><th>Contact</th><th>Email</th><th>Status</th><th>Action</th></tr></thead>
      <tbody>
        <?php $i=1; foreach($suppliers as $s): ?>
          <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($s['code']) ?></td>
            <td><?= htmlspecialchars($s['name']) ?></td>
            <td><?= htmlspecialchars($s['contact']) ?></td>
            <td><?= htmlspecialchars($s['email']) ?></td>
            <td><?= $s['status']?'<span class="badge bg-success">Active</span>':'<span class="badge bg-secondary">Inactive</span>' ?></td>
            <td>
              <div class="btn-group btn-group-sm">
                <button class="btn btn-primary edit" data-id="<?= $s['supplier_id'] ?>">Edit</button>
                <button class="btn btn-danger del" data-id="<?= $s['supplier_id'] ?>" data-name="<?= htmlspecialchars($s['name']) ?>">Delete</button>
              </div>
            </td>
          </tr>
        <?php endforeach; if(empty($suppliers)): ?>
          <tr><td colspan="7" class="text-center">No data</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<script>
$('#create_new').click(()=> uni_modal('Add Supplier','index.php?url=supplier/manage','mid-large'));
$('.edit').click(function(){ uni_modal('Edit Supplier','index.php?url=supplier/manage&id='+$(this).data('id'),'mid-large') });
$('.del').click(function(){
  _conf('Delete <b>'+$(this).data('name')+'</b>?','do_del',[ $(this).data('id') ]);
});
function do_del(id){
  $.post('index.php?url=supplier/delete',{id}, resp=>{
    if(resp.status==='success') location.reload();
    else alert(resp.msg||'Error');
  },'json');
}
</script>
