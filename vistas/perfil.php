<?php
require_once __DIR__ . '/../includes/verificar_sesion.php';
require_once __DIR__ . '/../conexiones/conexion.php';
require_once __DIR__ . '/../controles/ControlUsuario.php';

// Traemos al usuario que inició sesión (el id lo guardamos en la sesión al loguearse)
$controlUsuario = new ControlUsuario($conexion);
$usuario = $controlUsuario->buscarPorId($_SESSION['id_usuario']);

// Por si la cuenta ya no existe (por ejemplo, se borró de la base con la sesión abierta)
if ($usuario === null) {
    header('Location: cerrar_sesion.php');
    exit;
}

// Para la tarjeta del plan (RN01 y RN02)
$cursosCreados = $controlUsuario->contarCursosCreados($usuario->getId_usuario());
$inscripciones = $controlUsuario->contarInscripcionesActivas($usuario->getId_usuario());
$limite        = Usuario::LIMITE_GRATUITO;

// Teléfono y antecedentes pueden venir vacíos (NULL en la base)
$telefono     = $usuario->getTelefono() ?? '';
$antecedentes = $usuario->getAntecedentes() ?? '';

$paginaActual = 'perfil'; // para que el navbar marque PERFIL
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Cursolandia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
</head>
<body class="fondo-suave">
    <?php require __DIR__ . '/../includes/navbar.php'; ?>

    <main class="container perfil my-4">

        <!-- PARTE DE ARRIBA (foto de perfil / nombre / plan / mail / btn editar perfil) -->
        <section class="card perfil-encabezado mb-4">
            <div class="perfil-portada"></div>
            <div class="card-body d-flex flex-wrap align-items-end justify-content-between gap-3 px-4 pt-0">
                <div class="d-flex flex-wrap align-items-end gap-3">
                    <?php if ($usuario->tieneFoto()): ?>
                        <img src="../<?= htmlspecialchars($usuario->getImagen_principal()) ?>" alt="Foto de perfil" class="perfil-avatar">
                    <?php else: ?>
                        <div class="perfil-avatar"><?= htmlspecialchars($usuario->getIniciales()) ?></div>
                    <?php endif; ?>
                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <h1 class="h3 mb-0"><?= htmlspecialchars($usuario->getNombre() . ' ' . $usuario->getApellido()) ?></h1>
                            <?php if ($usuario->esPro()): ?>
                                <span class="badge">PRO</span>
                            <?php else: ?>
                                <span class="badge badge-gratuito">PLAN GRATUITO</span>
                            <?php endif; ?>
                        </div>
                        <p class="text-secondary mb-0"><?= htmlspecialchars($usuario->getEmail()) ?></p>
                    </div>
                </div>
                <a href="editar_perfil.php" class="btn btn-registrarse px-4">Editar perfil</a>
            </div>
        </section>

        <!-- PARTE DE ABAJO (una columna con los datos y otra con el tipo de plan) -->
        <div class="row g-4">

            <!-- COLUMNA IZQUIERDA: datos del usuario -->
            <div class="col-lg-6 d-flex flex-column gap-4">

                <!-- Mis datos -->
                <section class="card">
                    <div class="card-body p-4">
                        <h2 class="h5 mb-3">Mis datos</h2>
                        <dl class="row mb-0">
                            <div class="col-sm-6">
                                <dt class="dato-titulo">DNI</dt>
                                <dd class="mb-3"><?= number_format($usuario->getId_usuario(), 0, ',', '.') ?></dd>

                                <dt class="dato-titulo">Email</dt>
                                <dd class="mb-3 mb-sm-0 text-break"><?= htmlspecialchars($usuario->getEmail()) ?></dd>
                            </div>
                            <div class="col-sm-6">
                                <dt class="dato-titulo">Fecha de nacimiento</dt>
                                <dd class="mb-3"><?= date('d/m/Y', strtotime($usuario->getFecha_nacimiento())) ?></dd>

                                <dt class="dato-titulo">Teléfono</dt>
                                <?php if ($telefono === ''): ?>
                                    <dd class="mb-0 text-secondary">Sin cargar · <a href="editar_perfil.php" class="link-registro">Agregar</a></dd>
                                <?php else: ?>
                                    <dd class="mb-0"><?= htmlspecialchars($telefono) ?></dd>
                                <?php endif; ?>
                            </div>
                        </dl>
                    </div>
                </section>

                <!-- Sobre mí (antecedentes) -->
                <section class="card">
                    <div class="card-body p-4">
                        <h2 class="h5 mb-3">Sobre mí</h2>
                        <?php if ($antecedentes === ''): ?>
                            <div class="perfil-vacio">
                                <p class="fw-semibold mb-1">Todavía no contaste nada sobre vos</p>
                                <p class="text-secondary small mb-2">Tus antecedentes (estudios, experiencia) ayudan a que se inscriban en los cursos que creás.</p>
                                <a href="editar_perfil.php" class="link-registro small">Completar antecedentes</a>
                            </div>
                        <?php else: ?>
                            <p class="mb-0"><?= nl2br(htmlspecialchars($antecedentes)) ?></p>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Mis intereses (se completan cuando tengamos la tabla de etiquetas) -->
                <section class="card">
                    <div class="card-body p-4">
                        <h2 class="h5 mb-1">Mis intereses</h2>
                        <p class="text-secondary small mb-3">Los usamos para recomendarte cursos.</p>
                        <div class="perfil-vacio">
                            <p class="text-secondary small mb-2">Todavía no elegiste intereses.</p>
                            <a href="editar_perfil.php" class="link-registro small">+ Agregar intereses</a>
                        </div>
                    </div>
                </section>

            </div>

            <!-- COLUMNA DERECHA: el plan (cambia según si es Pro o no) -->
            <div class="col-lg-6 d-flex flex-column gap-4">
                <?php if ($usuario->esPro()): ?>

                    <!-- PRO: el plan con su vencimiento -->
                    <section class="card card-pro">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <h2 class="h5 mb-0">Tu plan</h2>
                                <span class="badge">PRO</span>
                            </div>
                            <p class="dato-titulo mb-1">Vigente hasta</p>
                            <p class="perfil-vencimiento mb-0"><?= date('d/m/Y', strtotime($usuario->getFecha_venc_pro())) ?></p>
                        </div>
                    </section>

                    <!-- PRO: actividad, sin límites -->
                    <section class="card">
                        <div class="card-body p-4">
                            <h2 class="h5 mb-3">Tu actividad</h2>
                            <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                                <span>Cursos activos creados</span><strong><?= $cursosCreados ?></strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Inscripciones activas</span><strong><?= $inscripciones ?></strong>
                            </div>
                        </div>
                    </section>

                <?php else: ?>

                    <!-- GRATUITO: invitación a pasarse a Pro -->
                    <section class="card card-pro">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <h2 class="h5 mb-0">Pasate a</h2>
                                <span class="badge">PRO</span>
                            </div>
                            <ul class="list-unstyled d-flex flex-column gap-2 mb-4">
                                <li>✓ Tus cursos aparecen destacados</li>
                                <li>✓ Sin límite de cursos creados ni de inscripciones</li>
                                <li>✓ Más archivos y más espacio por curso</li>
                            </ul>
                            <a href="solicitar_pro.php" class="btn btn-iniciar w-100 d-flex align-items-center justify-content-center">Solicitar Plan Pro</a>
                            <p class="text-secondary small mt-2 mb-0">Subís el comprobante de pago y un administrador lo activa.</p>
                        </div>
                    </section>

                    <!-- GRATUITO: cuánto le queda del límite (RN01 y RN02) -->
                    <section class="card">
                        <div class="card-body p-4">
                            <h2 class="h5 mb-3">Tu plan gratuito</h2>

                            <div class="d-flex justify-content-between small mb-1">
                                <span>Cursos activos creados</span><strong><?= $cursosCreados ?> de <?= $limite ?></strong>
                            </div>
                            <div class="progress mb-3" role="progressbar" aria-label="Cursos activos creados" aria-valuenow="<?= $cursosCreados ?>" aria-valuemin="0" aria-valuemax="<?= $limite ?>">
                                <div class="progress-bar" style="width: <?= min(100, $cursosCreados * 100 / $limite) ?>%"></div>
                            </div>

                            <div class="d-flex justify-content-between small mb-1">
                                <span>Inscripciones activas</span><strong><?= $inscripciones ?> de <?= $limite ?></strong>
                            </div>
                            <div class="progress" role="progressbar" aria-label="Inscripciones activas" aria-valuenow="<?= $inscripciones ?>" aria-valuemin="0" aria-valuemax="<?= $limite ?>">
                                <div class="progress-bar" style="width: <?= min(100, $inscripciones * 100 / $limite) ?>%"></div>
                            </div>
                        </div>
                    </section>

                <?php endif; ?>
            </div>

        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>
 