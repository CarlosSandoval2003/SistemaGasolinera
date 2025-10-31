<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title mb-0">Proveedores</h3>
  </div>

  <div class="card-body">
    <h5 class="mb-3">Filtros</h5>

    <div class="row g-3 align-items-end">
      <div class="col-md-6">
        <label for="q" class="form-label">Buscar</label>
        <input type="search" id="q" class="form-control form-control-sm rounded-0"
               placeholder="Buscar (código, nombre, contacto, email)"
               value="<?= htmlspecialchars($q ?? '') ?>">
      </div>

      <div class="col-md-3">
        <label for="fil_status" class="form-label">Estado</label>
        <select id="fil_status" class="form-select form-select-sm rounded-0">
          <option value="">(todos)</option>
          <option value="1" <?= isset($status) && $status===1 ? 'selected':'' ?>>Activo</option>
          <option value="0" <?= isset($status) && $status===0 ? 'selected':'' ?>>Inactivo</option>
        </select>
      </div>

      <div class="col-md-3 d-flex gap-2">
        <button class="btn btn-primary btn-sm rounded-0" id="btn-filtrar" type="button" data-bs-toggle="tooltip" title="Aplicar filtros">
          <i class="fa fa-filter"></i> Filtrar
        </button>
        <button class="btn btn-success btn-sm rounded-0" id="btn-print" type="button" data-bs-toggle="tooltip" title="Imprimir reporte">
          <i class="fa fa-print"></i> Imprimir
        </button>
        <button class="btn btn-dark btn-sm rounded-0" id="create_new" type="button" data-bs-toggle="tooltip" title="Nuevo proveedor">
          <i class="fa fa-plus"></i> Nuevo
        </button>
      </div>
    </div>

    <hr>
    <div class="clear-fix mb-2"></div>

    <div id="outprint">
      <table class="table table-hover table-striped table-bordered align-middle" id="tbl-sup">
        <colgroup>
          <col width="5%">
          <col width="12%">
          <col width="22%">
          <col width="12%">
          <col width="18%">
          <col width="18%">
          <col width="7%">
          <col width="6%" class="col-actions">
        </colgroup>
        <thead>
          <tr>
            <th class="text-center p-0">#</th>
            <th class="p-0">Código</th>
            <th class="p-0">Nombre</th>
            <th class="p-0">Contacto</th>
            <th class="p-0">Email</th>
            <th class="p-0">Dirección</th>
            <th class="text-center p-0">Estado</th>
            <th class="text-center p-0 col-actions">Acción</th>
          </tr>
        </thead>
        <tbody id="sup-tbody">
        <?php if (!empty($suppliers)): $i=1; foreach($suppliers as $s): ?>
          <tr>
            <td class="text-center idx"><?= $i++ ?></td>
            <td class="code"><?= htmlspecialchars($s['code']) ?></td>
            <td class="name"><?= htmlspecialchars($s['name']) ?></td>
            <td class="contact"><?= htmlspecialchars($s['contact']) ?></td>
            <td class="email"><?= htmlspecialchars($s['email']) ?></td>
            <td class="address"><?= htmlspecialchars($s['address'] ?? '') ?></td>
            <td class="text-center" data-status="<?= (int)$s['status'] ?>">
              <?php if ((int)$s['status']===1): ?>
                <span class="badge bg-success">Activo</span>
              <?php else: ?>
                <span class="badge bg-secondary">Inactivo</span>
              <?php endif; ?>
            </td>
            <td class="text-center col-actions">
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
          <tr class="no-data"><td class="text-center" colspan="8">Sin datos para mostrar.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
(function(){
  // Tooltips Bootstrap
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map(el => new bootstrap.Tooltip(el));

  // Filtro por estado (servidor) + query
  document.getElementById('btn-filtrar').addEventListener('click', () => {
    const params = new URLSearchParams();
    const q = document.getElementById('q').value.trim();
    const st = document.getElementById('fil_status').value;
    if (q)  params.set('q', q);
    if (st !== '') params.set('status', st);
    location.href = 'index.php?url=supplier/index' + (params.toString() ? '&'+params.toString() : '');
  });

  // Nuevo
  document.getElementById('create_new').addEventListener('click', () => {
    uni_modal('Nuevo proveedor','index.php?url=supplier/manage','mid-large');
  });

  // ====== Editar / Eliminar (delegación) ======
  $(document).on('click', '.edit', function(){
    const id = $(this).data('id');
    uni_modal('Editar proveedor','index.php?url=supplier/manage&id='+id,'mid-large');
  });

  $(document).on('click', '.del', function(){
    _conf('¿Eliminar al proveedor <b>'+$(this).data('name')+'</b>?','do_del',[ $(this).data('id') ]);
  });

  // === BÚSQUEDA EN VIVO (cliente) ===
  const $q = document.getElementById('q');
  const $tbody = document.getElementById('sup-tbody');

  function norm(s){ return (s||'').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''); }
  function applyFilter(){
    const term = norm($q.value.trim());
    const rows = Array.from($tbody.querySelectorAll('tr'));
    let visible=0;

    rows.forEach(tr=>{
      if (tr.classList.contains('no-data')) return;
      const code = norm(tr.querySelector('.code')?.textContent || '');
      const name = norm(tr.querySelector('.name')?.textContent || '');
      const contact = norm(tr.querySelector('.contact')?.textContent || '');
      const email = norm(tr.querySelector('.email')?.textContent || '');
      const address = norm(tr.querySelector('.address')?.textContent || '');
      const match = term === '' ? true : (
        code.includes(term) || name.includes(term) || contact.includes(term) || email.includes(term) || address.includes(term)
      );
      tr.style.display = match ? '' : 'none';
      if (match) visible++;
    });

    let noData = $tbody.querySelector('tr.no-data');
    if (!noData) {
      noData = document.createElement('tr');
      noData.className = 'no-data';
      noData.innerHTML = '<td class="text-center" colspan="8">Sin resultados</td>';
      $tbody.appendChild(noData);
    }
    noData.style.display = (visible===0) ? '' : 'none';

    // Recontar índice
    const rowsAll = Array.from($tbody.querySelectorAll('tr'));
    let idx=1;
    rowsAll.forEach(tr=>{
      if (!tr.classList.contains('no-data') && tr.style.display!=='none') {
        const cell = tr.querySelector('td.idx');
        if (cell) cell.textContent = idx++;
      }
    });
  }
  let t=null;
  $q.addEventListener('input', function(){
    clearTimeout(t);
    t = setTimeout(applyFilter, 150);
  });

  // ====== IMPRESIÓN (idéntico a Ventas, pero SIN "Acción" y CON "Dirección") ======
  $('#btn-print').click(function(){
    const h  = $('head').clone();
    const p  = $('#outprint').clone();
    const el = $('<div>').append(h);

    // Subtítulo con filtros aplicados
    const qTxt = $('#q').val().trim();
    const st   = $('#fil_status').val();
    let sub = [];
    if (qTxt) sub.push('Búsqueda: "'+qTxt.replace(/</g,'&lt;')+'"');
    if (st === '1') sub.push('Estado: Activo');
    else if (st === '0') sub.push('Estado: Inactivo');

    el.append("<div class='text-center lh-1 fw-bold'>REPORTE DE PROVEEDORES<br/><small>"+(sub.length? sub.join(' | ') : 'Todos los proveedores')+"</small></div><hr/>");

    // Quitar botones dentro de la tabla
    p.find('button').remove();
    // Quitar la columna de ACCIÓN (th y tds) y su <col> correspondiente
    p.find('th.col-actions, td.col-actions, col.col-actions').remove();

    el.append(p);

    const nw = window.open("", "", "width=1200,height=900,left=150");
    nw.document.write(el.html());
    nw.document.close();
    setTimeout(() => { nw.print(); setTimeout(()=>nw.close(), 150); }, 200);
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
