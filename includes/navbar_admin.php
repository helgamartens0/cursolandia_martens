<nav class="navbar">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center gap-2" href="panel_admin.php">
      <img class="logo" src="../img/logo-cursolandia-navbar.png" alt="Cursolandia">
      <span class="badge">ADMIN</span>
    </a>
    <div class="d-flex align-items-center gap-3">
      <span class="text-white d-none d-sm-inline">Hola, <?= htmlspecialchars($_SESSION['nombre']) ?></span>
      <a href="cerrar_sesion.php" class="btn btn-light btn-sm fw-semibold">Cerrar sesión</a>
    </div>
  </div>
</nav>