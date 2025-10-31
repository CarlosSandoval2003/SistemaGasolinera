<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary bg-gradient" id="topNavBar">
  <div class="container">
    <a class="navbar-brand" href="index.php?url=home/index">Gasolinera</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav">

        <!-- Kardex -->
        <?php if (isset($_SESSION['type']) && in_array($_SESSION['type'], [1,3])): ?>
        <li class="nav-item">
          <a class="nav-link <?= ($page=='kardex')?'active':'' ?>" href="index.php?url=kardex/index">Kardex</a>
        </li>
        <?php endif; ?>

        <!-- Clientes -->
        <?php if (isset($_SESSION['type']) && $_SESSION['type']==1): ?>
        <li class="nav-item">
          <a class="nav-link <?= ($page=='customer')?'active':'' ?>" href="index.php?url=customer/index">Clientes</a>
        </li>
        <?php endif; ?>

        <!-- POS -->
        <?php if (isset($_SESSION['type']) && in_array($_SESSION['type'], [0,1])): ?>
        <li class="nav-item">
          <a class="nav-link <?= ($page=='sales')?'active':'' ?>" href="index.php?url=sales/index">POS</a>
        </li>
        <?php endif; ?>

        <!-- Ventas -->
        <?php if (isset($_SESSION['type']) && in_array($_SESSION['type'], [1,3])): ?>
        <li class="nav-item">
          <a class="nav-link <?= ($page=='sales_report')?'active':'' ?>" href="index.php?url=salesReport/index">Ventas</a>
        </li>
        <?php endif; ?>
         
          
                  <!-- Compras: Admin(1) y Pedido(4) -->
        <?php if (isset($_SESSION['type']) && ($_SESSION['type']==1 || $_SESSION['type']==4)): ?>
        <li class="nav-item">
          <a class="nav-link <?= ($page=='purchases')?'active':'' ?>" href="index.php?url=purchase/index">Compras</a>
        </li>
        <?php endif; ?>

        <!-- Contenedores -->
        <?php if (isset($_SESSION['type']) && in_array($_SESSION['type'], [1,5])): ?>
        <li class="nav-item">
          <a class="nav-link <?= ($page=='containers')?'active':'' ?>" href="index.php?url=containers/index">Contenedores</a>
        </li>
        <?php endif; ?>

        <!-- 🔽 Mantenimientos agrupados -->
        <?php if (isset($_SESSION['type']) && $_SESSION['type']==1): ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?= in_array($page, ['maintenance','employees','positions','users','suppliers']) ? 'active' : '' ?>"
             href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Mantenimientos
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item <?= ($page=='maintenance')?'active':'' ?>" href="index.php?url=maintenance/index"><i class="fa fa-gas-pump me-2"></i>Gasolina</a></li>
            <li><a class="dropdown-item <?= ($page=='employees')?'active':'' ?>" href="index.php?url=employee/index"><i class="fa fa-users me-2"></i>Empleados</a></li>
            <li><a class="dropdown-item <?= ($page=='positions')?'active':'' ?>" href="index.php?url=position/index"><i class="fa fa-briefcase me-2"></i>Puestos</a></li>
            <li><a class="dropdown-item <?= ($page=='users')?'active':'' ?>" href="index.php?url=user/index"><i class="fa fa-user-cog me-2"></i>Usuarios</a></li>
            <li><a class="dropdown-item <?= ($page=='suppliers')?'active':'' ?>" href="index.php?url=supplier/index"><i class="fa fa-truck me-2"></i>Proveedores</a></li>
          </ul>
        </li>
        <?php elseif (isset($_SESSION['type']) && $_SESSION['type']==2): ?>
        <!-- Tipo mantenimiento (rol 2) ve solo gasolina -->
        <li class="nav-item">
          <a class="nav-link <?= ($page=='maintenance')?'active':'' ?>" href="index.php?url=maintenance/index">Mantenimientos</a>
        </li>
        <?php endif; ?>

      </ul>
    </div>

    <div>
      <div class="dropdown">
        <button class="btn btn-secondary dropdown-toggle bg-transparent text-light border-0" type="button" data-bs-toggle="dropdown">
          <?= $_SESSION['fullname'] ?? 'User' ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="index.php?url=account/index">Administrar Cuenta</a></li>
          <li><a class="dropdown-item" href="index.php?url=auth/logout">Cerrar Sesión</a></li>
        </ul>
      </div>
    </div>
  </div>
</nav>
