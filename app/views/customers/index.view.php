<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title mb-0">Clientes</h3>
    <div class="d-flex gap-2">
      <input type="search" id="fil_nit" class="form-control form-control-sm" placeholder="Buscar por NIT"
             value="<?= htmlspecialchars($filters['nit'] ?? '') ?>">
      <input type="search" id="fil_name" class="form-control form-control-sm" placeholder="Buscar por nombre"
             value="<?= htmlspecialchars($filters['name'] ?? '') ?>">
    </div>
  </div>

  <div class="card-body">
    <table class="table table-hover table-striped table-bordered align-middle" id="tbl-customers">
      <colgroup>
        <col width="6%"><col width="18%"><col width="46%"><col width="14%"><col width="16%">
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
      <tbody id="cust-tbody">
        <?php if(!empty($customers)): $i=1; foreach($customers as $c): ?>
          <tr>
            <td class="text-center idx"><?= $i++ ?></td>
            <td class="text-center nit"><?= htmlspecialchars($c['customer_code'] ?? '') ?></td>
            <td class="name"><?= htmlspecialchars($c['fullname'] ?? '') ?></td>
            <td class="text-center">
              <?php if(((int)($c['status'] ?? 1)) === 1): ?>
                <span class="badge bg-success">Activo</span>
              <?php else: ?>
                <span class="badge bg-secondary">Inactivo</span>
              <?php endif; ?>
            </td>
            <td class="text-center">
              <div class="btn-group btn-group-sm" role="group">
                <button class="btn btn-outline-primary edit_data"    data-bs-toggle="tooltip" title="Editar"
                        data-id="<?= (int)$c['customer_id'] ?>"><i class="fa fa-pen"></i></button>
                <button class="btn btn-outline-danger delete_data"   data-bs-toggle="tooltip" title="Eliminar"
                        data-id="<?= (int)$c['customer_id'] ?>"
                        data-name="<?= htmlspecialchars(($c['customer_code'] ?? '').' - '.($c['fullname'] ?? '')) ?>"><i class="fa fa-trash"></i></button>
              </div>
            </td>
          </tr>
        <?php endforeach; else: ?>
          <tr class="no-data"><td colspan="5" class="text-center">Sin datos</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
(() => {
  // Inicializar tooltips
  try {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(el => new bootstrap.Tooltip(el));
  } catch(e){}

  // Abrir modales
  $(document).on('click', '.edit_data', function(){
    const id = $(this).data('id');
    uni_modal('Editar Cliente', `index.php?url=customer/manage&id=${id}`, 'mid-large');
  });

  $(document).on('click', '.view_data', function(){
    const id = $(this).data('id');
    uni_modal('Detalle de Cliente', `index.php?url=customer/viewCustomer/${id}`, '');
  });

  $(document).on('click', '.delete_data', function(){
    const id = $(this).data('id');
    const name = $(this).data('name');
    _conf(`¿Eliminar <b>${name}</b>?`, 'delete_data', [ id ]);
  });

  // === Búsqueda en vivo (por NIT o nombre) ===
  const $nit   = document.getElementById('fil_nit');
  const $name  = document.getElementById('fil_name');
  const $tbody = document.getElementById('cust-tbody');

  function norm(s){
    return (s||'').toString().toLowerCase()
      .normalize('NFD').replace(/[\u0300-\u036f]/g,'');
  }

  function applyFilter(){
    if (!$tbody) return;
    const tNit  = norm(($nit?.value || '').trim());
    const tName = norm(($name?.value || '').trim());
    const rows  = Array.from($tbody.querySelectorAll('tr'));
    let visible = 0;

    rows.forEach(tr=>{
      if (tr.classList.contains('no-data')) return;
      const nit  = norm(tr.querySelector('.nit')?.textContent || '');
      const name = norm(tr.querySelector('.name')?.textContent || '');
      const okNit  = tNit===''  || nit.includes(tNit);
      const okName = tName==='' || name.includes(tName);
      const show = okNit && okName;
      tr.style.display = show ? '' : 'none';
      if (show) visible++;
    });

    let noData = $tbody.querySelector('tr.no-data');
    if (!noData) {
      noData = document.createElement('tr');
      noData.className = 'no-data';
      noData.innerHTML = '<td colspan="5" class="text-center">Sin resultados</td>';
      $tbody.appendChild(noData);
    }
    noData.style.display = (visible===0) ? '' : 'none';

    // Reindexar
    let i = 1;
    rows.forEach(tr=>{
      if (!tr.classList.contains('no-data') && tr.style.display!=='none') {
        const idx = tr.querySelector('td.idx');
        if (idx) idx.textContent = i++;
      }
    });
  }

  let t1=null, t2=null;
  if ($nit)  $nit.addEventListener('input', ()=>{ clearTimeout(t1); t1=setTimeout(applyFilter,150); });
  if ($name) $name.addEventListener('input', ()=>{ clearTimeout(t2); t2=setTimeout(applyFilter,150); });
})();

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
