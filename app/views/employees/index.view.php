<div class="card">
  <div class="card-header d-flex justify-content-between">
    <h3 class="card-title">Empleados</h3>
    <button class="btn btn-dark btn-sm" id="btn-new">Nuevo</button>
  </div>
  <div class="card-body">
    <table class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>#</th><th>Código</th><th>Nombre</th><th>Puesto</th>
          <th>DPI</th><th>Email</th><th>Salario</th><th>Usuario</th><th>Estado</th><th>Acción</th>
        </tr>
      </thead>
      <tbody>
        <?php $i=1; foreach($employees as $e): ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><?= htmlspecialchars($e['code']) ?></td>
          <td><?= htmlspecialchars($e['fullname']) ?></td>
          <td><?= htmlspecialchars($e['position_name']) ?></td>
          <td><?= htmlspecialchars($e['dpi']) ?></td>
          <td><?= htmlspecialchars($e['email']) ?></td>
          <td class="text-end"><?= number_format($e['salary'],2) ?></td>
          <td>
            <?php if(!empty($e['username'])): ?>
              <span class="badge bg-info text-dark">Usuario: <?= htmlspecialchars($e['username']) ?></span>
            <?php else: ?>
              <button class="btn btn-sm btn-outline-primary create-user" data-id="<?= $e['employee_id'] ?>">Crear usuario</button>
              <button class="btn btn-sm btn-outline-secondary link-user" data-id="<?= $e['employee_id'] ?>">Vincular</button>
            <?php endif; ?>
          </td>
          <td><?= $e['status']?'<span class="badge bg-success">Activo</span>':'<span class="badge bg-secondary">Inactivo</span>' ?></td>
          <td>
            <div class="btn-group btn-group-sm">
              <button class="btn btn-primary edit" data-id="<?= $e['employee_id'] ?>">Editar</button>
              <button class="btn btn-danger del" data-id="<?= $e['employee_id'] ?>" data-name="<?= htmlspecialchars($e['fullname']) ?>">Eliminar</button>
            </div>
          </td>
        </tr>
        <?php endforeach; if(empty($employees)): ?>
        <tr><td colspan="10" class="text-center">Sin datos</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<script>
$('#btn-new').click(()=> uni_modal('Nuevo empleado','index.php?url=employee/manage','large'));
$('.edit').click(function(){ uni_modal('Editar empleado','index.php?url=employee/manage&id='+$(this).data('id'),'large') });
$('.del').click(function(){
  _conf('¿Eliminar <b>'+$(this).data('name')+'</b>?','do_del',[ $(this).data('id') ]);
});
function do_del(id){
  $.post('index.php?url=employee/delete',{id}, resp=>{
    if(resp.status==='success') location.reload();
    else alert(resp.msg||'Error');
  },'json');
}
$('.create-user').click(function(){
  uni_modal('Crear usuario','index.php?url=employee/assignUser/'+$(this).data('id'),'modal-md');
});
$('.link-user').click(function(){
  uni_modal('Vincular usuario','index.php?url=employee/linkUser/'+$(this).data('id'),'modal-md');
});
</script>
