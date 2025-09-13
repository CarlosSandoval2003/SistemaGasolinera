<div class="card h-100 d-flex flex-column">
  <div class="card-header d-flex justify-content-between">
    <h3 class="card-title">Mantenimiento Gasolina</h3>
    <div class="card-tools align-middle">
      <button class="btn btn-dark btn-sm py-1 rounded-0" type="button" id="new_petrol_type">Add New Fuel</button>
      <button class="btn btn-primary btn-sm py-1 rounded-0" type="button" id="new_container">Add Container</button>
      <button class="btn btn-secondary btn-sm py-1 rounded-0" type="button" id="transfer_container">Transfer</button>
    </div>
  </div>

  <div class="card-body flex-grow-1">
    <div class="col-12 h-100">
      <div class="row h-100">
        <!-- LEFT: Petrol Types -->
        <div class="col-md-6 h-100 d-flex flex-column">
          <div class="w-100 d-flex border-bottom border-dark py-1 mb-1">
            <div class="fs-5 col-auto flex-grow-1"><b>Lista Tipos de Gasolina</b></div>
            <div class="col-auto flex-grow-0 d-flex justify-content-end">
              <a href="javascript:void(0)" id="new_petrol_type_btn" class="btn btn-dark btn-sm bg-gradient rounded-2" title="Add Petrol Type">
                <span class="fa fa-plus"></span>
              </a>
            </div>
          </div>
          <div class="h-100 overflow-auto border rounded-1 border-dark">
            <ul class="list-group">
              <?php if (!empty($types)): ?>
                <?php foreach ($types as $row): ?>
                  <li class="list-group-item d-flex align-items-center">
                    <div class="col-8 flex-grow-1">
                      <?= htmlspecialchars($row['name']) ?>
                      <small><span class="text-muted">Precio Actual: </span><?= number_format($row['price'], 2) ?></small>
                    </div>
                    <div class="col-2 pe-2 text-end">
                      <?php if ((int)$row['status'] === 1): ?>
                        <small><span class='badge rounded-pill bg-success'>Activo</span></small>
                      <?php else: ?>
                        <small><span class='badge rounded-pill bg-danger'>Inactivo</span></small>
                      <?php endif; ?>
                    </div>
                    <div class="col-2 d-flex justify-content-end">
                      <a href="javascript:void(0)" class="view_petrol_type btn btn-sm btn-info text-light bg-gradient py-0 px-1 me-1" data-id="<?= $row['petrol_type_id'] ?>" title="Ver">
                        <span class="fa fa-th-list"></span>
                      </a>
                      <a href="javascript:void(0)" class="edit_petrol_type btn btn-sm btn-primary bg-gradient py-0 px-1 me-1" data-id="<?= $row['petrol_type_id'] ?>" data-name="<?= htmlspecialchars($row['name']) ?>" title="Editar">
                        <span class="fa fa-edit"></span>
                      </a>
                      <a href="javascript:void(0)" class="delete_petrol_type btn btn-sm btn-danger bg-gradient py-0 px-1" data-id="<?= $row['petrol_type_id'] ?>" data-name="<?= htmlspecialchars($row['name']) ?>" title="Eliminar">
                        <span class="fa fa-trash"></span>
                      </a>
                    </div>
                  </li>
                <?php endforeach; ?>
              <?php else: ?>
                <li class="list-group-item text-center">Sin datos.</li>
              <?php endif; ?>
            </ul>
          </div>
        </div>
        

        <?php /*
        <!-- RIGHT: Containers -->
        <div class="col-md-6 h-100 d-flex flex-column">
          <div class="w-100 d-flex border-bottom border-dark py-1 mb-1">
            <div class="fs-5 col-auto flex-grow-1"><b>Containers</b></div>
            <div class="col-auto flex-grow-0 d-flex justify-content-end">
              <a href="javascript:void(0)" id="new_container_btn" class="btn btn-primary btn-sm bg-gradient rounded-2" title="Add Container">
                <span class="fa fa-plus"></span>
              </a>
            </div>
          </div>
          <div class="h-100 overflow-auto border rounded-1 border-dark">
            <ul class="list-group">
              <?php if (!empty($containers)): $i=1; ?>
                <?php foreach ($containers as $c): 
                  $pct = ($c['capacity_liters']>0) ? ($c['qty_liters']/$c['capacity_liters']*100) : 0; ?>
                  <li class="list-group-item d-flex align-items-center">
                    <div class="col-7 flex-grow-1">
                      <div class="fw-bold"><?= htmlspecialchars($c['name']) ?></div>
                      <small class="text-muted"><?= htmlspecialchars($c['petrol_name']) ?></small><br>
                      <small>Stock: <b><?= number_format($c['qty_liters'],2) ?> L</b> / Cap: <?= number_format($c['capacity_liters'],2) ?> L (<?= number_format($pct,1) ?>%)</small>
                    </div>
                    <div class="col-3 text-end">
                      <?php if (!empty($c['is_default'])): ?>
                        <span class="badge bg-success">Default</span><br>
                      <?php endif; ?>
                      <small><?= $c['status'] ? 'Active' : 'Inactive' ?></small>
                    </div>
                    <div class="col-2 d-flex justify-content-end">
                      <div class="btn-group btn-group-sm">
                        <a href="javascript:void(0)" class="btn btn-info text-light view_container" title="Kardex" data-id="<?= $c['container_id'] ?>"><i class="fa fa-list"></i></a>
                        <a href="javascript:void(0)" class="btn btn-primary edit_container" title="Edit" data-id="<?= $c['container_id'] ?>"><i class="fa fa-edit"></i></a>
                        <a href="javascript:void(0)" class="btn btn-danger delete_container" title="Delete" data-id="<?= $c['container_id'] ?>"><i class="fa fa-trash"></i></a>
                      </div>
                    </div>
                  </li>
                <?php endforeach; ?>
              <?php else: ?>
                <li class="list-group-item text-center">No containers yet.</li>
              <?php endif; ?>
            </ul>
          </div>
        </div>*/ ?>
        <!-- /RIGHT -->
      </div>
    </div>
  </div>
</div>

<script>
$(function(){
  // ---- PETROL TYPES ----
  $('#new_petrol_type, #new_petrol_type_btn').click(function(){
    uni_modal('Add New Petrol Type',"index.php?url=petroltype/manage",'mid-large');
  });
  $('.edit_petrol_type').click(function(){
    uni_modal('Edit Petrol Type Details',"index.php?url=petroltype/manage&id=" + $(this).data('id'),'mid-large');
  });
  $('.view_petrol_type').click(function(){
    uni_modal('Petrol Type Details',"index.php?url=petroltype/viewPetrol&id=" + $(this).data('id'),'');
  });
  $('.delete_petrol_type').click(function(){
    _conf("Are you sure to delete <b>"+$(this).data('name')+"</b> from Petrol Type List?", 'delete_petrol_type', [$(this).data('id')]);
  });

  // ---- CONTAINERS ----
  $('#new_container, #new_container_btn').click(function(){
    uni_modal('Add Container','index.php?url=containers/manage','mid-large');
  });
  $('#transfer_container').click(function(){
    uni_modal('Transfer Between Containers','index.php?url=containers/transfer','mid-large');
  });
  $('.edit_container').click(function(){
    uni_modal('Edit Container','index.php?url=containers/manage&id='+$(this).data('id'),'mid-large');
  });
  $('.view_container').click(function(){
    uni_modal('Container Kardex','index.php?url=containers/movements/'+$(this).data('id'),'large');
  });
  $('.delete_container').click(function(){
    _conf("Are you sure to delete this container?", 'delete_container', [$(this).data('id')]);
  });
});

function delete_petrol_type(id){
  $('#confirm_modal button').attr('disabled',true);
  $.ajax({
    url:'index.php?url=petroltype/delete',
    method:'POST',
    data:{id:id},
    dataType:'json',
    error:err=>{
      console.error(err);
      alert("An error occurred.");
      $('#confirm_modal button').attr('disabled',false);
    },
    success:function(resp){
      if(resp.status == 'success'){
        location.reload();
      }else{
        alert(resp.msg || "An error occurred.");
        $('#confirm_modal button').attr('disabled',false);
      }
    }
  });
}

function delete_container(id){
  $('#confirm_modal button').attr('disabled',true);
  $.ajax({
    url:'index.php?url=containers/delete',
    method:'POST',
    data:{id:id},
    dataType:'json',
    error:err=>{
      console.error(err);
      alert("An error occurred.");
      $('#confirm_modal button').attr('disabled',false);
    },
    success:function(resp){
      if(resp.status === 'success'){
        location.reload();
      }else{
        alert(resp.msg || "An error occurred.");
        $('#confirm_modal button').attr('disabled',false);
      }
    }
  });
}
</script>
