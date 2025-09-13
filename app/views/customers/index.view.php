<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h3 class="card-title">Lista de Clientes</h3>
        <div class="card-tools align-middle">
            <button class="btn btn-dark btn-sm py-1 rounded-0" type="button" id="create_new">Add New</button>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-hover table-striped table-bordered">
            <colgroup>
                <col width="5%">
                <col width="20%">
                <col width="15%">
                <col width="15%">
                <col width="20%">
                <col width="10%">
                <col width="15%">
            </colgroup>
            <thead>
                <tr>
                    <th class="text-center p-0">#</th>
                    <th class="text-center p-0">Nombre</th>
                    <th class="text-center p-0">Contacto</th>
                    <th class="text-center p-0">Email</th>
                    <th class="text-center p-0">Dirección</th>
                    <th class="text-center p-0">Estado</th>
                    <th class="text-center p-0">Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($customers as $row): ?>
                <tr>
                    <td class="text-center p-1"><?= $i++ ?></td>
                    <td class="py-1 px-2">
                        <div class="lh-1">
                            <span><?= htmlspecialchars($row['customer_code']) ?></span><br>
                            <span><?= htmlspecialchars($row['fullname']) ?></span>
                        </div>
                    </td>
                    <td class="py-1 px-2"><?= htmlspecialchars($row['contact']) ?></td>
                    <td class="py-1 px-2"><?= htmlspecialchars($row['email']) ?></td>
                    <td class="py-1 px-2 text-truncate"><?= htmlspecialchars($row['address']) ?></td>
                    <td class="py-1 px-2 text-center">
                        <?php if ($row['status'] == 1): ?>
                            <span class="py-1 px-3 badge rounded-pill bg-success"><small>Activo</small></span>
                        <?php else: ?>
                            <span class="py-1 px-3 badge rounded-pill bg-danger"><small>Inactivo</small></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center py-1 px-2">
                        <div class="btn-group" role="group">
                            <button id="btnGroupDrop1" type="button" class="btn btn-primary dropdown-toggle btn-sm rounded-0 py-0" data-bs-toggle="dropdown" aria-expanded="false">Acción</button>
                            <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                                <li><a class="dropdown-item view_data" data-id="<?= $row['customer_id'] ?>" href="javascript:void(0)">Ver Detalles</a></li>
                                <li><a class="dropdown-item edit_data" data-id="<?= $row['customer_id'] ?>" href="javascript:void(0)">Editar</a></li>
                                <li><a class="dropdown-item delete_data" data-id="<?= $row['customer_id'] ?>" data-name="<?= $row['customer_code']." - ".$row['fullname'] ?>" href="javascript:void(0)">Eliminar</a></li>
                            </ul>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
$(function(){
    $('#create_new').click(function(){
        uni_modal('Add New Customer',"index.php?url=customer/manage",'mid-large')
    })
    $('.edit_data').click(function(){
        uni_modal('Edit Customer Details', "index.php?url=customer/manage&id=" + $(this).data('id'), 'mid-large')
    })
    $('.view_data').click(function(){
    uni_modal('Customer Details', "index.php?url=customer/viewCustomer/" + $(this).data('id'), '')
})
    $('.delete_data').click(function(){
        _conf("Are you sure to delete <b>"+$(this).attr('data-name')+"</b> from Customer List?",'delete_data',[$(this).attr('data-id')])
    })
    $('table td,table th').addClass('align-middle')
    $('table').dataTable({
        columnDefs: [
            { orderable: false, targets:3 }
        ]
    })
})

function delete_data(id) {
    $('#confirm_modal button').attr('disabled', true);

    $.ajax({
        url: 'index.php?url=customer/delete',
        method: 'POST',
        data: { id: id },
        dataType: 'json',
        error: err => {
            console.error(err);
            alert("An error occurred.");
            $('#confirm_modal button').attr('disabled', false);
        },
        success: function(resp) {
            if (resp.status === 'success') {
                location.reload();
            } else {
                alert(resp.msg || "An error occurred.");
                $('#confirm_modal button').attr('disabled', false);
            }
        }
    });
}

</script>
