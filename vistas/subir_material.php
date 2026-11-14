<?php
require_once __DIR__ . '/../includes/verificar_sesion.php';
require_once __DIR__ . '/../conexiones/conexion.php';
require_once __DIR__ . '/../controles/ControlCurso.php';
require_once __DIR__ . '/../controles/ControlArchivo.php';

$idCurso = (int) ($_GET['id'] ?? 0);

$controlCurso = new ControlCurso($conexion);
$curso = $controlCurso->buscarPorId($idCurso);

//si el curso no existe o no es del logueado(?, no puedo subir nada
if ($curso === null || $curso->getIdCreador() != $_SESSION['id_usuario']) {
    header('Location: principal.php');
    exit;
}

//material que ya tiene el curso
$controlArchivo = new ControlArchivo($conexion);
foreach ($controlArchivo->obtenerDeCurso($curso->getId()) as $archivo) {
    $curso->agregarArchivo($archivo);
}

//para los controles de tamaño etc
$esPro = $curso->getCreador()->esPro();

//mensajes que deja procesar_material.php
$errores = $_SESSION['errores_material'] ?? [];
unset($_SESSION['errores_material']);
$aviso = $_SESSION['aviso_material'] ?? '';
unset($_SESSION['aviso_material']);

$paginaActual = 'cursos';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Material del curso - Cursolandia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
</head>
<body class="fondo-suave">
    <?php require __DIR__ . '/../includes/navbar.php'; ?>

    <main class="container perfil my-4">

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
            <div>
                <h1 class="h3 mb-0">Material del curso</h1>
                <p class="text-secondary mb-0"><?= htmlspecialchars($curso->getTitulo()) ?></p>
            </div>
            <a href="curso.php?id=<?= $curso->getId() ?>" class="link-registro">Volver al curso</a>
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

        <div class="row g-4">

            <!-- IZQUIERDA: formulario para subir -->
            <div class="col-lg-5">
                <section class="card">
                    <div class="card-body p-4">
                        <h2 class="h5 mb-3">Subir un archivo</h2>

                        <?php if (!$esPro): ?>
                            <!-- limites del plan gratuito -->
                            <p class="small text-secondary">
                                Con el plan gratuito podés subir hasta <?= Curso::MAX_ARCHIVOS ?> archivos de <?= Archivo::MAX_MB ?> MB cada uno,
                                y <?= Curso::MAX_MB_TOTAL ?> MB entre todos.
                            </p>
                            <p class="small mb-1">Usado: <?= number_format($curso->tamanioTotalArchivos(), 2, ',', '.') ?> MB de <?= Curso::MAX_MB_TOTAL ?> MB</p>
                            <div class="progress mb-3" role="progressbar" aria-label="Espacio usado">
                                <div class="progress-bar" style="width: <?= $curso->tamanioTotalArchivos() * 100 / Curso::MAX_MB_TOTAL ?>%"></div>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="../procesos/procesar_material.php" enctype="multipart/form-data">
                            <input type="hidden" name="id_curso" value="<?= $curso->getId() ?>">
                            <div class="mb-3">
                                <label for="archivo" class="form-label">Archivo</label>
                                <input type="file" class="form-control" id="archivo" name="archivo"
                                       accept=".pdf,.jpg,.jpeg,.png,.docx,.pptx,.zip" required>
                                <div class="form-text">PDF, imagen, Word, PowerPoint o ZIP.</div>
                            </div>
                            <button type="submit" class="btn btn-iniciar w-100">Subir</button>
                        </form>
                    </div>
                </section>
            </div>

            <!-- DERECHA: lo que ya se subio -->
            <div class="col-lg-7">
                <section class="card">
                    <div class="card-body p-4">
                        <h2 class="h5 mb-3">Archivos subidos (<?= count($curso->getArchivos()) ?>)</h2>

                        <?php if (count($curso->getArchivos()) === 0): ?>
                            <p class="text-secondary mb-0">Todavía no subiste material.</p>
                        <?php endif; ?>

                        <?php foreach ($curso->getArchivos() as $archivo): ?>
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom py-2">
                                <div>
                                    <a href="descargar_material.php?id=<?= $archivo->getId_archivo() ?>&curso=<?= $curso->getId() ?>" class="link-registro">
                                        <?= htmlspecialchars($archivo->getNombre()) ?>
                                    </a>
                                    <span class="text-secondary small d-block">
                                        <?= number_format($archivo->getTamanio_mb(), 2, ',', '.') ?> MB · <?= date('d/m/Y', strtotime($archivo->getFecha_carga())) ?>
                                    </span>
                                </div>
                                <form method="post" action="../procesos/procesar_eliminar_material.php">
                                    <input type="hidden" name="id_archivo" value="<?= $archivo->getId_archivo() ?>">
                                    <input type="hidden" name="id_curso" value="<?= $curso->getId() ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Eliminar</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>

        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>