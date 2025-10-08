<?php
$qServer = $q ?? '';
?>
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title mb-0">Contenedores</h3>
    <div class="d-flex gap-2">
      <input type="search" id="q" class="form-control form-control-sm" placeholder="Buscar (nombre, combustible)">
      <select id="fil_status" class="form-select form-select-sm">
        <option value="">Estado (todos)</option>
        <option value="1" <?= isset($status) && $status===1 ? 'selected':'' ?>>Activo</option>
        <option value="0" <?= isset($status) && $status===0 ? 'selected':'' ?>>Inactivo</option>
      </select>
      <button class="btn btn-outline-secondary btn-sm" id="btn-filtrar" title="Aplicar filtros">
        <i class="fa fa-filter"></i>
      </button>
      <button class="btn btn-primary btn-sm" id="btn-transfer" title="Transferir combustible">
        <i class="fa fa-random"></i> Transferir
      </button>
      <button class="btn btn-dark btn-sm" id="btn-new" title="Nuevo contenedor">
        <i class="fa fa-plus"></i> Nuevo
      </button>
    </div>
  </div>

  <div class="card-body">
    <table class="table table-hover table-striped table-bordered align-middle" id="tbl-cont">
      <colgroup>
        <col width="5%"><col width="20%"><col width="18%"><col width="14%"><col width="14%"><col width="8%"><col width="8%"><col width="13%">
      </colgroup>
      <thead>
        <tr>
          <th class="text-center">#</th>
          <th>Nombre</th>
          <th>Combustible</th>
          <th class="text-end">Capacidad (gal)</th>
          <th class="text-end">Stock (gal)</th>
          <th class="text-center">%</th>
          <th class="text-center">Default</th>
          <th class="text-center">Acción</th>
        </tr>
      </thead>
      <tbody id="cont-tbody">
      <?php if (!empty($containers)): $i=1; foreach($containers as $c):
        // SIN conversiones: mostramos los números tal cual vienen de BD (litros),
        // solo cambiamos la ETIQUETA visual a “(gal)” como pediste.
        $cap = (float)$c['capacity_liters'];
        $stk = (float)$c['qty_liters'];
        $pct = $cap>0 ? ($stk/$cap*100) : 0;
      ?>
        <tr data-status="<?= (int)$c['status'] ?>">
          <td class="text-center idx"><?= $i++ ?></td>
          <td class="name"><?= htmlspecialchars($c['name']) ?></td>
          <td class="fuel"><?= htmlspecialchars($c['petrol_name']) ?></td>
          <td class="text-end cap"><?= number_format($cap, 2) ?></td>
          <td class="text-end stk"><?= number_format($stk, 2) ?></td>
          <td class="text-center"><?= number_format($pct,1) ?>%</td>
          <td class="text-center">
            <?php if (!empty($c['is_default'])): ?>
              <span class="badge bg-success">Sí</span>
            <?php else: ?>
              <span class="badge bg-secondary">No</span>
            <?php endif; ?>
          </td>
          <td class="text-center">
            <div class="btn-group btn-group-sm">
              <!-- Botón Kardex eliminado -->
              <button class="btn btn-outline-primary edit" data-id="<?= (int)$c['container_id'] ?>" data-bs-toggle="tooltip" title="Editar">
                <i class="fa fa-pen"></i>
              </button>
              <button class="btn btn-outline-danger del" data-id="<?= (int)$c['container_id'] ?>" data-name="<?= htmlspecialchars($c['name']) ?>" data-bs-toggle="tooltip" title="Eliminar">
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

<script>
(() => {
  const qServer = <?= json_encode($qServer) ?>;
  if (qServer) document.getElementById('q').value = qServer;

  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map(el => new bootstrap.Tooltip(el));

  document.getElementById('btn-filtrar').addEventListener('click', () => {
    const params = new URLSearchParams();
    const q = document.getElementById('q').value.trim();
    const st = document.getElementById('fil_status').value;
    if (q)  params.set('q', q);
    if (st) params.set('status', st);
    location.href = 'index.php?url=containers/index' + (params.toString() ? '&'+params.toString() : '');
  });

  document.getElementById('btn-new').addEventListener('click', () => {
    uni_modal('Nuevo contenedor','index.php?url=containers/manage','mid-large');
  });

  document.querySelectorAll('.edit').forEach(btn=>{
    btn.addEventListener('click',()=>{
      uni_modal('Editar contenedor','index.php?url=containers/manage&id='+btn.dataset.id,'mid-large');
    });
  });

  document.getElementById('btn-transfer').addEventListener('click', () => {
    uni_modal('Transferencia','index.php?url=containers/transfer','mid-large');
  });

  document.querySelectorAll('.del').forEach(btn=>{
    btn.addEventListener('click',()=>{
      _conf('¿Eliminar el contenedor <b>'+btn.dataset.name+'</b>?','do_del',[ btn.dataset.id ]);
    });
  });

  // Búsqueda en vivo
  const $q = document.getElementById('q');
  const $tbody = document.getElementById('cont-tbody');
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
        const fuel = norm(tr.querySelector('.fuel')?.textContent || '');
        const match = term === '' ? true : (name.includes(term) || fuel.includes(term));
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

      let idx=1;
      rows.forEach(tr=>{
        if (!tr.classList.contains('no-data') && tr.style.display!=='none') {
          const cell = tr.querySelector('td.idx');
          if (cell) cell.textContent = idx++;
        }
      });
    }, 150);
  });
})();

function do_del(id){
  $('#confirm_modal button').attr('disabled', true);
  $.post('index.php?url=containers/delete',{id}, resp=>{
    if(resp.status==='success'){ location.reload(); }
    else { alert(resp.msg||'Error'); $('#confirm_modal button').attr('disabled', false); }
  },'json').fail(()=>{
    alert('Error de red'); $('#confirm_modal button').attr('disabled', false);
  });
}
</script>
