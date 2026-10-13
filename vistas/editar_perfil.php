<?php 
require_once __DIR__ . '/../includes/verificar_sesion.php';
require_once __DIR__ . '/../conexiones/conexion.php';
require_once __DIR__ . '/../controles/ControlUsuario.php';
require_once __DIR__ . '/../controles/ControlEtiqueta.php';

// Traemos al usuario que inició sesión (el id lo guardamos en la sesión al loguearse)
$controlUsuario = new ControlUsuario($conexion);
$usuario = $controlUsuario->buscarPorId($_SESSION['id_usuario']);

// Por si la cuenta ya no existe (por ejemplo, se borró de la base con la sesión abierta)
if ($usuario === null) {
    header('Location: cerrar_sesion.php');
    exit;
}

$controlEtiqueta = new ControlEtiqueta($conexion);
foreach($controlEtiqueta->obtenerInteresesDeUsuario($usuario->getId_usuario()) as $etiqueta){
    $usuario->agregarInteres($etiqueta); 
}
// Mensajes que dejan los procesos (se muestran una sola vez)
$mensaje = $_SESSION['mensaje_perfil'] ?? '';
$errores = $_SESSION['errores_perfil'] ?? [];
unset($_SESSION['mensaje_perfil'], $_SESSION['errores_perfil']);

$paginaActual = 'perfil'; // para que el navbar marque PERFIL
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil - Cursolandia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
</head>
<body class="fondo-suave">
    <?php require __DIR__ . '/../includes/navbar.php'; ?>

    <main class="container perfil my-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
            <h1 class="h3 mb-0">Editar perfil</h1>
            <a href="perfil.php" class="link-registro">← Volver a mi perfil</a>
        </div>

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
        
        <div class="row g-4">
            <div class="col-lg-7 d-flex flex-column gap-4">
                <!-- tarjeta Datos del perfil + tarjeta Mis intereses -->
                 <section class="card">
                    <div class="card-body p-4">
                        <h2 class="h5 mb-3">Datos del perfil</h2>
                        <form method="post" action="../procesos/procesar_editar_perfil.php" class="row g-3" data-validar novalidate>
                            
                                <!-- Bloqueados: se muestran pero no se pueden cambiar (disabled = tampoco se envían) -->
                        <div class="col-md-6">
                            <label for="nombre" class="form-label fw-semibold">Nombre</label>
                            <input type="text" id="nombre" class="form-control inicio-input" value="<?= htmlspecialchars($usuario->getNombre()) ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label for="apellido" class="form-label fw-semibold">Apellido</label>
                            <input type="text" id="apellido" class="form-control inicio-input" value="<?= htmlspecialchars($usuario->getApellido()) ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label for="dni" class="form-label fw-semibold">DNI</label>
                            <input type="text" id="dni" class="form-control inicio-input" value="<?= $usuario->getId_usuario() ?>" disabled>
                        </div>
                        <div class="col-12 mt-1">
                            <p class="form-text mb-0">Nombre, apellido y DNI no se pueden modificar.</p>
                        </div>

                        <!-- Editables -->
                        <div class="col-md-6">
                            <label for="fecha_nacimiento" class="form-label fw-semibold">Fecha de nacimiento</label>
                            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="form-control inicio-input"
                                min="1920-01-01" max="2010-12-31" data-mensaje="La fecha tiene que estar entre 1920 y 2010."
                                value="<?= htmlspecialchars($usuario->getFecha_nacimiento()) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label fw-semibold">Email</label>
                            <input type="email" id="email" name="email" class="form-control inicio-input" maxlength="100"
                                value="<?= htmlspecialchars($usuario->getEmail()) ?>" required>
                        </div>
                            <div class="col-md-6">
                                <label for="telefono" class="form-label fw-semibold">Teléfono</label>
                                <input type="tel" id="telefono" name="telefono" class="form-control inicio-input"
                                    maxlength="20" pattern="[0-9+\(\)\- ]{6,20}"
                                    data-mensaje="Solo números, espacios y los signos + - ( ). Entre 6 y 20 caracteres."
                                    placeholder="Ej: 266 4123456" value="<?= htmlspecialchars($usuario->getTelefono() ?? '') ?>">
                                <div class="form-text">Opcional.</div>
                            </div>
                            <div class="col-md-12">
                                <label for="antecedentes" class="form-label fw-semibold">Sobre mí</label>
                                <textarea id="antecedentes" name="antecedentes" class="form-control" rows="5" maxlength="1000"
                                        placeholder="Contá tus estudios, tu experiencia o qué te gusta enseñar."><?= htmlspecialchars($usuario->getAntecedentes() ?? '') ?></textarea>
                                <div class="form-text">Opcional. Hasta 1000 caracteres.</div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-iniciar px-4">Guardar cambios</button>
                            </div>
                        </form>
                    </div>
                </section>
                
                <section class="card">
                    <div class="card-body p-4">
                        <h2 class="h5 mb-1">Mis intereses</h2>
                        <p class="text-secondary small mb-3">Los usamos para recomendarte cursos.</p>

                        <?php if (count($usuario->getIntereses()) === 0): ?>
                            <p class="text-secondary small">Todavía no elegiste intereses.</p>
                        <?php else: ?>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($usuario->getIntereses() as $interes): ?>
                                    <form method="post" action="../procesos/procesar_intereses.php" class="chip d-inline-flex align-items-center gap-1">
                                        <input type="hidden" name="accion" value="quitar">
                                        <input type="hidden" name="id_etiqueta" value="<?= $interes->getId_etiqueta() ?>">
                                        <?= htmlspecialchars($interes->getNombre()) ?>
                                        <button type="submit" class="chip-quitar" aria-label="Quitar <?= htmlspecialchars($interes->getNombre()) ?>">×</button>
                                    </form>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="../procesos/procesar_intereses.php" class="d-flex gap-2 mt-3">
                            <input type="hidden" name="accion" value="agregar">
                            <label for="nuevo_interes" class="visually-hidden">Nuevo interés</label>
                            <input type="text" id="nuevo_interes" name="nombre" class="form-control inicio-input"
                                maxlength="30" placeholder="Escribí un interés (ej: fotografía)" required>
                            <button type="submit" class="btn btn-registrarse px-4">Agregar</button>
                        </form>
                    </div>
                </section>
                
                
            </div>
            <div class="col-lg-5 d-flex flex-column gap-4">
                <section class="card">
                    <div class="card-body p-4">
                        <h2 class="h5 mb-3">Cambiar contraseña</h2>
                        <p class="text-secondary small mb-0">Próximamente.</p>
                    </div>
                </section>
            </div>
        </div>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="../js/validacion.js"></script>
</body>
</html>
 