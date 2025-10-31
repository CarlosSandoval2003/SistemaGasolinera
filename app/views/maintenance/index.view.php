<div class="card h-100 d-flex flex-column">
  <div class="card-header d-flex justify-content-between">
    <h3 class="card-title">Mantenimiento de Gasolina</h3>
    <div class="card-tools align-middle">
      <button class="btn btn-dark btn-sm py-1 rounded-0" type="button" id="new_petrol_type">Nueva Gasolina</button>
    </div>
  </div>

  <div class="card-body flex-grow-1">
    <div class="col-12 h-100">
      <div class="row h-100">
        <!-- LEFT: Petrol Types -->
        <div class="col-md-6 h-100 d-flex flex-column">
          <div class="w-100 d-flex border-bottom border-dark py-1 mb-1">
            <div class="fs-5 col-auto flex-grow-1"><b>Lista de tipos de gasolina</b></div>
            <div class="col-auto flex-grow-0 d-flex justify-content-end">
              <a href="javascript:void(0)" id="new_petrol_type_btn" class="btn btn-dark btn-sm bg-gradient rounded-2" title="Agregar gasolina">
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
                      <div><b><?= htmlspecialchars($row['name']) ?></b></div>
                      <small class="text-muted d-block">
                        Compra: Q<?= number_format((float)$row['purchase_price_gal'], 4) ?>/gal &nbsp; | &nbsp;
                        Venta: Q<?= number_format((float)$row['sale_price_gal'], 4) ?>/gal
                      </small>
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
        <!-- /RIGHT (tus contenedores quedan como estaban) -->
      </div>
    </div>
  </div>
</div>

<script>
$(function(){
  // ---- GASOLINA ----
  $('#new_petrol_type, #new_petrol_type_btn').click(function(){
    uni_modal('Nueva Gasolina',"index.php?url=petrolType/manage",'mid-large');
  });
  $('.edit_petrol_type').click(function(){
    uni_modal('Editar Gasolina',"index.php?url=petrolType/manage&id=" + $(this).data('id'),'mid-large');
  });
  $('.view_petrol_type').click(function(){
    uni_modal('Detalle de Gasolina',"index.php?url=petrolType/viewPetrol&id=" + $(this).data('id'),'');
  });
  $('.delete_petrol_type').click(function(){
    _conf("¿Eliminar <b>"+$(this).data('name')+"</b>?", 'delete_petrol_type', [$(this).data('id')]);
  });

  // ---- CONTENEDORES ----
  $('#new_container, #new_container_btn').click(function(){
    uni_modal('Nuevo Contenedor','index.php?url=containers/manage','mid-large');
  });
  $('#transfer_container').click(function(){
    uni_modal('Transferir entre contenedores','index.php?url=containers/transfer','mid-large');
  });
  $('.edit_container').click(function(){
    uni_modal('Editar Contenedor','index.php?url=containers/manage&id='+$(this).data('id'),'mid-large');
  });
  $('.view_container').click(function(){
    uni_modal('Kardex de Contenedor','index.php?url=containers/movements/'+$(this).data('id'),'large');
  });
  $('.delete_container').click(function(){
    _conf("¿Eliminar este contenedor?", 'delete_container', [$(this).data('id')]);
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
      alert("Ocurrió un error.");
      $('#confirm_modal button').attr('disabled',false);
    },
    success:function(resp){
      if(resp.status == 'success'){
        location.reload();
      }else{
        alert(resp.msg || "Ocurrió un error.");
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
      alert("Ocurrió un error.");
      $('#confirm_modal button').attr('disabled',false);
    },
    success:function(resp){
      if(resp.status === 'success'){
        location.reload();
      }else{
        alert(resp.msg || "Ocurrió un error.");
        $('#confirm_modal button').attr('disabled',false);
      }
    }
  });
}
</script>
