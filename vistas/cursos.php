<?php
require_once __DIR__ . '/../includes/verificar_sesion.php';
require_once __DIR__ . '/../conexiones/conexion.php';
require_once __DIR__ . '/../controles/ControlCurso.php';

//lo que se escribio en el buscador de la navbar (cursos.php?q=html)
$busqueda = trim($_GET['q'] ?? '');

$controlCurso = new ControlCurso($conexion);
if ($busqueda === '') {
    $cursos = $controlCurso->obtenerTodos($_SESSION['id_usuario']);
} else {
    $cursos = $controlCurso->buscar($busqueda, $_SESSION['id_usuario']);
}

$paginaActual = 'cursos';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cursos - Cursolandia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
</head>
<body class="fondo-suave">
    <?php require __DIR__ . '/../includes/navbar.php'; ?>

    <main class="container my-4">

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
            <?php if ($busqueda === ''): ?>
                <h1 class="h3 mb-0">Todos los cursos</h1>
            <?php else: ?>
                <h1 class="h3 mb-0">Resultados para "<?= htmlspecialchars($busqueda) ?>" (<?= count($cursos) ?>)</h1>
                <a href="cursos.php" class="link-registro">Ver todos los cursos</a>
            <?php endif; ?>
        </div>

        <?php if (count($cursos) === 0): ?>
            <p class="text-secondary">No encontramos cursos. Probá con otra palabra.</p>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($cursos as $curso): ?>
                    <?php require __DIR__ . '/../includes/tarjeta_curso.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>