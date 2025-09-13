<div class="container-fluid">
    <form action="" id="customer-form">
        <input type="hidden" name="id" value="<?= $customer['customer_id'] ?? '' ?>">
        <div class="col-12">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="fullname" class="control-label">Nombre Cliente</label>
                        <input type="text" name="fullname" autofocus id="fullname" required class="form-control form-control-sm rounded-0" value="<?= $customer['fullname'] ?? '' ?>">
                    </div>
                    <div class="form-group">
                        <label for="email" class="control-label">Email</label>
                        <input type="text" name="email" id="email" required class="form-control form-control-sm rounded-0" value="<?= $customer['email'] ?? '' ?>">
                    </div>
                    <div class="form-group">
                        <label for="contact" class="control-label">Contacto</label>
                        <input type="text" name="contact" id="contact" required class="form-control form-control-sm rounded-0" value="<?= $customer['contact'] ?? '' ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="address" class="control-label">Dirección</label>
                        <textarea name="address" id="address" cols="30" rows="3" class="form-control rounded-0" required><?= $customer['address'] ?? '' ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="status" class="control-label">Estado</label>
                        <select name="status" id="status" class="form-select form-select-sm rounded-0" required>
                            <option value="1" <?= (isset($customer['status']) && $customer['status'] == 1) ? 'selected' : '' ?>>Activo</option>
                            <option value="0" <?= (isset($customer['status']) && $customer['status'] == 0) ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
$(function(){
    $('#customer-form').submit(function(e){
        e.preventDefault();
        $('.pop_msg').remove()
        let _this = $(this)
        let _el = $('<div>').addClass('pop_msg')

        $('#uni_modal button').attr('disabled', true)
        $('#uni_modal button[type="submit"]').text('Submitting form...')

        $.ajax({
            url: 'index.php?url=customer/save',
            data: new FormData(_this[0]),
            cache: false,
            contentType: false,
            processData: false,
            method: 'POST',
            dataType: 'json',
            error: err => {
                console.log(err)
                _el.addClass('alert alert-danger').text("An error occurred.")
                _this.prepend(_el).hide().show('slow')
                $('#uni_modal button').attr('disabled', false)
                $('#uni_modal button[type="submit"]').text('Save')
            },
            success: function(resp){
                if(resp.status == 'success'){
                    _el.addClass('alert alert-success').text(resp.msg)
                    $('#uni_modal').on('hide.bs.modal', function(){
                        location.reload()
                    })
                    if (!"<?= isset($customer['customer_id']) ?>")
                        _this.get(0).reset()
                } else {
                    _el.addClass('alert alert-danger').text(resp.msg)
                }
                _this.prepend(_el).hide().show('slow')
                $('#uni_modal button').attr('disabled', false)
                $('#uni_modal button[type="submit"]').text('Save')
            }
        })
    })
})
</script>
