<div class="container-fluid">
  <form id="transfer-form" autocomplete="off">
    <div class="row g-2">
      <div class="col-md-6">
        <label class="form-label">Combustible</label>
        <select name="petrol_type_id" id="ptype" class="form-select form-select-sm" required>
          <option value="" selected disabled></option>
          <?php foreach($types as $t): ?>
            <option value="<?= $t['petrol_type_id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
          <?php endforeach ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Litros</label>
        <input type="number" step="0.001" min="0.001" class="form-control form-control-sm text-end" name="qty_liters" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Desde</label>
        <select name="from_container_id" id="from" class="form-select form-select-sm" required></select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Hacia</label>
        <select name="to_container_id" id="to" class="form-select form-select-sm" required></select>
      </div>
      <div class="col-12">
        <label class="form-label">Nota</label>
        <input class="form-control form-control-sm" name="note" maxlength="200">
      </div>
    </div>
  </form>
</div>
<script>
(() => {
  const containers = <?= json_encode($containers ?? []) ?>;
  const $modal = $('#uni_modal');

  function optionHtml(c){
    const cap = Number(c.capacity_liters||0);
    const stk = Number(c.qty_liters||0);
    const min = Number(c.min_level_liters||0);
    const st  = Number(c.status||0);
    return `<option value="${c.container_id}"
              data-cap="${cap}" data-stk="${stk}" data-min="${min}" data-status="${st}">
              ${c.name} (stock: ${stk.toFixed(3)}L / cap: ${cap.toFixed(3)}L)
            </option>`;
  }

  function fillSelects(ptid){
    const list = containers.filter(c => String(c.petrol_type_id) === String(ptid) && Number(c.status) === 1);
    const options = ['<option value="" disabled selected></option>'];
    list.forEach(c => options.push(optionHtml(c)));
    $modal.find('#from,#to').html(options.join(''));
  }

  $modal.find('#from,#to').html('<option value="" disabled selected></option>');

  $(document)
    .off('change.transfer', '#uni_modal #ptype')
    .on('change.transfer', '#uni_modal #ptype', function(){ fillSelects(this.value); });

  $(document)
    .off('submit.transfer', '#uni_modal #transfer-form')
    .on('submit.transfer', '#uni_modal #transfer-form', function(e){
      e.preventDefault();
      const $f = $(this);
      const liters = Number($f.find('[name="qty_liters"]').val() || 0);
      const fromId = $f.find('#from').val();
      const toId   = $f.find('#to').val();

      if (!fromId || !toId) { alert('Selecciona los contenedores.'); return; }
      if (fromId === toId) { alert('No puedes transferir al mismo contenedor.'); return; }
      if (!(liters > 0)) { alert('Los litros deben ser > 0.'); return; }

      const fromOpt = $f.find('#from option:selected');
      const toOpt   = $f.find('#to option:selected');

      const fromStk = Number(fromOpt.data('stk') || 0);
      const toStk   = Number(toOpt.data('stk') || 0);
      const toCap   = Number(toOpt.data('cap') || 0);

      if (fromStk < liters) { alert('Stock insuficiente en el contenedor origen.'); return; }
      if (toStk + liters > toCap) { alert('Capacidad excedida en el contenedor destino.'); return; }

      $.post('index.php?url=containers/transfer', $f.serialize(), function(resp){
        if (resp.status === 'success') {
          alert(resp.msg || 'Transferencia realizada');
          // >>> Recargar listado al cerrar:
          $modal.one('hidden.bs.modal', ()=> location.reload());
          $modal.modal('hide');
        } else {
          alert(resp.msg || 'Error');
        }
      }, 'json').fail(()=> alert('Error de red'));
    });

  $modal.one('hidden.bs.modal', function(){ $(document).off('.transfer'); });
})();
</script>
