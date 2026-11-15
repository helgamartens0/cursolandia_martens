<?php
require_once __DIR__ . '/../includes/verificar_sesion.php';
require_once __DIR__ . '/../conexiones/conexion.php';
require_once __DIR__ . '/../controles/ControlCurso.php';
require_once __DIR__ . '/../controles/ControlEtiqueta.php';

$controlCurso = new ControlCurso($conexion);
$destacados = $controlCurso->obtenerDestacados($_SESSION['id_usuario']);
$misCursos  = $controlCurso->obtenerActivosDeInscripto($_SESSION['id_usuario']);

//recomendados segun los intereses (si no cargo ninguno, le aviso que los agregue)
$recomendados = $controlCurso->obtenerRecomendados($_SESSION['id_usuario']);
$controlEtiqueta = new ControlEtiqueta($conexion);
$cantidadIntereses = count($controlEtiqueta->obtenerInteresesDeUsuario($_SESSION['id_usuario']));

$paginaActual = 'cursos'; // para que el navbar marque CURSOS
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Principal - Cursolandia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="../css/styles.css" rel="stylesheet">
</head>
<body>
    <?php require __DIR__ . '/../includes/navbar.php'; ?>

    <main class="container my-4">

        <!-- Cursos de usuarios Pro -->
        <section class="mb-5">
            <h2 class="h3 mb-3">Cursos PRO destacados</h2>
            <?php if (count($destacados) === 0): ?>
                <p class="text-secondary">Por ahora no hay cursos destacados.</p>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($destacados as $curso): ?>
                        <?php require __DIR__ . '/../includes/tarjeta_curso.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Recomendados segun  intereses -->
        <section class="mb-5">
            <h2 class="h3 mb-3">Recomendados para vos</h2>
            <?php if ($cantidadIntereses === 0): ?>
                <p class="text-secondary">Contanos qué te interesa y te recomendamos cursos. <a href="editar_perfil.php">Agregar intereses</a></p>
            <?php elseif (count($recomendados) === 0): ?>
                <p class="text-secondary">Por ahora no hay cursos que coincidan con tus intereses. <a href="cursos.php">Ver todos los cursos</a></p>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($recomendados as $curso): ?>
                        <?php require __DIR__ . '/../includes/tarjeta_curso.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
        
        <!-- Cursos activos en los que el usuario está inscripto -->
        <section>
            <h2 class="h3 mb-3">Mis cursos activos</h2>
            <?php if (count($misCursos) === 0): ?>
                <p class="text-secondary">Todavía no estás inscripto en ningún curso activo. <a href="cursos.php">Ver todos los cursos</a></p>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($misCursos as $curso): ?>
                        <?php require __DIR__ . '/../includes/tarjeta_curso.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>