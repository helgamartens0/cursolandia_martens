<?php
// cada página dice en $paginaActual cuál es ('cursos' o 'perfil') antes de incluir el navbar
$paginaActual = $paginaActual ?? '';

//cuantas notificaciones sin leer tiene (para el numerito de la campanita)
require_once __DIR__ . '/../controles/ControlNotificacion.php';
$controlNotiNavbar = new ControlNotificacion($conexion);
$notiSinLeer = $controlNotiNavbar->contarNoLeidas($_SESSION['id_usuario']);

?>
<nav class="navbar navbar-expand-lg">
  <div class="container-fluid">
    <a class="navbar-brand" href="principal.php"><img class="logo" src="../img/logo-cursolandia-navbar.png" alt="Cursolandia"></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        
        <li class="nav-item dropdown">
          <a class= "nav-link dropdown-toggle <?= $paginaActual === 'cursos' ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" >
            CURSOS
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="mis_cursos.php">Ver mis cursos</a></li>
            <li><a class="dropdown-item" href="crear_curso.php">Crear curso</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="cursos.php">Ver todos los cursos</a></li>
          </ul>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $paginaActual === 'perfil' ? 'active' : '' ?>" href="perfil.php">PERFIL</a>
        </li>
      </ul>

      <form class="d-flex" method="get" action="cursos.php">
        <input class="form-control me-2" type="search" name="q" placeholder="Buscar cursos..."
               value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
      </form>
            
      
      <!-- campanita con las notificaciones sin leer -->
      <a href="notificaciones.php" class="campanita position-relative me-3">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        <?php if ($notiSinLeer > 0): ?>
          <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= $notiSinLeer ?></span>
        <?php endif; ?>
      </a>
      
      
<!-- para el avatar con las iniciales -> nos lleva al perfil yy para cerrar sesion -->
      <div class="dropdown">
        <button class="avatar border-0 p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Menú de usuario">
                <?php if (($_SESSION['foto'] ?? '') !== ''): ?>
                  <img src="../<?= htmlspecialchars($_SESSION['foto']) ?>" alt="Mi foto">
                <?php else: ?>
                  <?= htmlspecialchars($_SESSION['iniciales']) ?>
                <?php endif; ?>
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