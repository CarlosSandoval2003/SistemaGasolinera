<h3>Contenedores</h3>
<hr>
<div class="mb-2">
  <button class="btn btn-dark btn-sm rounded-0" id="btn-new">Nuevo contenedor</button>
  <button class="btn btn-primary btn-sm rounded-0" id="btn-transfer">Transferir</button>
</div>
<table class="table table-bordered table-striped">
  <thead>
    <tr>
      <th>#</th><th>Nombre</th><th>Combustible</th><th>Capacidad (L)</th>
      <th>Stock (L)</th><th>%</th><th>Default</th><th>Estado</th><th>Acción</th>
    </tr>
  </thead>
  <tbody>
    <?php $i=1; foreach($containers as $c): 
        $pct = $c['capacity_liters']>0 ? ($c['qty_liters']/$c['capacity_liters']*100) : 0; ?>
      <tr>
        <td><?= $i++ ?></td>
        <td><?= htmlspecialchars($c['name']) ?></td>
        <td><?= htmlspecialchars($c['petrol_name']) ?></td>
        <td class="text-end"><?= number_format($c['capacity_liters'],2) ?></td>
        <td class="text-end"><?= number_format($c['qty_liters'],2) ?></td>
        <td class="text-end"><?= number_format($pct,1) ?>%</td>
        <td><?= $c['is_default'] ? '<span class="badge bg-success">Sí</span>' : '' ?></td>
        <td><?= $c['status'] ? 'Activo':'Inactivo' ?></td>
        <td>
          <div class="btn-group btn-group-sm">
            <button class="btn btn-info text-light view" data-id="<?= $c['container_id'] ?>">Kardex</button>
            <button class="btn btn-primary edit" data-id="<?= $c['container_id'] ?>">Editar</button>
            <button class="btn btn-danger del" data-id="<?= $c['container_id'] ?>">Eliminar</button>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<script>
$('#btn-new').click(()=> uni_modal('Nuevo contenedor','index.php?url=containers/manage','mid-large'));
$('.edit').click(function(){ uni_modal('Editar contenedor','index.php?url=containers/manage&id='+$(this).data('id'),'mid-large') });
$('.view').click(function(){ uni_modal('Kardex','index.php?url=containers/movements/'+$(this).data('id'),'large') });
$('#btn-transfer').click(()=> uni_modal('Transferencia','index.php?url=containers/transfer','mid-large'));
$('.del').click(function(){
  _conf('¿Eliminar contenedor?', 'do_delete', [$(this).data('id')]);
});
function do_delete(id){
  $.post('index.php?url=containers/delete',{id}, resp=>{
    if(resp.status==='success') location.reload(); else alert(resp.msg||'Error');
  },'json');
}
</script>
