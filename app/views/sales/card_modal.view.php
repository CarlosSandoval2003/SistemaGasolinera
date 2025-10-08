<style>
  .spinner {
    width: 48px; height: 48px; border: 4px solid #ddd; border-top-color: #0d6efd;
    border-radius: 50%; animation: spin 0.9s linear infinite; margin: 0 auto 12px;
  }
  @keyframes spin { to { transform: rotate(360deg) } }
</style>

<div class="container-fluid text-center py-3">
  <div class="mb-2">
    <div class="spinner"></div>
    <div class="fw-bold">Procesando pago...</div>
    <div class="text-muted small">Acerque/tarjete la tarjeta al POS</div>
  </div>
  <div class="text-muted small" id="cardStatus">Esperando confirmación del banco</div>
</div>

<script>
$(function(){
  // Simula progreso y aprobación
  setTimeout(()=> $('#cardStatus').text('Validando chip/contacless...'), 700);
  setTimeout(()=> $('#cardStatus').text('Contactando emisor...'), 1300);
  setTimeout(()=> {
    $('#cardStatus').html('<span class="text-success fw-bold">Transacción aprobada</span>');
  }, 1900);
  setTimeout(()=> {
    $('#uni_modal').modal('hide');
    // No se envía nada especial; submit normal del formulario
    setTimeout(()=> $('#transaction-form').trigger('submit'), 180);
  }, 2300);
});
</script>
