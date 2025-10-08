<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title mb-0">Empleados</h3>
    <div class="d-flex gap-2">
      <input type="search" id="q" class="form-control form-control-sm" placeholder="Buscar empleado (nombre, DPI, email, código)">
      <button class="btn btn-dark btn-sm" id="btn-new">Nuevo</button>
    </div>
  </div>

  <div class="card-body">
    <div id="results">
      <table class="table table-bordered table-striped align-middle">
        <thead>
          <tr>
            <th>#</th><th>Código</th><th>Nombre</th><th>Puesto</th>
            <th>DPI</th><th>Email</th><th>Teléfono</th>
            <th class="text-end">Salario (Q)</th><th>Estado</th><th>Acción</th>
          </tr>
        </thead>
        <tbody id="emp-tbody">
          <?php $i=1; foreach($employees as $e): ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= htmlspecialchars($e['code']) ?></td>
              <td><?= htmlspecialchars($e['fullname']) ?></td>
              <td><?= htmlspecialchars($e['position_name']) ?></td>
              <td><?= htmlspecialchars($e['dpi']) ?></td>
              <td><?= htmlspecialchars($e['email']) ?></td>
              <td><?= htmlspecialchars($e['phone'] ?? '') ?></td>
              <td class="text-end"><?= number_format($e['salary'],2) ?></td>
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
</div>

<script>
const $q = $('#q');
const $tbody = $('#emp-tbody');

$('#btn-new').click(()=> uni_modal('Nuevo empleado','index.php?url=employee/manage','large'));
$(document).on('click','.edit',function(){ uni_modal('Editar empleado','index.php?url=employee/manage&id='+$(this).data('id'),'large') });
$(document).on('click','.del',function(){
  _conf('¿Eliminar <b>'+$(this).data('name')+'</b>?','do_del',[ $(this).data('id') ]);
});
function do_del(id){
  $.post('index.php?url=employee/delete',{id}, resp=>{
    if(resp.status==='success') location.reload();
    else alert(resp.msg||'Error');
  },'json');
}

// Búsqueda (debounce simple)
let t=null;
$q.on('input', function(){
  clearTimeout(t);
  t = setTimeout(()=>{
    const q = $q.val().trim();
    $.get('index.php?url=employee/search', { q }, function(resp){
      if(resp.status!=='success') return;
      renderRows(resp.data);
    }, 'json');
  }, 250);
});

function renderRows(rows){
  let html='';
  if(!rows || !rows.length){
    html = '<tr><td colspan="10" class="text-center">Sin resultados</td></tr>';
  } else {
    let idx=1;
    rows.forEach(r=>{
      html += `
        <tr>
          <td>${idx++}</td>
          <td>${escapeHtml(r.code||'')}</td>
          <td>${escapeHtml(r.fullname||'')}</td>
          <td><!-- puesto no viene en search lite --></td>
          <td>${escapeHtml(r.dpi||'')}</td>
          <td>${escapeHtml(r.email||'')}</td>
          <td>${escapeHtml(r.phone||'')}</td>
          <td class="text-end"></td>
          <td><!-- estado no viene en search lite --></td>
          <td>
            <div class="btn-group btn-group-sm">
              <button class="btn btn-primary edit" data-id="${r.employee_id}">Editar</button>
              <button class="btn btn-danger del" data-id="${r.employee_id}" data-name="${escapeHtml(r.fullname||'')}">Eliminar</button>
            </div>
          </td>
        </tr>`;
    });
  }
  $tbody.html(html);
}

function escapeHtml(s){
  return (s||'').replace(/[&<>"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m]));
}
</script>
