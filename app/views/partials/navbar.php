<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary bg-gradient" id="topNavBar">
  <div class="container">
    <a class="navbar-brand" href="index.php?url=home/index">Gasolinera</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav">
        <!-- Inicio (todos) -->

        <!-- Kardex: Admin(1) y Consulta(3) -->
        <?php if (isset($_SESSION['type']) && ($_SESSION['type']==1 || $_SESSION['type']==3)): ?>
        <li class="nav-item">
          <a class="nav-link <?= ($page=='kardex')?'active':'' ?>" href="index.php?url=kardex/index">Kardex</a>
        </li>
        <?php endif; ?>

        <!-- Clientes / Balances: solo Admin(1) -->
        <?php if (isset($_SESSION['type']) && $_SESSION['type']==1): ?>
        <li class="nav-item">
          <a class="nav-link <?= ($page=='customer')?'active':'' ?>" href="index.php?url=customer/index">Clientes</a>
        </li>
        <?php endif; ?>

        <!-- POS: Admin(1) y Cashier(0) -->
        <?php if (isset($_SESSION['type']) && ($_SESSION['type']==1 || $_SESSION['type']==0)): ?>
        <li class="nav-item">
          <a class="nav-link <?= ($page=='sales')?'active':'' ?>" href="index.php?url=sales/index">POS</a>
        </li>
        <?php endif; ?>

        <!-- Ventas (reporte): Admin(1) y Consulta(3) -->
        <?php if (isset($_SESSION['type']) && ($_SESSION['type']==1 || $_SESSION['type']==3)): ?>
        <li class="nav-item">
          <a class="nav-link <?= ($page=='sales_report')?'active':'' ?>" href="index.php?url=salesreport/index">Ventas</a>
        </li>
        <?php endif; ?>

        <!-- Usuarios: solo Admin(1) -->
        <?php if (isset($_SESSION['type']) && $_SESSION['type']==1): ?>
        <li class="nav-item">
          <a class="nav-link <?= ($page=='users')?'active':'' ?>" href="index.php?url=user/index">Usuarios</a>
        </li>
        <?php endif; ?>

        <!-- Proveedores / Compras: Admin(1) y Pedido(4) -->
        <?php if (isset($_SESSION['type']) && ($_SESSION['type']==1 || $_SESSION['type']==4)): ?>
        <li class="nav-item">
          <a class="nav-link <?= ($page=='suppliers')?'active':'' ?>" href="index.php?url=supplier/index">Proveedores</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= ($page=='purchases')?'active':'' ?>" href="index.php?url=purchase/index">Compras</a>
        </li>
        <?php endif; ?>

        <!-- Mantenimientos (tipos, catálogo contenedores): Admin(1) y Mantenimiento(2) -->
        <?php if (isset($_SESSION['type']) && ($_SESSION['type']==1 || $_SESSION['type']==2)): ?>
        <li class="nav-item">
          <a class="nav-link <?= ($page=='maintenance')?'active':'' ?>" href="index.php?url=maintenance/index">Mantenimientos</a>
        </li>
        <?php endif; ?>

        <!-- Contenedores (operar transfer/ajustes): Admin(1) y Abastecimiento(5) -->
        <?php if (isset($_SESSION['type']) && ($_SESSION['type']==1 || $_SESSION['type']==5)): ?>
        <li class="nav-item">
          <a class="nav-link <?= ($page=='containers')?'active':'' ?>" href="index.php?url=containers/index">Contenedores</a>
        </li>
        <?php endif; ?>

        <!-- Empleados / Puestos: solo Admin(1) -->
        <?php if (isset($_SESSION['type']) && $_SESSION['type']==1): ?>
        <li class="nav-item">
          <a class="nav-link <?= ($page=='employees')?'active':'' ?>" href="index.php?url=employee/index">Empleados</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= ($page=='positions')?'active':'' ?>" href="index.php?url=position/index">Puestos</a>
        </li>
        <?php endif; ?>
      </ul>
    </div>

    <div>
      <div class="dropdown">
        <button class="btn btn-secondary dropdown-toggle bg-transparent text-light border-0" type="button" data-bs-toggle="dropdown">
          <?= $_SESSION['fullname'] ?? 'User' ?>
        </button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="index.php?url=account/index">Administrar Cuenta</a></li>
          <li><a class="dropdown-item" href="index.php?url=auth/logout">Logout</a></li>
        </ul>
      </div>
    </div>
  </div>
</nav>
