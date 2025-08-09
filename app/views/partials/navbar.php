<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary bg-gradient" id="topNavBar">
        <div class="container">
            <a class="navbar-brand" href="index.php?url=home/index">
            Petrol Station
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($page == 'home')? 'active' : '' ?>" aria-current="page" href="index.php?url=home/index">Inicio</a>
                    </li>
                    <?php if(isset($_SESSION['type']) && $_SESSION['type'] == 1): ?>
                    <li class="nav-item">
                        <a class="nav-link  <?php echo ($page == 'customer')? 'active' : '' ?>" href="index.php?url=customer/index">Clientes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link  <?php echo ($page == 'balances')? 'active' : '' ?>" href="index.php?url=balance/index">Blances</a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link  <?php echo ($page == 'sales')? 'active' : '' ?>" href="index.php?url=sales/index">POS</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link  <?php echo ($page == 'sales_report')? 'active' : '' ?>" href="index.php?url=salesreport/index">Ventas</a>
                    </li>
                    <?php if($_SESSION['type'] == 1): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($page == 'users')? 'active' : '' ?>" aria-current="page" href="index.php?url=user/index">Usuarios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?url=petroltype/index">Mantenimientos</a>
                    </li>
                    <?php endif; ?>
                    
                </ul>
            </div>
            <div>
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle bg-transparent  text-light border-0" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                    Hello <?php echo $_SESSION['fullname'] ?? 'User' ?>
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                    <li><a class="dropdown-item" href="index.php?url=account/index">Manage Account</a></li>
                    <li><a class="dropdown-item" href="index.php?url=auth/logout">Logout</a></li>
                </ul>
            </div>
            </div>
        </div>
    </nav>