<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title mb-0">Clientes</h3>
  </div>

  <div class="card-body">
    <table class="table table-hover table-striped table-bordered align-middle">
      <colgroup>
        <col width="5%">
        <col width="20%">
        <col width="35%">
        <col width="15%">
        <col width="25%">
      </colgroup>
      <thead>
        <tr>
          <th class="text-center">#</th>
          <th class="text-center">NIT</th>
          <th>Cliente</th>
          <th class="text-center">Estado</th>
          <th class="text-center">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php $i=1; foreach($customers as $c): ?>
          <?php
            $full  = $c['fullname'] ?? '';
            $parts = preg_split('/\s+/', trim($full), 2);
            $first = $parts[0] ?? '';
            $last  = $parts[1] ?? '';
          ?>
          <tr>
            <td class="text-center"><?= $i++ ?></td>
            <td class="text-center"><?= htmlspecialchars($c['customer_code'] ?? '') ?></td>
            <td>
              <div class="fw-semibold"><?= htmlspecialchars($first) ?></div>
              <div class="text-muted small"><?= htmlspecialchars($last) ?></div>
            </td>
            <td class="text-center">
              <?= ((int)($c['status'] ?? 1) === 1)
                ? "<span class='badge bg-success'>Activo</span>"
                : "<span class='badge bg-secondary'>Inactivo</span>" ?>
            </td>
            <td class="text-center">
              <div class="btn-group btn-group-sm" role="group">
                <button class="btn btn-outline-secondary view_data"  title="Ver"    data-id="<?= $c['customer_id'] ?>"><i class="fa fa-eye"></i></button>
                <button class="btn btn-outline-primary edit_data"    title="Editar" data-id="<?= $c['customer_id'] ?>"><i class="fa fa-edit"></i></button>
                <button class="btn btn-outline-danger delete_data"   title="Eliminar" data-id="<?= $c['customer_id'] ?>" data-name="<?= htmlspecialchars(($c['customer_code'] ?? '').' - '.($c['fullname'] ?? '')) ?>"><i class="fa fa-trash"></i></button>
              </div>
            </td>
          </tr>
        <?php endforeach; if(empty($customers)): ?>
          <tr><td colspan="5" class="text-center">Sin datos</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
$(function(){
  $(document).on('click','.edit_data',function(){
    uni_modal('Editar Cliente', "index.php?url=customer/manage&id=" + $(this).data('id'), 'mid-large');
  });
  $(document).on('click','.view_data',function(){
    uni_modal('Detalle de Cliente', "index.php?url=customer/viewCustomer/" + $(this).data('id'), '');
  });
  $(document).on('click','.delete_data',function(){
    const name = $(this).data('name');
    _conf("¿Eliminar <b>"+name+"</b>?", 'delete_data', [ $(this).data('id') ]);
  });

  $('table').dataTable({
    columnDefs: [{ orderable:false, targets:[4] }]
  });
});

function delete_data(id){
  $('#confirm_modal button').attr('disabled', true);
  $.ajax({
    url:'index.php?url=customer/delete',
    method:'POST',
    data:{ id },
    dataType:'json',
    error: err=>{
      console.error(err);
      alert("Ocurrió un error.");
      $('#confirm_modal button').attr('disabled', false);
    },
    success: resp=>{
      if(resp.status==='success') location.reload();
      else{
        alert(resp.msg||'Error');
        $('#confirm_modal button').attr('disabled', false);
      }
    }
  });
}
</script>
