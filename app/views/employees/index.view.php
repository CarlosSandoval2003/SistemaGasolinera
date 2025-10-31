<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title mb-0">Empleados</h3>
    <div class="d-flex gap-2">
      <input type="search" id="q" class="form-control form-control-sm" placeholder="Buscar empleado (nombre, DPI, email, código)">
      <button class="btn btn-dark btn-sm" id="btn-new" data-bs-toggle="tooltip" title="Nuevo empleado">
        <i class="fa fa-plus"></i>
      </button>
    </div>
  </div>

  <div class="card-body">
    <div id="results">
      <table class="table table-bordered table-striped align-middle">
        <thead>
          <tr>
            <th>#</th><th>Código</th><th>Nombre</th><th>Puesto</th>
            <th>DPI</th><th>Email</th><th>Teléfono</th>
            <th class="text-end">Salario (Q)</th><th>Estado</th><th class="text-center">Acción</th>
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
              <td class="text-end"><?= number_format((float)$e['salary'],2) ?></td>
              <td><?= ($e['status']??0)?'<span class="badge bg-success">Activo</span>':'<span class="badge bg-secondary">Inactivo</span>' ?></td>
              <td class="text-center">
                <div class="btn-group btn-group-sm">
                  <button class="btn btn-outline-primary edit"
                          data-id="<?= (int)$e['employee_id'] ?>"
                          data-bs-toggle="tooltip" title="Editar">
                    <i class="fa fa-pen"></i>
                  </button>
                  <button class="btn btn-outline-danger del"
                          data-id="<?= (int)$e['employee_id'] ?>"
                          data-name="<?= htmlspecialchars($e['fullname']) ?>"
                          data-bs-toggle="tooltip" title="Eliminar">
                    <i class="fa fa-trash"></i>
                  </button>
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
(function(){
  const $q = $('#q');
  const $tbody = $('#emp-tbody');

  // Init tooltips (Bootstrap 5)
  function initTooltips(scope){
    const els = (scope||document).querySelectorAll('[data-bs-toggle="tooltip"]');
    els.forEach(el => new bootstrap.Tooltip(el));
  }
  initTooltips(document);

  // Botones
  $('#btn-new').on('click', ()=> {
    uni_modal('Nuevo empleado','index.php?url=employee/manage','large');
  });

  $(document).on('click','.edit', function(){
    uni_modal('Editar empleado','index.php?url=employee/manage&id='+$(this).data('id'),'large');
  });

  $(document).on('click','.del', function(){
    _conf('¿Eliminar <b>'+$(this).data('name')+'</b>?','do_del',[ $(this).data('id') ]);
  });

  window.do_del = function(id){
    $.post('index.php?url=employee/delete',{id}, resp=>{
      if(resp.status==='success') location.reload();
      else alert(resp.msg||'Error');
    },'json').fail(()=> alert('Error de red'));
  };

  // Búsqueda (debounce simple, server-side lite)
  let t=null;
  $q.on('input', function(){
    clearTimeout(t);
    t = setTimeout(()=>{
      const q = $q.val().trim();
      $.get('index.php?url=employee/search', { q }, function(resp){
        if(resp.status!=='success') return;
        renderRows(resp.data);
      }, 'json').fail(()=>{/* silencioso */});
    }, 250);
  });

  function renderRows(rows){
    let html='';
    if(!rows || !rows.length){
      html = '<tr><td colspan="10" class="text-center">Sin resultados</td></tr>';
    } else {
      let idx=1;
      rows.forEach(r=>{
        // En búsqueda lite a veces no viene puesto/estado/salario -> dejamos celdas en blanco
        html += `
          <tr>
            <td>${idx++}</td>
            <td>${escapeHtml(r.code||'')}</td>
            <td>${escapeHtml(r.fullname||'')}</td>
            <td>${escapeHtml(r.position_name||'')}</td>
            <td>${escapeHtml(r.dpi||'')}</td>
            <td>${escapeHtml(r.email||'')}</td>
            <td>${escapeHtml(r.phone||'')}</td>
            <td class="text-end">${r.salary!==undefined ? Number(r.salary).toFixed(2) : ''}</td>
            <td>${
              r.status!==undefined
                ? (Number(r.status)===1 ? "<span class='badge bg-success'>Activo</span>" : "<span class='badge bg-secondary'>Inactivo</span>")
                : ''
            }</td>
            <td class="text-center">
              <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-primary edit"
                        data-id="${Number(r.employee_id)||0}"
                        data-bs-toggle="tooltip" title="Editar">
                  <i class="fa fa-pen"></i>
                </button>
                <button class="btn btn-outline-danger del"
                        data-id="${Number(r.employee_id)||0}"
                        data-name="${escapeHtml(r.fullname||'')}"
                        data-bs-toggle="tooltip" title="Eliminar">
                  <i class="fa fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>`;
      });
    }
    $tbody.html(html);
    initTooltips($tbody[0]); // re-inicializa tooltips después de re-render
  }

  function escapeHtml(s){
    return (s||'').replace(/[&<>"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m]));
  }
})();
</script>
