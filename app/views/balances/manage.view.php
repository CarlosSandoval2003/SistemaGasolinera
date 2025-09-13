<div class="container-fluid">
    <form action="" id="payment-form">
        <input type="hidden" name="id" value="">
        <input type="hidden" name="customer_id" value="<?= $customer['customer_id'] ?? '' ?>">
        <div class="form-group">
            <label for="balance" class="control-label">Balance Restante</label>
            <input type="text" id="balance" required class="form-control form-control-sm rounded-0 text-end"
                   value="<?= number_format($customer['balance'] ?? 0, 2) ?>" disabled>
        </div>
        <div class="form-group">
            <label for="amount" class="control-label">Cantidad a Pagar</label>
            <input type="number" step="any" name="amount" id="amount" required
                   class="form-control form-control-sm rounded-0 text-end" value="0">
        </div>
    </form>
</div>

<script>
    $(function () {
        $('#payment-form').submit(function (e) {
            e.preventDefault();

            const balance = parseFloat($('#balance').val().replace(/,/g, ''));
            const amount = parseFloat($('#amount').val());

            if (balance === 0) {
                alert("El cliente no tiene balance pendiente de pago.");
                return;
            }

            if (amount > balance) {
                alert("Cantidad a pagar invalida.");
                return;
            }

            $('.pop_msg').remove();
            let _this = $(this);
            let _el = $('<div>').addClass('pop_msg');

            $('#uni_modal button').attr('disabled', true);
            $('#uni_modal button[type="submit"]').text('Submitting form...');

            $.ajax({
                url: 'index.php?url=balance/save',
                data: new FormData(_this[0]),
                cache: false,
                contentType: false,
                processData: false,
                method: 'POST',
                dataType: 'json',
                error: err => {
                    console.log(err);
                    _el.addClass('alert alert-danger').text("An error occurred.");
                    _this.prepend(_el).hide().show('slow');
                    $('#uni_modal button').attr('disabled', false);
                    $('#uni_modal button[type="submit"]').text('Save');
                },
                success: function (resp) {
                    if (resp.status === 'success') {
                        _el.addClass('alert alert-success');

                        uni_modal("Balance de <b><?= $customer['customer_code'] . ' - ' . $customer['fullname'] ?></b>",
                            'index.php?url=balance/view/<?= $customer['customer_id'] ?>',
                            'large');

                        $('#uni_modal').on('hide.bs.modal', function () {
                            if ($(this).find('#customer-balance_details').length > 0) {
                                location.reload();
                            }
                        });

                        _this.get(0).reset();
                    } else {
                        _el.addClass('alert alert-danger').text(resp.msg);
                    }

                    _this.prepend(_el).hide().show('slow');
                    $('#uni_modal button').attr('disabled', false);
                    $('#uni_modal button[type="submit"]').text('Save');
                }
            });
        });
    });
</script>
