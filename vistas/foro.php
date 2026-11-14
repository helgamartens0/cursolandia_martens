<?php
require_once __DIR__ . '/../includes/verificar_sesion.php';
require_once __DIR__ . '/../conexiones/conexion.php';
require_once __DIR__ . '/../controles/ControlCurso.php';
require_once __DIR__ . '/../controles/ControlInscripcion.php';
require_once __DIR__ . '/../controles/ControlPregunta.php';
require_once __DIR__ . '/../includes/avatar.php';

$idCurso = (int) ($_GET['id'] ?? 0);

$controlCurso = new ControlCurso($conexion);
$curso = $controlCurso->buscarPorId($idCurso);

if ($curso === null) {
    header('Location: principal.php');
    exit;
}

//al foro entran: el creador y los inscriptos confirmados
$controlInscripcion = new ControlInscripcion($conexion);
$esCreador = $curso->getIdCreador() == $_SESSION['id_usuario'];
$estaConfirmado = $controlInscripcion->obtenerEstado($_SESSION['id_usuario'], $curso->getId()) === 'confirmada';

if (!$esCreador && !$estaConfirmado) {
    header('Location: curso.php?id=' . $curso->getId());
    exit;
}

$controlPregunta = new ControlPregunta($conexion);
$preguntas = $controlPregunta->obtenerDeCurso($curso->getId());

//mensajes que dejan los procesos del foro
$errores = $_SESSION['errores_foro'] ?? [];
unset($_SESSION['errores_foro']);
$aviso = $_SESSION['aviso_foro'] ?? '';
unset($_SESSION['aviso_foro']);

$paginaActual = 'cursos';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foro - Cursolandia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
</head>
<body class="fondo-suave">
    <?php require __DIR__ . '/../includes/navbar.php'; ?>

    <main class="container perfil my-4">

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
            <div>
                <h1 class="h3 mb-0">Foro (<?= count($preguntas) ?>)</h1>
                <p class="text-secondary mb-0"><?= htmlspecialchars($curso->getTitulo()) ?></p>
            </div>
            <a href="curso.php?id=<?= $curso->getId() ?>" class="link-registro">← Volver al curso</a>
        </div>

        <?php if ($aviso !== ''): ?>
            <div class="alert alert-success" role="alert"><?= htmlspecialchars($aviso) ?></div>
        <?php endif; ?>

        <?php if (count($errores) > 0): ?>
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0 ps-3">
                    <?php foreach ($errores as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- NUEVA PREGUNTA -->
        <?php if (!$curso->estaActivo()): ?>
            <!-- solo se participa mientras el curso esta activo -->
            <div class="alert alert-secondary" role="alert">El foro está cerrado: solo se puede participar mientras el curso está en curso.</div>

        <?php elseif ($esCreador): ?>
            <!-- iniciar preguntas es solo para los inscriptos -->
            <p class="text-secondary">Como creador del curso podés responder, destacar y eliminar preguntas.</p>

        <?php else: ?>
            <section class="card mb-4">
                <div class="card-body p-4">
                    <h2 class="h5 mb-3">Hacer una pregunta</h2>
                    <form method="post" action="../procesos/procesar_pregunta.php">
                        <input type="hidden" name="id_curso" value="<?= $curso->getId() ?>">
                        <textarea name="texto" class="form-control mb-3" rows="3" maxlength="1000"
                                  placeholder="Escribí tu pregunta..." required></textarea>
                        <button type="submit" class="btn btn-iniciar px-4">Publicar</button>
                    </form>
                </div>
            </section>
        <?php endif; ?>

        <!-- LISTA DE PREGUNTAS: primero las destacadas -->
        <?php if (count($preguntas) === 0): ?>
            <p class="text-secondary">Todavía no hay preguntas en este foro.</p>
        <?php endif; ?>

        <?php foreach ($preguntas as $pregunta): ?>
            <?php $autor = $pregunta->getAutor(); ?>
            <section class="card mb-3 <?php if ($pregunta->estaDestacada()) { echo 'card-pro'; } ?>">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <?php mostrarAvatar($autor); ?>
                        <strong><?= htmlspecialchars($autor->getNombre() . ' ' . $autor->getApellido()) ?></strong>
                        <span class="text-secondary small">· <?= date('d/m/Y H:i', strtotime($pregunta->getFecha_creacion())) ?></span>

                        <?php if ($pregunta->estaDestacada()): ?>
                            <span class="badge">Destacada</span>
                        <?php endif; ?>
                        <?php if ($pregunta->estaCerrada()): ?>
                            <span class="badge badge-gratuito">Cerrada</span>
                        <?php endif; ?>
                    </div>

                    <p class="mb-2"><?= nl2br(htmlspecialchars($pregunta->getTexto())) ?></p>

                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <span class="text-secondary small"><?= $controlPregunta->contarRespuestas($pregunta->getId_pregunta()) ?> respuestas</span>
                        <a href="pregunta.php?id=<?= $pregunta->getId_pregunta() ?>&curso=<?= $curso->getId() ?>" class="link-registro">Ver pregunta →</a>
                    </div>
                </div>
            </section>
        <?php endforeach; ?>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>