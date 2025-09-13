<?php
session_start();
if (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0) {
    header("Location: /");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LOGIN | Gasolinera</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <script src="js/jquery-3.6.0.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/script.js"></script>
    <style>
        html, body { height: 100%; }
        .pop_msg {
            margin-top: 10px;
            display: none;
        }
    </style>
</head>
<body class="bg-dark bg-gradient">
<div class="h-100 d-flex justify-content-center align-items-center">
    <div class="w-100">
        <h3 class="py-5 text-center text-light">Gasolinera</h3>
        <div class="card my-3 col-md-4 offset-md-4">
            <div class="card-body">
                <form action="" id="login-form">
                    <center><small>Por favor, ingresa tus credenciales.</small></center>
                    <div class="form-group">
                        <label for="username" class="control-label">Usuario</label>
                        <input type="text" id="username" autofocus name="username" class="form-control form-control-sm rounded-0" required>
                    </div>
                    <div class="form-group">
                        <label for="password" class="control-label">Contraseña</label>
                        <input type="password" id="password" name="password" class="form-control form-control-sm rounded-0" required>
                    </div>
                    <div class="form-group d-flex w-100 justify-content-end">
                        <button class="btn btn-sm btn-primary rounded-0 my-1" type="submit">Login</button>
                    </div>
                </form>
                <div id="responseMsg" class="pop_msg alert"></div>
            </div>
        </div>
    </div>
</div>

<script>
$(function(){
    $('#login-form').submit(function(e){
        e.preventDefault();
        $('#responseMsg').removeClass('alert-success alert-danger').hide();
        let _this = $(this);
        let $btn = _this.find('button[type="submit"]');
        $btn.attr('disabled', true).text('Logging in...');

        $.ajax({
            url: 'index.php?url=auth/login',
            method: 'POST',
            data: _this.serialize(),
            dataType: 'JSON',
            success: function(resp) {
                if (resp.status === 'success') {
                    $('#responseMsg').addClass('alert alert-success').text(resp.msg).slideDown();
                    setTimeout(() => location.href = 'index.php?url=home/index', 1200);
                } else {
                    $('#responseMsg').addClass('alert alert-danger').text(resp.msg).slideDown();
                }
            },
            error: function() {
                $('#responseMsg').addClass('alert alert-danger').text("Error de conexión.").slideDown();
            },
            complete: function() {
                $btn.attr('disabled', false).text('Login');
            }
        });
    });
});
</script>
</body>
</html>
