<?php
require_once __DIR__ . '/../includes/verificar_sesion.php';
require_once __DIR__ . '/../conexiones/conexion.php';
require_once __DIR__ . '/../controles/ControlNotificacion.php';

$controlNotificacion = new ControlNotificacion($conexion);
$notificaciones = $controlNotificacion->obtenerDeUsuario($_SESSION['id_usuario']);

$paginaActual = '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificaciones - Cursolandia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
</head>
<body class="fondo-suave">
    <?php require __DIR__ . '/../includes/navbar.php'; ?>

    <main class="container perfil my-4">

        <h1 class="h3 mb-4">Notificaciones</h1>

        <?php if (count($notificaciones) === 0): ?>
            <p class="text-secondary">Todavía no tenés notificaciones.</p>
        <?php endif; ?>

        <?php foreach ($notificaciones as $notificacion): ?>
            <!-- al tocarla se marca como leida y nos lleva a la pregunta (o al foro si ya no existe) -->
            <a href="../procesos/abrir_notificacion.php?id=<?= $notificacion->getId_notificacion() ?>"
               class="card mb-2 text-decoration-none text-reset <?php if (!$notificacion->estaLeida()) { echo 'noti-nueva'; } ?>">
                <div class="card-body py-3 px-4 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <span class="<?php if (!$notificacion->estaLeida()) { echo 'fw-semibold'; } ?>">
                        <?= htmlspecialchars($notificacion->getMensaje()) ?>
                    </span>
                    <span class="text-secondary small">
                        <?php if (!$notificacion->estaLeida()): ?>
                            <span class="badge me-1">Nueva</span>
                        <?php endif; ?>
                        <?= date('d/m/Y H:i', strtotime($notificacion->getFecha())) ?>
                    </span>
                </div>
            </a>
        <?php endforeach; ?>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>