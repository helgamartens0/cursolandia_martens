<nav class="navbar navbar-expand-lg">
  <div class="container-fluid">
    <a class="navbar-brand" href="principal.php"><img class="logo" src="../img/logo-cursolandia-navbar.png" alt="Cursolandia"></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle active" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" >
            CURSOS
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#">Ver mis cursos</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="#">Ver todos los cursos</a></li>
          </ul>
        </li>
        <li class="nav-item">
          <a class="nav-link"  href="#">PERFIL</a>
        </li>
      </ul>
      <form class="d-flex" role="search">
        <input class="form-control me-2" type="search" placeholder="Buscar cursos..." >
      </form>
      
<!-- para el avatar con las iniciales -> nos lleva al perfil yy para cerrar sesion -->
      <div class="dropdown">
        <button class="avatar border-0 p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Menú de usuario">
        <?= htmlspecialchars($_SESSION['iniciales']) ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><span class="dropdown-item-text">Hola, <?= htmlspecialchars($_SESSION['nombre']) ?></span></li>
          <li><hr class="dropdown-divider"></li> 
          <li><a class="dropdown-item" href="perfil.php">Ver perfil</a></li>
          <li><a class="dropdown-item" href="cerrar_sesion.php">Cerrar sesi&oacute;n</a></li>
        </ul>
      </div>
    </div>
  </div>
</nav>