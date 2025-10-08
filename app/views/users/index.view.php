<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title mb-0">Usuarios</h3>
    <div class="d-flex gap-2">
      <input type="search" id="q" class="form-control form-control-sm" placeholder="Buscar (nombre, usuario)">
      <select id="fil_role" class="form-select form-select-sm">
        <option value="">Rol (todos)</option>
        <?php foreach($roles as $val=>$label): ?>
          <option value="<?= $val ?>" <?= isset($role) && $role===$val ? 'selected':'' ?>><?= htmlspecialchars($label) ?></option>
        <?php endforeach; ?>
      </select>
      <select id="fil_status" class="form-select form-select-sm">
        <option value="">Estado (todos)</option>
        <option value="1" <?= isset($status) && $status===1 ? 'selected':'' ?>>Activo</option>
        <option value="0" <?= isset($status) && $status===0 ? 'selected':'' ?>>Inactivo</option>
      </select>
      <button class="btn btn-outline-secondary btn-sm" id="btn-filtrar" title="Aplicar filtros">
        <i class="fa fa-filter"></i>
      </button>
      <button class="btn btn-dark btn-sm" id="create_new" title="Nuevo usuario">
        <i class="fa fa-user-plus"></i> Nuevo
      </button>
    </div>
  </div>

  <div class="card-body">
    <table class="table table-hover table-striped table-bordered align-middle" id="tbl-users">
      <colgroup>
        <col width="5%"><col width="30%"><col width="20%"><col width="15%"><col width="15%"><col width="15%">
      </colgroup>
      <thead>
        <tr>
          <th class="text-center">#</th>
          <th>Nombre</th>
          <th>Usuario</th>
          <th>Rol</th>
          <th class="text-center">Estado</th>
          <th class="text-center">Acción</th>
        </tr>
      </thead>
      <tbody id="users-tbody">
      <?php if (!empty($users)): $i=1; foreach ($users as $row): ?>
        <tr>
          <td class="text-center"><?= $i++ ?></td>
          <td class="fullname"><?= htmlspecialchars($row['fullname']) ?></td>
          <td class="username"><?= htmlspecialchars($row['username']) ?></td>
          <td class="role-label"><?= htmlspecialchars($roles[(int)$row['type']] ?? 'Desconocido') ?></td>
          <td class="text-center status-cell" data-status="<?= (int)$row['status'] ?>">
            <?php if ((int)$row['status'] === 1): ?>
              <span class="badge bg-success">Activo</span>
            <?php else: ?>
              <span class="badge bg-secondary">Inactivo</span>
            <?php endif; ?>
          </td>
          <td class="text-center">
            <div class="btn-group btn-group-sm">
              <button class="btn btn-outline-primary edit_data" data-bs-toggle="tooltip" title="Editar"
                      data-id="<?= (int)$row['user_id'] ?>">
                <i class="fa fa-pen"></i>
              </button>
              <button class="btn btn-outline-warning reset_pwd" data-bs-toggle="tooltip" title="Reiniciar contraseña"
                      data-id="<?= (int)$row['user_id'] ?>">
                <i class="fa fa-redo"></i>
              </button>
              <button class="btn btn-outline-danger delete_data" data-bs-toggle="tooltip" title="Eliminar"
                      data-id="<?= (int)$row['user_id'] ?>" data-name="<?= htmlspecialchars($row['fullname']) ?>">
                <i class="fa fa-trash"></i>
              </button>
            </div>
          </td>
        </tr>
      <?php endforeach; else: ?>
        <tr class="no-data"><td class="text-center" colspan="6">Sin datos para mostrar.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
(() => {
  // Prefill desde servidor
  const qServer = <?= json_encode($q ?? '') ?>;
  if (qServer) document.getElementById('q').value = qServer;

  // Tooltips
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map(el => new bootstrap.Tooltip(el));

  // Filtros por rol/estado (servidor)
  document.getElementById('btn-filtrar').addEventListener('click', () => {
    const params = new URLSearchParams();
    const q = document.getElementById('q').value.trim(); // opcional enviarlo también
    const role = document.getElementById('fil_role').value;
    const st = document.getElementById('fil_status').value;
    if (q)    params.set('q', q);
    if (role) params.set('role', role);
    if (st)   params.set('status', st);
    location.href = 'index.php?url=user/index' + (params.toString() ? '&'+params.toString() : '');
  });

  // Nuevo
  document.getElementById('create_new').addEventListener('click', () => {
    uni_modal('Nuevo usuario', "index.php?url=user/manage");
  });

  // Editar
  document.querySelectorAll('.edit_data').forEach(b=>{
    b.addEventListener('click',()=>{
      uni_modal('Editar usuario', "index.php?url=user/manage&id=" + b.dataset.id);
    });
  });

  // Reset password
  document.querySelectorAll('.reset_pwd').forEach(b=>{
    b.addEventListener('click',()=>{
      const id = b.dataset.id;
      _conf('¿Reiniciar la contraseña de este usuario a <b>123456</b>?','do_reset_pwd',[id]);
    });
  });

  // Eliminar
  document.querySelectorAll('.delete_data').forEach(b=>{
    b.addEventListener('click',()=>{
      _conf("¿Eliminar al usuario <b>"+b.dataset.name+"</b>?", 'delete_user', [b.dataset.id]);
    });
  });

  // === BÚSQUEDA INSTANTÁNEA (CLIENTE) ===
  const $q = document.getElementById('q');
  const $tbody = document.getElementById('users-tbody');

  function normalize(s){ return (s||'').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''); }

  let t=null;
  $q.addEventListener('input', function(){
    clearTimeout(t);
    t = setTimeout(()=>{
      const term = normalize($q.value.trim());
      const rows = Array.from($tbody.querySelectorAll('tr'));
      let visible = 0;

      rows.forEach((tr)=>{
        if (tr.classList.contains('no-data')) return; // fila "Sin datos"
        const name = normalize(tr.querySelector('.fullname')?.textContent || '');
        const user = normalize(tr.querySelector('.username')?.textContent || '');
        const match = term === '' ? true : (name.includes(term) || user.includes(term));
        tr.style.display = match ? '' : 'none';
        if (match) visible++;
      });

      // Mostrar/ocultar fila "Sin datos"
      let noData = $tbody.querySelector('tr.no-data');
      if (!noData) {
        noData = document.createElement('tr');
        noData.className = 'no-data';
        noData.innerHTML = '<td class="text-center" colspan="6">Sin resultados</td>';
        $tbody.appendChild(noData);
      }
      noData.style.display = (visible === 0) ? '' : 'none';

      // Recalcular numeración (#)
      let idx = 1;
      rows.forEach(tr=>{
        if (!tr.classList.contains('no-data') && tr.style.display !== 'none') {
          const cell = tr.querySelector('td');
          if (cell) cell.textContent = idx++;
        }
      });

    }, 150); // debounce
  });
})();

function do_reset_pwd(id){
  $('#confirm_modal button').attr('disabled', true);
  $.post('index.php?url=user/resetPassword',{id}, function(resp){
    if(resp.status==='success'){ alert(resp.msg); location.reload(); }
    else { alert(resp.msg||'Error'); $('#confirm_modal button').attr('disabled', false); }
  }, 'json').fail(()=>{
    alert('Error de red.'); $('#confirm_modal button').attr('disabled', false);
  });
}

function delete_user(id){
  $('#confirm_modal button').attr('disabled', true);
  $.ajax({
    url: 'index.php?url=user/delete',
    method: 'POST',
    data: { id },
    dataType: 'json',
    error: err => {
      console.error(err);
      alert("Ocurrió un error.");
      $('#confirm_modal button').attr('disabled', false);
    },
    success: function(resp){
      if (resp.status === 'success') {
        location.reload();
      } else {
        alert(resp.msg || "Ocurrió un error.");
        $('#confirm_modal button').attr('disabled', false);
      }
    }
  });
}
</script>
