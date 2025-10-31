<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title mb-0">Puestos</h3>
    <div class="d-flex gap-2">
      <input type="search" id="q" class="form-control form-control-sm" placeholder="Buscar por nombre de puesto">
      <button class="btn btn-dark btn-sm" id="btn-new" title="Nuevo puesto" data-bs-toggle="tooltip">
        <i class="fa fa-plus"></i>
      </button>
    </div>
  </div>

  <div class="card-body">
    <table class="table table-hover table-striped table-bordered align-middle" id="tbl-pos">
      <colgroup>
        <col width="8%"><col width="54%"><col width="18%"><col width="20%">
      </colgroup>
      <thead class="table-light">
        <tr>
          <th class="text-center">#</th>
          <th>Nombre</th>
          <th class="text-center">Estado</th>
          <th class="text-center">Acción</th>
        </tr>
      </thead>
      <tbody id="pos-tbody">
        <?php if(!empty($positions)): $i=1; foreach($positions as $p): ?>
          <tr data-status="<?= (int)$p['status'] ?>">
            <td class="text-center idx"><?= $i++ ?></td>
            <td class="name"><?= htmlspecialchars($p['name']) ?></td>
            <td class="text-center">
              <?php if((int)$p['status']===1): ?>
                <span class="badge bg-success">Activo</span>
              <?php else: ?>
                <span class="badge bg-secondary">Inactivo</span>
              <?php endif; ?>
            </td>
            <td class="text-center">
              <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-primary edit" 
                        data-id="<?= (int)$p['position_id'] ?>" 
                        data-bs-toggle="tooltip" title="Editar">
                  <i class="fa fa-pen"></i>
                </button>
                <button class="btn btn-outline-danger del" 
                        data-id="<?= (int)$p['position_id'] ?>" 
                        data-name="<?= htmlspecialchars($p['name']) ?>" 
                        data-bs-toggle="tooltip" title="Eliminar">
                  <i class="fa fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>
        <?php endforeach; else: ?>
          <tr class="no-data"><td class="text-center" colspan="4">Sin datos</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- === Modal de confirmación simple (OK) === -->
<div class="modal fade" id="ok_modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h6 class="modal-title">Confirmación</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div id="ok_modal_msg">Operación realizada con éxito.</div>
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>

<script>
(() => {
  // Tooltips
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map(el => new bootstrap.Tooltip(el));

  // Nuevo
  document.getElementById('btn-new').addEventListener('click', () => {
    uni_modal('Nuevo puesto','index.php?url=position/manage','modal-md');
  });

  // Editar
  document.querySelectorAll('.edit').forEach(btn=>{
    btn.addEventListener('click',()=>{
      uni_modal('Editar puesto','index.php?url=position/manage&id='+btn.dataset.id,'modal-md');
    });
  });

  // Eliminar
  document.querySelectorAll('.del').forEach(btn=>{
    btn.addEventListener('click',()=>{
      _conf('¿Eliminar el puesto <b>'+btn.dataset.name+'</b>?','do_del',[ btn.dataset.id ]);
    });
  });

  // === BÚSQUEDA EN VIVO POR NOMBRE ===
  const $q = document.getElementById('q');
  const $tbody = document.getElementById('pos-tbody');

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
        const name = norm(tr.querySelector('.name')?.textContent || '');
        const match = term === '' ? true : name.includes(term);
        tr.style.display = match ? '' : 'none';
        if (match) visible++;
      });

      let noData = $tbody.querySelector('tr.no-data');
      if (!noData) {
        noData = document.createElement('tr');
        noData.className = 'no-data';
        noData.innerHTML = '<td class="text-center" colspan="4">Sin resultados</td>';
        $tbody.appendChild(noData);
      }
      noData.style.display = (visible===0) ? '' : 'none';

      // Recontar índice visible
      let idx=1;
      rows.forEach(tr=>{
        if (!tr.classList.contains('no-data') && tr.style.display!=='none') {
          const cell = tr.querySelector('td.idx');
          if (cell) cell.textContent = idx++;
        }
      });
    }, 150);
  });

  // ======== MOSTRAR MODAL DE OK (por sessionStorage o por querystring) ========
  function showOk(msg){
    document.getElementById('ok_modal_msg').innerHTML = msg || 'Operación realizada con éxito.';
    const m = new bootstrap.Modal(document.getElementById('ok_modal'));
    m.show();
  }

  // 1) Vía sessionStorage (recomendado desde manage)
  const k = 'pos_saved_msg';
  const m1 = sessionStorage.getItem(k);
  if (m1) {
    sessionStorage.removeItem(k);
    showOk(m1);
  }

  // 2) Vía parámetros en URL (?saved=1&msg=...)
  const usp = new URLSearchParams(location.search);
  if (usp.get('saved') === '1') {
    const msg = usp.get('msg') ? decodeURIComponent(usp.get('msg')) : 'Operación realizada con éxito.';
    showOk(msg);
    // Limpia la query sin recargar
    history.replaceState({}, document.title, location.pathname);
  }
})();

function do_del(id){
  $.post('index.php?url=position/delete',{id}, resp=>{
    if(resp.status==='success') location.reload();
    else alert(resp.msg||'No se pudo eliminar');
  }, 'json').fail(()=>{
    alert('Error de red');
  });
}
</script>
