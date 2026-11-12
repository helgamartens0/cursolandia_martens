<?php
require_once __DIR__ . '/../includes/verificar_sesion.php';
require_once __DIR__ . '/../conexiones/conexion.php';
require_once __DIR__ . '/../controles/ControlCurso.php';
require_once __DIR__ . '/../controles/ControlEtiqueta.php';
require_once __DIR__ . '/../controles/ControlUsuario.php';
require_once __DIR__ . '/../controles/ControlInscripcion.php';

//el id del curso viene en la direccion: curso.php?id=5
$idCurso = (int) ($_GET['id'] ?? 0);

$controlCurso = new ControlCurso($conexion);
$curso = $controlCurso->buscarPorId($idCurso);

//si el curso no existe, vuelvo a la principal
if ($curso === null) {
    header('Location: principal.php');
    exit;
}

$controlEtiqueta = new ControlEtiqueta($conexion);
$etiquetas = $controlEtiqueta->obtenerEtiquetasDeCurso($curso->getId());

//$esCreador xq solo el creador sube material y confirma inscripciones 
$esCreador = $curso->getIdCreador() == $_SESSION['id_usuario'];

//datos para la inscripcion
$controlUsuario = new ControlUsuario($conexion);
$controlInscripcion = new ControlInscripcion($conexion);

$usuario = $controlUsuario->buscarPorId($_SESSION['id_usuario']);
$estadoInscripcion = $controlInscripcion->obtenerEstado($_SESSION['id_usuario'], $curso->getId());  //null, 'pendiente' o 'confirmada'
$inscriptos = $controlInscripcion->contarInscriptos($curso->getId());
$inscripcionesActivas = $controlUsuario->contarInscripcionesActivas($_SESSION['id_usuario']);

//error que deja procesar_inscripcion.php (se muestra una sola vez)
$errorCurso = $_SESSION['error_curso'] ?? '';
unset($_SESSION['error_curso']);

//aviso que deja procesar_crear_curso.php (se muestra una sola vez)
$aviso = $_SESSION['aviso_curso'] ?? '';
unset($_SESSION['aviso_curso']);

//textos para mostrar
$creador = $curso->getCreador();
$hoy = date('Y-m-d');

if ($curso->esGratuito()) {
    $precio = 'Gratis';
} else {
    $precio = '$ ' . number_format($curso->getCosto(), 2, ',', '.');
}

if ($curso->getCupo() === null) {
    $cupo = 'Sin límite';
} else {
        $cupo = $inscriptos . ' de ' . $curso->getCupo() . ' lugares';
}

if ($curso->estaActivo()) {
    $estado = 'En curso';
} elseif ($curso->getFechaInicio() > $hoy) {
    $estado = 'Empieza el ' . date('d/m/Y', strtotime($curso->getFechaInicio()));
} else {
    $estado = 'Terminado';
}

$paginaActual = 'cursos';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($curso->getTitulo()) ?> - Cursolandia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
</head>
<body class="fondo-suave">
    <?php require __DIR__ . '/../includes/navbar.php'; ?>
        
    <main class="container perfil my-4">
        <div class="d-flex justify-content-end mb-3">
            <a href="mis_cursos.php" class="link-registro">Volver a mis cursos</a>
        </div>

        <!-- aviso (ej: "Tu curso fue creado") -->
        <?php if ($aviso !== ''): ?>
            <div class="alert aviso-exito alert-dismissible fade show d-flex align-items-center gap-3 mb-4" role="alert">
                <span class="aviso-icono">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
                </span>
                <strong><?= htmlspecialchars($aviso) ?></strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        <?php endif; ?>
        <?php if ($errorCurso !== ''): ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($errorCurso) ?></div>
        <?php endif; ?>

        <!-- ENCABEZADO: titulo, creador y estado -->
        <section class="card mb-4 <?php if ($curso->esDestacado()) { echo 'card-pro'; } else { echo 'curso'; } ?>">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <h1 class="h3 mb-0"><?= htmlspecialchars($curso->getTitulo()) ?></h1>
                    <?php if ($curso->esDestacado()): ?>
                        <span class="badge">PRO</span>
                    <?php endif; ?>
                </div>
                <p class="text-secondary mb-2">Por <?= htmlspecialchars($creador->getNombre() . ' ' . $creador->getApellido()) ?></p>
                <span class="badge badge-gratuito"><?= $estado ?></span>
            </div>
        </section>

        <div class="row g-4">

            <!-- IZQUIERDA: descripcion y etiquetas -->
            <div class="col-lg-8 d-flex flex-column gap-4">
                <section class="card">
                    <div class="card-body p-4">
                        <h2 class="h5 mb-3">Sobre el curso</h2>
                        <p><?= nl2br(htmlspecialchars($curso->getDescripcion())) ?></p>

                        <?php if (count($etiquetas) > 0): ?>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($etiquetas as $etiqueta): ?>
                                    <span class="chip"><?= htmlspecialchars($etiqueta->getNombre()) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- aca despues van el MATERIAL (CU-10) y el FORO (CU-13 a 18) -->
            </div>

            <!-- DERECHA: datos del curso y lo que puede hacer -->
            <div class="col-lg-4 d-flex flex-column gap-4">
                <section class="card">
                    <div class="card-body p-4">
                        <h2 class="h5 mb-3">Datos</h2>
                        <dl class="mb-0">
                            <dt class="dato-titulo">Costo</dt>
                            <dd class="mb-3"><?= $precio ?></dd>

                            <dt class="dato-titulo">Cupo</dt>
                            <dd class="mb-3"><?= $cupo ?></dd>

                            <dt class="dato-titulo">Fechas</dt>
                            <dd class="mb-3">Del <?= date('d/m/Y', strtotime($curso->getFechaInicio())) ?> al <?= date('d/m/Y', strtotime($curso->getFechaFin())) ?></dd>

                            <dt class="dato-titulo">Acceso</dt>
                            <dd class="mb-0"><?php if ($curso->esPublico()) { echo 'Público'; } else { echo 'Privado'; } ?></dd>
                        </dl>
                    </div>
                </section>

                <section class="card">
                    <div class="card-body p-4">
                        <?php if ($esCreador): ?>
                            <p class="fw-semibold mb-3">Sos el creador de este curso</p>
                            <a href="subir_material.php?id=<?= $curso->getId() ?>" class="btn btn-iniciar w-100 d-flex align-items-center justify-content-center mb-2">Subir material</a>
                            <a href="inscripciones.php?id=<?= $curso->getId() ?>" class="btn btn-registrarse w-100">Ver inscripciones</a>

                        <?php elseif ($estadoInscripcion === 'confirmada'): ?>
                            <p class="fw-semibold mb-0">✓ Ya te inscribiste en este curso</p>

                        <?php elseif ($estadoInscripcion === 'pendiente'): ?>
                            <div class="solicitud-estado">
                                <p class="fw-semibold mb-1">⏳ Tu inscripción está pendiente</p>
                                <p class="small mb-0">El creador del curso la tiene que confirmar.</p>
                            </div>

                        <?php elseif (!$curso->estaActivo()): ?>
                            <!-- solo mientras el curso esta en curso -->
                            <p class="text-secondary mb-0">Las inscripciones están cerradas: solo te podés inscribir mientras el curso está en curso.</p>

                        <?php elseif (!$curso->hayCupoDisponible($inscriptos)): ?>
                            <p class="text-secondary mb-0">No quedan lugares en este curso.</p>

                        <?php elseif (!$usuario->puedeInscribirse($inscripcionesActivas)): ?>
                            <!--  limite del plan gratuito -->
                            <div class="solicitud-estado">
                                <p class="fw-semibold mb-1">Llegaste al límite del plan gratuito</p>
                                <p class="small mb-2">Podés estar inscripto en hasta <?= Usuario::LIMITE_GRATUITO ?> cursos activos a la vez.</p>
                                <a href="solicitar_pro.php" class="link-registro small">Pasate a Pro</a>
                            </div>

                        <?php else: ?>
                            <!-- puede inscribirse -->
                            <form method="post" action="../procesos/procesar_inscripcion.php">
                                <input type="hidden" name="id_curso" value="<?= $curso->getId() ?>">
                                <button type="submit" class="btn btn-iniciar w-100">Inscribirme</button>
                            </form>
                            <?php if ($curso->requiereConfirmacion()): ?>
                                <!-- si la inscripcion requiere de confirmacion del creador: -->
                                <p class="small text-secondary mt-2 mb-0">
                                    Tu inscripción va a quedar pendiente hasta que el creador la confirme.
                                    <?php if (!$curso->esGratuito()): ?>
                                        El pago se coordina directamente con el creador.
                                    <?php endif; ?>
                                </p>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </section>
            </div>

        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>