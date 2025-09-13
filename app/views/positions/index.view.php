<div class="card">
  <div class="card-header d-flex justify-content-between">
    <h3 class="card-title">Puestos</h3>
    <button class="btn btn-dark btn-sm" id="btn-new">Nuevo</button>
  </div>
  <div class="card-body">
    <table class="table table-bordered table-striped">
      <thead><tr><th>#</th><th>Nombre</th><th>Rol sugerido</th><th>Estado</th><th>Acción</th></tr></thead>
      <tbody>
        <?php
$ROLE_LABELS = [
  1 => 'Admin',
  0 => 'Cashier',
  2 => 'Mantenimiento',
  3 => 'Consulta',
  4 => 'Pedido',
  5 => 'Abastecimiento',
];
?>

        <?php $i=1; foreach($positions as $p): ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><?= htmlspecialchars($p['name']) ?></td>
          <td><?= $ROLE_LABELS[$p['suggested_role']] ?? 'Desconocido' ?></td>
          <td><?= $p['status']?'<span class="badge bg-success">Activo</span>':'<span class="badge bg-secondary">Inactivo</span>' ?></td>
          <td>
            <div class="btn-group btn-group-sm">
              <button class="btn btn-primary edit" data-id="<?= $p['position_id'] ?>">Editar</button>
              <button class="btn btn-danger del" data-id="<?= $p['position_id'] ?>" data-name="<?= htmlspecialchars($p['name']) ?>">Eliminar</button>
            </div>
          </td>
        </tr>
        <?php endforeach; if(empty($positions)): ?>
        <tr><td colspan="5" class="text-center">Sin datos</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<script>
$('#btn-new').click(()=> uni_modal('Nuevo Puesto','index.php?url=position/manage','modal-md'));
$('.edit').click(function(){ uni_modal('Editar Puesto','index.php?url=position/manage&id='+$(this).data('id'),'modal-md') });
$('.del').click(function(){
  _conf('¿Eliminar <b>'+$(this).data('name')+'</b>?','do_del',[ $(this).data('id') ]);
});
function do_del(id){
  $.post('index.php?url=position/delete',{id}, resp=>{
    if(resp.status==='success') location.reload();
    else alert(resp.msg||'Error');
  },'json');
}
</script>
