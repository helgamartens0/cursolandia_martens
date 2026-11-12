<?php
require_once __DIR__ . '/../includes/verificar_admin.php';
require_once __DIR__ . '/../conexiones/conexion.php';
require_once __DIR__ . '/../controles/ControlSolicitud.php';

$estados = ['pendiente' => 'Pendientes', 'aprobada' => 'Aprobadas', 'denegada' => 'Rechazadas'];
$estado  = $_GET['estado'] ?? 'pendiente';
if (!array_key_exists($estado, $estados)) {
    $estado = 'pendiente';
}

$controlSolicitud = new ControlSolicitud($conexion);
$solicitudes = $controlSolicitud->obtenerPorEstado($estado);

// Mensajes que deja procesar_resolucion.php (se muestran una sola vez)
$mensaje = $_SESSION['mensaje_admin'] ?? '';
$errores = $_SESSION['errores_admin'] ?? [];
unset($_SESSION['mensaje_admin'], $_SESSION['errores_admin']);

// Para el formulario de aprobar: vence en 1 año por defecto, y como mínimo mañana
$vencimientoSugerido = date('Y-m-d', strtotime('+1 year'));
$vencimientoMinimo   = date('Y-m-d', strtotime('+1 day'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de administración - Cursolandia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
</head>
<body class="fondo-suave">
    <?php require __DIR__ . '/../includes/navbar_admin.php'; ?>

    <main class="container perfil my-4">

        <h1 class="h3 mb-1">Solicitudes de plan Pro</h1>
        <p class="text-secondary mb-4">Revisá el comprobante y aprobá (con fecha de vencimiento) o rechazá cada solicitud.</p>

        <?php if ($mensaje !== ''): ?>
            <div class="alert alert-success" role="alert"><?= htmlspecialchars($mensaje) ?></div>
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

        <!-- Pestañas: cada una es un link a la misma página con ?estado=... -->
        <ul class="nav nav-pills mb-4">
            <?php foreach ($estados as $valor => $titulo): ?>
                <li class="nav-item">
                    <a class="nav-link pestania <?= $estado === $valor ? 'active' : '' ?>" href="panel_admin.php?estado=<?= $valor ?>"><?= $titulo ?></a>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php if (count($solicitudes) === 0): ?>
            <p class="text-secondary">No hay solicitudes <?= mb_strtolower($estados[$estado]) ?>.</p>
        <?php endif; ?>

        <div class="d-flex flex-column gap-3">
            <?php foreach ($solicitudes as $solicitud): ?>
                <?php $u = $solicitud->getUsuario(); ?>
                <section class="card">
                    <div class="card-body p-4">

                        <!-- ara ver quien la mando y cuando la mando -->
                        <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                            <div>
                                <h2 class="h5 mb-0"><?= htmlspecialchars($u->getNombre() . ' ' . $u->getApellido()) ?></h2>
                                <span class="text-secondary small">
                                    DNI <?= number_format($u->getId_usuario(), 0, ',', '.') ?> · <?= htmlspecialchars($u->getEmail()) ?>
                                </span>
                            </div>
                            <span class="text-secondary small">Enviada el <?= date('d/m/Y', strtotime($solicitud->getFecha_envio())) ?></span>
                        </div>

                        <!-- Su mensaje y el comprobante -->
                        <p class="mb-2"><?= nl2br(htmlspecialchars($solicitud->getMensaje())) ?></p>
                        <a href="ver_comprobante.php?id=<?= $solicitud->getId_solicitud() ?>" target="_blank" class="link-registro">Ver comprobante ↗</a>

                        <?php if ($solicitud->estaPendiente()): ?>

                            <!-- Aprobar o rechazar: cada uno es su propio formulario -->
                            <div class="row g-3 mt-2">
                                <div class="col-md-6">
                                    <form method="post" action="../procesos/procesar_resolucion.php" class="solicitud-estado h-100 d-flex flex-column gap-2">
                                        <input type="hidden" name="id_solicitud" value="<?= $solicitud->getId_solicitud() ?>">
                                        <input type="hidden" name="accion" value="aprobar">
                                        <label for="venc_<?= $solicitud->getId_solicitud() ?>" class="form-label fw-semibold mb-0">Pro vigente hasta</label>
                                        <input type="date" id="venc_<?= $solicitud->getId_solicitud() ?>" name="fecha_venc" class="form-control"
                                               min="<?= $vencimientoMinimo ?>" value="<?= $vencimientoSugerido ?>" required>
                                        <button type="submit" class="btn btn-iniciar mt-auto">Aprobar</button>
                                    </form>
                                </div>
                                <div class="col-md-6">
                                    <form method="post" action="../procesos/procesar_resolucion.php" class="solicitud-estado solicitud-rechazada h-100 d-flex flex-column gap-2">
                                        <input type="hidden" name="id_solicitud" value="<?= $solicitud->getId_solicitud() ?>">
                                        <input type="hidden" name="accion" value="rechazar">
                                        <label for="motivo_<?= $solicitud->getId_solicitud() ?>" class="form-label fw-semibold mb-0">Motivo del rechazo</label>
                                        <input type="text" id="motivo_<?= $solicitud->getId_solicitud() ?>" name="motivo" class="form-control"
                                               maxlength="255" placeholder="Ej: el comprobante no se lee" required>
                                        <button type="submit" class="btn btn-outline-danger mt-auto">Rechazar</button>
                                    </form>
                                </div>
                            </div>

                        <?php elseif ($solicitud->getEstado() === 'aprobada'): ?>
                            <p class="mt-3 mb-0 fw-semibold">OKI Aprobada · Pro hasta el <?= date('d/m/Y', strtotime($solicitud->getFecha_venc_asignada())) ?></p>
                        <?php else: ?>
                            <p class="mt-3 mb-0"><strong>NAO Rechazada.</strong> Motivo: <?= htmlspecialchars($solicitud->getMotivo_rechazo() ?? '') ?></p>
                        <?php endif; ?>

                    </div>
                </section>
            <?php endforeach; ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>