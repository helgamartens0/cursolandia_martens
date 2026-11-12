<?php
require_once __DIR__ . '/../includes/verificar_sesion.php';
require_once __DIR__ . '/../conexiones/conexion.php';
require_once __DIR__ . '/../controles/ControlUsuario.php';
require_once __DIR__ . '/../controles/ControlSolicitud.php';

$controlUsuario = new ControlUsuario($conexion);
$usuario = $controlUsuario->buscarPorId($_SESSION['id_usuario']);

if ($usuario === null) {
    header('Location: cerrar_sesion.php');
    exit;
}

// Control para q si ya es Pro no tiene nada que solicitar: vuelve al perfil
if ($usuario->esPro()) {
    header('Location: perfil.php');
    exit;
}

// si tiene alguna solicitud pendiente no le muestro ningun formularioo
$controlSolicitud = new ControlSolicitud($conexion);
$pendiente = $controlSolicitud->tienePendiente($usuario->getId_usuario());

// Errores que deja procesar_solicitud_pro.php (se muestran una sola vez)
$errores = $_SESSION['errores_solicitud'] ?? [];
unset($_SESSION['errores_solicitud']);

$paginaActual = 'perfil';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Plan Pro - Cursolandia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
</head>
<body class="fondo-suave">
    <?php require __DIR__ . '/../includes/navbar.php'; ?>

    <main class="container perfil my-4">

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
            <h1 class="h3 mb-0">Solicitar Plan Pro</h1>
            <a href="perfil.php" class="link-registro">Volver a mi perfil</a>
        </div>

        <!-- Errores del proceso -->
        <?php if (count($errores) > 0): ?>
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0 ps-3">
                    <?php foreach ($errores as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-8">
                <section class="card card-pro">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <h2 class="h5 mb-0">Pasate a</h2>
                            <span class="badge">PRO</span>
                        </div>

                        <?php if ($pendiente): ?>
                            <p class="mb-0">Ya enviaste una solicitud y está pendiente de revisión. Te avisamos en tu perfil cuando el administrador la revise.</p>

                        <?php else: ?>
                            <p class="text-secondary">Paga y subi el comprobante. Un administrador lo revisa y activa tu plan Pro.</p>

                            <!-- sin esto el archivo NO llega al servidor -->
                            <form method="post" action="../procesos/procesar_solicitud_pro.php" enctype="multipart/form-data" class="d-flex flex-column gap-3">
                                <div>
                                    <label for="mensaje" class="form-label fw-semibold">Mensaje para el administrador</label>
                                    <textarea id="mensaje" name="mensaje" class="form-control" rows="4" maxlength="500"
                                              placeholder="Ej: Hola, ya hice la transferencia del plan Pro anual." required></textarea>
                                </div>
                                <div>
                                    <label for="comprobante" class="form-label fw-semibold">Comprobante de pago</label>
                                    <input type="file" id="comprobante" name="comprobante" class="form-control"
                                           accept=".pdf,.jpg,.jpeg,.png" required>
                                    <div class="form-text">PDF, JPG o PNG. Hasta 2 MB.</div>
                                </div>
                                <button type="submit" class="btn btn-iniciar align-self-start px-4">Enviar solicitud</button>
                            </form>

                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>