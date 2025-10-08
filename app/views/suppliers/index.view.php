<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title mb-0">Proveedores</h3>
    <div class="d-flex gap-2">
      <input type="search" id="q" class="form-control form-control-sm" placeholder="Buscar (código, nombre, contacto, email)">
      <select id="fil_status" class="form-select form-select-sm">
        <option value="">Estado (todos)</option>
        <option value="1" <?= isset($status) && $status===1 ? 'selected':'' ?>>Activo</option>
        <option value="0" <?= isset($status) && $status===0 ? 'selected':'' ?>>Inactivo</option>
      </select>
      <button class="btn btn-outline-secondary btn-sm" id="btn-filtrar" title="Aplicar filtros">
        <i class="fa fa-filter"></i>
      </button>
      <button class="btn btn-dark btn-sm" id="create_new" title="Nuevo proveedor">
        <i class="fa fa-plus"></i> Nuevo
      </button>
    </div>
  </div>

  <div class="card-body">
    <table class="table table-hover table-striped table-bordered align-middle" id="tbl-sup">
      <colgroup>
        <col width="5%"><col width="14%"><col width="25%"><col width="14%"><col width="20%"><col width="10%"><col width="12%">
      </colgroup>
      <thead>
        <tr>
          <th class="text-center">#</th>
          <th>Código</th>
          <th>Nombre</th>
          <th>Contacto</th>
          <th>Email</th>
          <th class="text-center">Estado</th>
          <th class="text-center">Acción</th>
        </tr>
      </thead>
      <tbody id="sup-tbody">
      <?php if (!empty($suppliers)): $i=1; foreach($suppliers as $s): ?>
        <tr>
          <td class="text-center"><?= $i++ ?></td>
          <td class="code"><?= htmlspecialchars($s['code']) ?></td>
          <td class="name"><?= htmlspecialchars($s['name']) ?></td>
          <td class="contact"><?= htmlspecialchars($s['contact']) ?></td>
          <td class="email"><?= htmlspecialchars($s['email']) ?></td>
          <td class="text-center" data-status="<?= (int)$s['status'] ?>">
            <?php if ((int)$s['status']===1): ?>
              <span class="badge bg-success">Activo</span>
            <?php else: ?>
              <span class="badge bg-secondary">Inactivo</span>
            <?php endif; ?>
          </td>
          <td class="text-center">
            <div class="btn-group btn-group-sm">
              <button class="btn btn-outline-primary edit" data-id="<?= (int)$s['supplier_id'] ?>" data-bs-toggle="tooltip" title="Editar">
                <i class="fa fa-pen"></i>
              </button>
              <button class="btn btn-outline-danger del" data-id="<?= (int)$s['supplier_id'] ?>" data-name="<?= htmlspecialchars($s['name']) ?>" data-bs-toggle="tooltip" title="Eliminar">
                <i class="fa fa-trash"></i>
              </button>
            </div>
          </td>
        </tr>
      <?php endforeach; else: ?>
        <tr class="no-data"><td class="text-center" colspan="7">Sin datos para mostrar.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
(() => {
  // Prefill de búsqueda desde servidor si vino en la URL
  const qServer = <?= json_encode($q ?? '') ?>;
  if (qServer) document.getElementById('q').value = qServer;

  // Tooltips
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map(el => new bootstrap.Tooltip(el));

  // Filtro por estado (servidor)
  document.getElementById('btn-filtrar').addEventListener('click', () => {
    const params = new URLSearchParams();
    const q = document.getElementById('q').value.trim(); // si quieres persistirlo al servidor
    const st = document.getElementById('fil_status').value;
    if (q)  params.set('q', q);
    if (st) params.set('status', st);
    location.href = 'index.php?url=supplier/index' + (params.toString() ? '&'+params.toString() : '');
  });

  // Nuevo
  document.getElementById('create_new').addEventListener('click', () => {
    uni_modal('Nuevo proveedor','index.php?url=supplier/manage','mid-large');
  });

  // Editar
  document.querySelectorAll('.edit').forEach(btn=>{
    btn.addEventListener('click',()=>{
      uni_modal('Editar proveedor','index.php?url=supplier/manage&id='+btn.dataset.id,'mid-large');
    });
  });

  // Eliminar
  document.querySelectorAll('.del').forEach(btn=>{
    btn.addEventListener('click',()=>{
      _conf('¿Eliminar al proveedor <b>'+btn.dataset.name+'</b>?','do_del',[ btn.dataset.id ]);
    });
  });

  // === BÚSQUEDA EN VIVO (cliente) ===
  const $q = document.getElementById('q');
  const $tbody = document.getElementById('sup-tbody');

  function norm(s){ return (s||'').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''); }

  let t=null;
  $q.addEventListener('input', function(){
    clearTimeout(t);
    t = setTimeout(()=>{
      const term = norm($q.value.trim());
      const rows = Array.from($tbody.querySelectorAll('tr'));
      let visible=0;

      rows.forEach(tr=>{
        if (tr.classList.contains('no-data')) return;
        const code = norm(tr.querySelector('.code')?.textContent || '');
        const name = norm(tr.querySelector('.name')?.textContent || '');
        const contact = norm(tr.querySelector('.contact')?.textContent || '');
        const email = norm(tr.querySelector('.email')?.textContent || '');
        const match = term === '' ? true : (code.includes(term) || name.includes(term) || contact.includes(term) || email.includes(term));
        tr.style.display = match ? '' : 'none';
        if (match) visible++;
      });

      let noData = $tbody.querySelector('tr.no-data');
      if (!noData) {
        noData = document.createElement('tr');
        noData.className = 'no-data';
        noData.innerHTML = '<td class="text-center" colspan="7">Sin resultados</td>';
        $tbody.appendChild(noData);
      }
      noData.style.display = (visible===0) ? '' : 'none';

      // Recontar índice
      let idx=1;
      rows.forEach(tr=>{
        if (!tr.classList.contains('no-data') && tr.style.display!=='none') {
          const cell = tr.querySelector('td');
          if (cell) cell.textContent = idx++;
        }
      });
    }, 150);
  });
})();

function do_del(id){
  $('#confirm_modal button').attr('disabled', true);
  $.post('index.php?url=supplier/delete',{id}, resp=>{
    if(resp.status==='success'){ location.reload(); }
    else { alert(resp.msg||'Error'); $('#confirm_modal button').attr('disabled', false); }
  },'json').fail(()=>{
    alert('Error de red'); $('#confirm_modal button').attr('disabled', false);
  });
}
</script>
