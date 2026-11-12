<?php
require_once __DIR__ . '/../includes/verificar_sesion.php';
require_once __DIR__ . '/../conexiones/conexion.php';
require_once __DIR__ . '/../controles/ControlUsuario.php';
require_once __DIR__ . '/../controles/ControlEtiqueta.php';

$controlUsuario = new ControlUsuario($conexion);
$usuario = $controlUsuario->buscarPorId($_SESSION['id_usuario']);

if ($usuario === null) {
    header('Location: cerrar_sesion.php');
    exit;
}

// RN01: ¿puede crear otro curso? (gratuito: hasta 3 activos; Pro: sin límite)
$cursosActivos = $controlUsuario->contarCursosCreados($usuario->getId_usuario());
$puedeCrear    = $usuario->puedeCrearCurso($cursosActivos);

// Etiquetas que ya existen, para sugerirlas
$controlEtiqueta = new ControlEtiqueta($conexion);
$todasLasEtiquetas = $controlEtiqueta->traerEtiquetas();

// Si el proceso encontró errores, vuelven los mensajes y lo que había escrito
$errores = $_SESSION['errores_curso'] ?? [];
$datos   = $_SESSION['datos_curso'] ?? [];
unset($_SESSION['errores_curso'], $_SESSION['datos_curso']);

function valor($datos, $campo) {
    return htmlspecialchars($datos[$campo] ?? '');
}

$hoy = date('Y-m-d');
$paginaActual = 'cursos';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear curso - Cursolandia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
</head>
<body class="fondo-suave">
    <?php require __DIR__ . '/../includes/navbar.php'; ?>

    <main class="container perfil my-4">

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
            <h1 class="h3 mb-0">Crear curso</h1>
            <a href="principal.php" class="link-registro">← Volver</a>
        </div>

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
            <div class="col-lg-9">
                <section class="card">
                    <div class="card-body p-4">

                        <?php if (!$puedeCrear): ?>

                            <!-- RN01: llegó al límite del plan gratuito -->
                            <div class="solicitud-estado">
                                <p class="fw-semibold mb-1">Llegaste al límite del plan gratuito</p>
                                <p class="small mb-2">Podés tener hasta <?= Usuario::LIMITE_GRATUITO ?> cursos activos creados. Esperá a que termine alguno o pasate a Pro para crear sin límite.</p>
                                <a href="solicitar_pro.php" class="link-registro small">Solicitar Plan Pro</a>
                            </div>

                        <?php else: ?>

                            <form method="post" action="../procesos/procesar_crear_curso.php" class="row g-3">
                                <div class="col-12">
                                    <label for="titulo" class="form-label fw-semibold">Título</label>
                                    <input type="text" id="titulo" name="titulo" class="form-control inicio-input"
                                           minlength="3" maxlength="100" value="<?= valor($datos, 'titulo') ?>" required>
                                </div>
                                <div class="col-12">
                                    <label for="descripcion" class="form-label fw-semibold">Descripción</label>
                                    <textarea id="descripcion" name="descripcion" class="form-control" rows="4" maxlength="2000"
                                              placeholder="¿Qué se va a aprender? ¿A quién está dirigido?" required><?= valor($datos, 'descripcion') ?></textarea>
                                </div>
                                <div class="col-12">
                                    <label for="etiquetas" class="form-label fw-semibold">Etiquetas</label>
                                    <input type="text" id="etiquetas" name="etiquetas" class="form-control inicio-input"
                                           placeholder="Ej: php, programación, principiantes" value="<?= valor($datos, 'etiquetas') ?>">
                                    <div class="form-text">
                                        Separadas por comas. Ayudan a que te encuentren y a recomendar tu curso.
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="costo" class="form-label fw-semibold">Costo ($)</label>
                                    <input type="number" id="costo" name="costo" class="form-control inicio-input"
                                           min="1" step="0.01" placeholder="Vacío = gratis" value="<?= valor($datos, 'costo') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="cupo" class="form-label fw-semibold">Cupo</label>
                                    <input type="number" id="cupo" name="cupo" class="form-control inicio-input"
                                           min="1" step="1" placeholder="Vacío = sin límite" value="<?= valor($datos, 'cupo') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="fecha_inicio" class="form-label fw-semibold">Fecha de inicio</label>
                                    <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control inicio-input"
                                           min="<?= $hoy ?>" value="<?= valor($datos, 'fecha_inicio') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="fecha_fin" class="form-label fw-semibold">Fecha de fin</label>
                                    <input type="date" id="fecha_fin" name="fecha_fin" class="form-control inicio-input"
                                           min="<?= $hoy ?>" value="<?= valor($datos, 'fecha_fin') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="tipo_acceso" class="form-label fw-semibold">Acceso</label>
                                    <select id="tipo_acceso" name="tipo_acceso" class="form-select inicio-input">
                                        <option value="publico" <?= ($datos['tipo_acceso'] ?? '') !== 'privado' ? 'selected' : '' ?>>Público: cualquiera se puede inscribir</option>
                                        <option value="privado" <?= ($datos['tipo_acceso'] ?? '') === 'privado' ? 'selected' : '' ?>>Privado: vos confirmás cada inscripción</option>
                                    </select>
                                </div>
                                <div class="col-12 d-flex gap-2 mt-4">
                                    <button type="submit" class="btn btn-iniciar px-4">Crear curso</button>
                                    <a href="principal.php" class="btn btn-registrarse px-4">Cancelar</a>
                                </div>
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