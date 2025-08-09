<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'Petrol Station' ?></title>
    <link rel="stylesheet" href="/psms/public/css/bootstrap.min.css">
    <link rel="stylesheet" href="/psms/public/DataTables/datatables.min.css">
    <link rel="stylesheet" href="/psms/public/Font-Awesome-master/css/all.min.css">
    <script src="/psms/public/js/jquery-3.6.0.min.js"></script>
    <script src="/psms/public/js/popper.min.js"></script>
    <script src="/psms/public/js/bootstrap.min.js"></script>
    <script src="/psms/public/DataTables/datatables.min.js"></script>
    <script src="/psms/public/Font-Awesome-master/js/all.min.js"></script>
    <script src="/psms/public/js/script.js"></script>
    <style>
        html, body { height: 100%; width: 100%; }
        main { height: 100%; display: flex; flex-direction: column; }
        #page-container { flex: 1 1 auto; overflow: auto; }
        #topNavBar { flex: 0 1 auto; }
        .truncate-1 { overflow: hidden; text-overflow: ellipsis; -webkit-line-clamp: 1; display: -webkit-box; -webkit-box-orient: vertical; }
        .truncate-3 { overflow: hidden; text-overflow: ellipsis; -webkit-line-clamp: 3; display: -webkit-box; -webkit-box-orient: vertical; }
        .modal-dialog.large { width: 80% !important; }
        .modal-dialog.mid-large { width: 50% !important; }
        @media (max-width: 720px) {
            .modal-dialog.large, .modal-dialog.mid-large { width: 100% !important; }
        }
    </style>
</head>
<body>
    <main>
        <!-- Navbar -->
        <?php include '../app/views/partials/navbar.php'; ?>

        <!-- Contenido dinámico -->
        <div class="container py-3" id="page-container">
            <?php if (!empty($content)) echo $content; ?>
        </div>
    </main>

    <!-- Modales globales -->
    <?php include '../app/views/partials/modals.php'; ?>
</body>
</html>
