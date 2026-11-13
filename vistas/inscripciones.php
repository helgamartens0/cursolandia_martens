<?php
require_once __DIR__ . '/../includes/verificar_sesion.php';
require_once __DIR__ . '/../conexiones/conexion.php';
require_once __DIR__ . '/../controles/ControlCurso.php';
require_once __DIR__ . '/../controles/ControlInscripcion.php';

$idCurso = (int) ($_GET['id'] ?? 0);

$controlCurso = new ControlCurso($conexion);
$curso = $controlCurso->buscarPorId($idCurso);

//esto es xq solo el creador del curso puede ver y confirmar sus inscripciones
if ($curso === null || $curso->getIdCreador() != $_SESSION['id_usuario']) {
    header('Location: principal.php');
    exit;
}

$controlInscripcion = new ControlInscripcion($conexion);
$inscripciones = $controlInscripcion->obtenerDeCurso($curso->getId());

//ahora separo las pendientes de las confirmadas
$pendientes = [];
$confirmadas = [];
foreach ($inscripciones as $inscripcion) {
    if ($inscripcion->estaPendiente()) {
        $pendientes[] = $inscripcion;
    } else {
        $confirmadas[] = $inscripcion;
    }
}

//mensaje que deja procesar_confirmacion.php
$mensaje = $_SESSION['mensaje_inscripciones'] ?? '';
unset($_SESSION['mensaje_inscripciones']);

$paginaActual = 'cursos';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscripciones - Cursolandia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
</head>
<body class="fondo-suave">
    <?php require __DIR__ . '/../includes/navbar.php'; ?>

    <main class="container perfil my-4">

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
            <div>
                <h1 class="h3 mb-0">Inscripciones</h1>
                <p class="text-secondary mb-0"><?= htmlspecialchars($curso->getTitulo()) ?></p>
            </div>
            <a href="curso.php?id=<?= $curso->getId() ?>" class="link-registro">← Volver al curso</a>
        </div>

        <?php if ($mensaje !== ''): ?>
            <div class="alert alert-success" role="alert"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>

        <!-- PENDIENTES -->
        <section class="card mb-4">
            <div class="card-body p-4">
                <h2 class="h5 mb-3">Pendientes (<?= count($pendientes) ?>)</h2>

                <?php if (count($pendientes) === 0): ?>
                    <p class="text-secondary mb-0">No hay inscripciones pendientes.</p>
                <?php endif; ?>

                <?php foreach ($pendientes as $inscripcion): ?>
                    <?php $alumno = $inscripcion->getUsuario(); ?>
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom py-2">
                        <div>
                            <strong><?= htmlspecialchars($alumno->getNombre() . ' ' . $alumno->getApellido()) ?></strong>
                            <span class="text-secondary small d-block">
                                <?= htmlspecialchars($alumno->getEmail()) ?> · pidió el <?= date('d/m/Y', strtotime($inscripcion->getFecha_solicitud())) ?>
                            </span>
                        </div>
                        <div class="d-flex gap-2">
                            <form method="post" action="../procesos/procesar_confirmacion.php">
                                <input type="hidden" name="id_inscripcion" value="<?= $inscripcion->getId_inscripcion() ?>">
                                <input type="hidden" name="id_curso" value="<?= $curso->getId() ?>">
                                <input type="hidden" name="accion" value="confirmar">
                                <button type="submit" class="btn btn-iniciar btn-sm px-3">Confirmar</button>
                            </form>
                            <form method="post" action="../procesos/procesar_confirmacion.php">
                                <input type="hidden" name="id_inscripcion" value="<?= $inscripcion->getId_inscripcion() ?>">
                                <input type="hidden" name="id_curso" value="<?= $curso->getId() ?>">
                                <input type="hidden" name="accion" value="rechazar">
                                <button type="submit" class="btn btn-outline-danger btn-sm px-3">Rechazar</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- CONFIRMADAS -->
        <section class="card">
            <div class="card-body p-4">
                <h2 class="h5 mb-3">Confirmadas (<?= count($confirmadas) ?>)</h2>

                <?php if (count($confirmadas) === 0): ?>
                    <p class="text-secondary mb-0">Todavía no hay inscriptos confirmados.</p>
                <?php endif; ?>

                <?php foreach ($confirmadas as $inscripcion): ?>
                    <?php $alumno = $inscripcion->getUsuario(); ?>
                    <div class="border-bottom py-2">
                        <strong><?= htmlspecialchars($alumno->getNombre() . ' ' . $alumno->getApellido()) ?></strong>
                        <span class="text-secondary small d-block">
                            <?= htmlspecialchars($alumno->getEmail()) ?> · confirmada el <?= date('d/m/Y', strtotime($inscripcion->getFecha_confirmacion())) ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>