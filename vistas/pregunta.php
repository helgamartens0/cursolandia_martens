<?php
require_once __DIR__ . '/../includes/verificar_sesion.php';
require_once __DIR__ . '/../conexiones/conexion.php';
require_once __DIR__ . '/../controles/ControlCurso.php';
require_once __DIR__ . '/../controles/ControlInscripcion.php';
require_once __DIR__ . '/../controles/ControlPregunta.php';
require_once __DIR__ . '/../controles/ControlRespuesta.php';
require_once __DIR__ . '/../includes/avatar.php';

//viene como pregunta.php?id=7&curso=3 x ejemplooo
$idPregunta = (int) ($_GET['id'] ?? 0);
$idCurso = (int) ($_GET['curso'] ?? 0);

$controlCurso = new ControlCurso($conexion);
$curso = $controlCurso->buscarPorId($idCurso);

if ($curso === null) {
    header('Location: principal.php');
    exit;
}

//igual que en el foro, solo el creador y los inscriptos confirmados
$controlInscripcion = new ControlInscripcion($conexion);
$esCreador = $curso->getIdCreador() == $_SESSION['id_usuario'];
$estaConfirmado = $controlInscripcion->obtenerEstado($_SESSION['id_usuario'], $curso->getId()) === 'confirmada';

if (!$esCreador && !$estaConfirmado) {
    header('Location: curso.php?id=' . $curso->getId());
    exit;
}

$controlPregunta = new ControlPregunta($conexion);
$pregunta = $controlPregunta->buscarDeCurso($idPregunta, $curso->getId());

if ($pregunta === null) {
    header('Location: foro.php?id=' . $curso->getId());
    exit;
}

//le cargo sus respuestas
$controlRespuesta = new ControlRespuesta($conexion);
foreach ($controlRespuesta->obtenerDePregunta($pregunta->getId_pregunta()) as $respuesta) {
    $pregunta->agregarRespuesta($respuesta);
}

//no se puede participar dos veces seguidas (la pregunta cuenta como la primera vez)
$ultimoAutor = $pregunta->autorUltimaRespuesta();
$participeUltimo = $ultimoAutor->getId_usuario() == $_SESSION['id_usuario'];
$ultimaRespuesta = $pregunta->ultimaRespuesta();

//mensajes que deja procesar_respuesta.php
$errores = $_SESSION['errores_pregunta'] ?? [];
unset($_SESSION['errores_pregunta']);
$aviso = $_SESSION['aviso_pregunta'] ?? '';
unset($_SESSION['aviso_pregunta']);

$autor = $pregunta->getAutor();
$paginaActual = 'cursos';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pregunta - Cursolandia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
</head>
<body class="fondo-suave">
    <?php require __DIR__ . '/../includes/navbar.php'; ?>

    <main class="container perfil my-4">

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
            <div>
                <h1 class="h3 mb-0">Pregunta</h1>
                <p class="text-secondary mb-0"><?= htmlspecialchars($curso->getTitulo()) ?></p>
            </div>
            <a href="foro.php?id=<?= $curso->getId() ?>" class="link-registro">← Volver al foro</a>
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

        <!-- LA PREGUNTA -->
        <section class="card mb-4 <?php if ($pregunta->estaDestacada()) { echo 'card-pro'; } ?>">
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
                <p class="mb-0 fs-5"><?= nl2br(htmlspecialchars($pregunta->getTexto())) ?></p>
            </div>
        </section>

        <!-- LAS RESPUESTAS -->
        <h2 class="h5 mb-3">Respuestas (<?= count($pregunta->getRespuestas()) ?>)</h2>

        <?php if (count($pregunta->getRespuestas()) === 0): ?>
            <p class="text-secondary">Todavía nadie respondió.</p>
        <?php endif; ?>

        <?php foreach ($pregunta->getRespuestas() as $respuesta): ?>
            <?php $quien = $respuesta->getAutor(); ?>
            <section class="card mb-3">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <?php mostrarAvatar($quien); ?>
                        <strong><?= htmlspecialchars($quien->getNombre() . ' ' . $quien->getApellido()) ?></strong>
                        <?php if ($quien->getId_usuario() == $curso->getIdCreador()): ?>
                            <span class="badge badge-gratuito">Docente</span>
                        <?php endif; ?>
                        <span class="text-secondary small">· <?= date('d/m/Y H:i', strtotime($respuesta->getFecha_creacion())) ?></span>
                        <?php if ($respuesta->getFecha_ult_edicion() !== null): ?>
                            <span class="text-secondary small">(editada el <?= date('d/m/Y H:i', strtotime($respuesta->getFecha_ult_edicion())) ?>)</span>
                        <?php endif; ?>
                    </div>
                    <p class="mb-0"><?= nl2br(htmlspecialchars($respuesta->getTexto())) ?></p>
                </div>
            </section>
        <?php endforeach; ?>

        <!-- RESPONDER O EDITAR -->
        <section class="card mt-4">
            <div class="card-body p-4">
                <?php if (!$curso->estaActivo()): ?>
                    <p class="text-secondary mb-0">El foro está cerrado: solo se puede participar mientras el curso está en curso.</p>

                <?php elseif ($pregunta->estaCerrada()): ?>
                    <p class="text-secondary mb-0">Esta pregunta está cerrada: ya tiene una respuesta aceptada.</p>

                <?php elseif ($participeUltimo && $ultimaRespuesta === null): ?>
                    <!-- hice la pregunta y nadie respondio todavia -->
                    <p class="text-secondary mb-0">Esperá a que alguien responda tu pregunta.</p>

                <?php elseif ($participeUltimo): ?>
                    <!-- la ultima respuesta es mia, entonces solo puedo editarla -->
                    <h2 class="h5 mb-1">Editar tu última respuesta</h2>
                    <p class="small text-secondary">La última respuesta es tuya. Para agregar algo, editala o esperá a que responda otra persona.</p>
                    <form method="post" action="../procesos/procesar_respuesta.php">
                        <input type="hidden" name="id_pregunta" value="<?= $pregunta->getId_pregunta() ?>">
                        <input type="hidden" name="id_curso" value="<?= $curso->getId() ?>">
                        <input type="hidden" name="accion" value="editar">
                        <textarea name="texto" class="form-control mb-3" rows="3" maxlength="1000" required><?= htmlspecialchars($ultimaRespuesta->getTexto()) ?></textarea>
                        <button type="submit" class="btn btn-iniciar px-4">Guardar cambios</button>
                    </form>
                    
                <?php else: ?>
                    <h2 class="h5 mb-3">Responder</h2>
                    <form method="post" action="../procesos/procesar_respuesta.php">
                        <input type="hidden" name="id_pregunta" value="<?= $pregunta->getId_pregunta() ?>">
                        <input type="hidden" name="id_curso" value="<?= $curso->getId() ?>">
                        <input type="hidden" name="accion" value="responder">
                        <textarea name="texto" class="form-control mb-3" rows="3" maxlength="1000"
                                  placeholder="Escribí tu respuesta..." required></textarea>
                        <button type="submit" class="btn btn-iniciar px-4">Responder</button>
                    </form>
                <?php endif; ?>
            </div>
        </section>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>