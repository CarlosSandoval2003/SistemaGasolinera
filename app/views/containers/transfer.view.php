<div class="container-fluid">
  <form id="transfer-form">
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
        <input type="number" step="any" class="form-control form-control-sm" name="qty_liters" required>
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
        <input class="form-control form-control-sm" name="note">
      </div>
    </div>
  </form>
</div>
<script>
(() => {
  // Scope local: nada se fuga a window
  const containers = <?= json_encode($containers ?? []) ?>;
  const $modal = $('#uni_modal');

  function fillSelects(ptid){
    const list = containers.filter(c => String(c.petrol_type_id) === String(ptid) && Number(c.status) === 1);
    const options = ['<option value="" disabled selected></option>'];
    list.forEach(c => {
      options.push(`<option value="${c.container_id}">${c.name} (stock: ${Number(c.qty_liters).toFixed(2)}L)</option>`);
    });
    $modal.find('#from,#to').html(options.join(''));
  }

  // Limpia selects al cargar el modal
  $modal.find('#from,#to').html('<option value="" disabled selected></option>');

  // Evitar duplicados: off + on con namespace
  $(document)
    .off('change.transfer', '#uni_modal #ptype')
    .on('change.transfer', '#uni_modal #ptype', function(){
      fillSelects(this.value);
    });

  $(document)
    .off('submit.transfer', '#uni_modal #transfer-form')
    .on('submit.transfer', '#uni_modal #transfer-form', function(e){
      e.preventDefault();
      const $form = $(this);
      $.post('index.php?url=containers/transfer', $form.serialize(), function(resp){
        if (resp.status === 'success') {
          alert('Transferencia realizada');
          $modal.modal('hide');
        } else {
          alert(resp.msg || 'Error');
        }
      }, 'json').fail(() => alert('Error'));
    });

  // Al cerrar el modal, limpia los handlers para este modal
  $modal.one('hidden.bs.modal', function(){
    $(document).off('.transfer');
  });
})();
</script>

