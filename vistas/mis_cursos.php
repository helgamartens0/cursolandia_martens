<?php
require_once __DIR__ . '/../includes/verificar_sesion.php';
require_once __DIR__ . '/../conexiones/conexion.php';
require_once __DIR__ . '/../controles/ControlUsuario.php';
require_once __DIR__ . '/../controles/ControlCurso.php';

$controlUsuario = new ControlUsuario($conexion);
$usuario = $controlUsuario->buscarPorId($_SESSION['id_usuario']);

if ($usuario === null) {
    header('Location: cerrar_sesion.php');
    exit;
}

$controlCurso = new ControlCurso($conexion);
$tomados = $controlCurso->obtenerTomadosPor($usuario->getId_usuario());
$creados = $controlCurso->obtenerCreadosPor($usuario->getId_usuario());

//RN01: para saber si le muestro el boton de crear curso
$cantidad = $controlUsuario->contarCursosCreados($usuario->getId_usuario());
$puedeCrear = $usuario->puedeCrearCurso($cantidad);

$paginaActual = 'cursos';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis cursos - Cursolandia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
</head>
<body>
    <?php require __DIR__ . '/../includes/navbar.php'; ?>
        
    <main class="container my-4">
        
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
            <h1 class="h3 mb-0">Mis cursos</h1>
            <a href="principal.php" class="link-registro">Volver</a>
        </div>
        
        
        <!-- CURSOS EN LOS Q ESTA INSCRIPTO -->
        <section class="mb-5">
            <h2 class="h3 mb-3">Mis inscripciones</h2>
            <?php if (count($tomados) === 0): ?>
                <p class="text-secondary">Todavía no te inscribiste en ningún curso.</p>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($tomados as $curso): ?>
                        <?php require __DIR__ . '/../includes/tarjeta_curso.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- CURSOS QUE CREO -->
        <section>
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <h2 class="h3 mb-0">Cursos que creé</h2>
                <?php if ($puedeCrear): ?>
                    <a href="crear_curso.php" class="btn btn-iniciar px-4 d-flex align-items-center">+ Crear curso</a>
                <?php else: ?>
                    <span class="small text-secondary">Llegaste al límite de <?= Usuario::LIMITE_GRATUITO ?> cursos activos. <a href="solicitar_pro.php" class="link-registro">Pasate a Pro</a></span>
                <?php endif; ?>
            </div>

            <?php if (count($creados) === 0): ?>
                <p class="text-secondary">Todavía no creaste ningún curso.</p>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($creados as $curso): ?>
                        <?php require __DIR__ . '/../includes/tarjeta_curso.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>