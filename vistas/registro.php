<?php
session_start();

//si ya tiene la sesion iniciada, no tiene sentido el registro asiq manda a la pag principal
if(isset($_SESSION['id_usuario'])){
    header('Location: principal.php');
    exit;
}

//si el procesar_registrp.php dejo errores, los borramos (solo se muestran una vez cdo se envia el formulario)
$errores = $_SESSION['errores_registro'] ?? [];
$datos = $_SESSION['datos_registro'] ?? [];
unset($_SESSION['errores_registro'], $_SESSION['datos_registro']);

// ppara no repetir htmlspecialchars($datos['...'] ?? '') en cada input
function valor($datos, $campo) {
    return htmlspecialchars($datos[$campo] ?? '');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta - Cursolandia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
</head>
<body>
    <main class="container-fluid px-0">
        <div class="row g-0 min-vh-100">

            <!-- Izquierda: presentación (igual estilo que el inicio) -->
            <section class="col-lg-5 inicio-presentacion registro-presentacion d-flex flex-column justify-content-between">

                <a href="../index.php" class="d-flex align-items-center gap-2 text-decoration-none text-reset">
                    <svg class="flex-shrink-0" width="44" height="44" viewBox="0 0 56 56" aria-hidden="true">
                        <rect width="56" height="56" rx="14" fill="#FFFFFF"/>
                        <path d="M10 18 Q19 14 27 19 V42 Q19 37 10 41 Z" fill="#2EC4B6"/>
                        <path d="M46 18 Q37 14 29 19 V42 Q37 37 46 41 Z" fill="#2EC4B6"/>
                        <path d="M33 8 H41 V26 L37 22 L33 26 Z" fill="#F4A259"/>
                    </svg>
                    <span class="inicio-marca"><span class="text-white">Curso</span>landia</span>
                </a>

                <div class="d-flex flex-column gap-4 my-5">
                    <h1 class="inicio-titulo">Sumate a <span class="resaltado">Cursolandia</span></h1>
                    <p class="inicio-texto">Creá tu cuenta gratis y empezá a aprender o a enseñar lo que sabés.</p>
                    <ul class="list-unstyled d-flex flex-column gap-2 m-0">
                        <li class="d-flex gap-2"><svg class="flex-shrink-0 mt-1" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg><span>Con el plan gratuito podés tener hasta 3 cursos activos.</span></li>
                        <li class="d-flex gap-2"><svg class="flex-shrink-0 mt-1" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg><span>Cuando quieras, pasate a <span class="badge">Pro</span> desde tu perfil, y obtene cursos ilimitados y mucho m&aacute;s</span></li>
                    </ul>
                </div>

                <span></span>

                <!-- La barra ondulada (solo en pantallas grandes) -->
                <svg class="inicio-onda d-none d-lg-block" viewBox="0 0 160 800" preserveAspectRatio="none" aria-hidden="true">
                    <path d="M160,0 L80,0 C20,120 130,220 70,340 C10,460 120,560 60,680 C35,730 55,770 70,800 L160,800 Z" fill="#FFFFFF"/>
                    <path d="M58,0 C-2,120 108,220 48,340 C-12,460 98,560 38,680 C13,730 33,770 48,800" fill="none" stroke="#F4A259" stroke-width="7" stroke-linecap="round" vector-effect="non-scaling-stroke"/>
                    <path d="M80,0 C20,120 130,220 70,340 C10,460 120,560 60,680 C35,730 55,770 70,800" fill="none" stroke="#CBF3F0" stroke-width="3" vector-effect="non-scaling-stroke"/>
                </svg>
            </section>

            <!-- Derecha: formulario de registro -->
            <section class="col-lg-7 d-flex align-items-center justify-content-center p-4 p-lg-5">
                <div class="registro-form w-100">
                    <h2 class="mb-1">Crear cuenta</h2>
                    <p class="text-secondary mb-4">Completá tus datos. Los demás (foto, intereses, teléfono) los podés cargar después en tu perfil.</p>

                    <?php if (count($errores) > 0): ?>
                        <div class="alert alert-danger" role="alert">
                            <ul class="mb-0 ps-3">
                                <?php foreach ($errores as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="../procesos/procesar_registro.php" class="row g-3" data-validar novalidate>
                        <div class="col-md-6">
                            <label for="nombre" class="form-label fw-semibold">Nombre</label>
                            <input type="text" id="nombre" name="nombre" class="form-control inicio-input" pattern="[A-Za-zÁÉÍÓÚáéíóúÜüÑñ][A-Za-zÁÉÍÓÚáéíóúÜüÑñ' \-]{1,19}" maxlength="20" data-mensaje="Solo letras (sin números), entre 2 y 20 caracteres." value="<?= valor($datos, 'nombre') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="apellido" class="form-label fw-semibold">Apellido</label>
                            <input type="text" id="apellido" name="apellido" class="form-control inicio-input" pattern="[A-Za-zÁÉÍÓÚáéíóúÜüÑñ][A-Za-zÁÉÍÓÚáéíóúÜüÑñ' \-]{1,19}" maxlength="20" data-mensaje="Solo letras (sin números), entre 2 y 20 caracteres." value="<?= valor($datos, 'apellido') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="dni" class="form-label fw-semibold">DNI</label>
                            <input type="text" id="dni" name="dni" class="form-control inicio-input" inputmode="numeric" data-solo-numeros pattern="[0-9]{7,8}" maxlength="8" placeholder="Sin puntos" data-mensaje="El DNI tiene que tener 7 u 8 números, sin puntos." value="<?= valor($datos, 'dni') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="fecha_nacimiento" class="form-label fw-semibold">Fecha de nacimiento</label>
                            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="form-control inicio-input" max="<?= date('Y-m-d') ?>" data-mensaje="La fecha no puede ser posterior a hoy." value="<?= valor($datos, 'fecha_nacimiento') ?>" required>
                        </div>
                        <div class="col-12">
                            <label for="email" class="form-label fw-semibold">Email</label>
                            <input type="email" id="email" name="email" class="form-control inicio-input" maxlength="100" placeholder="tu@email.com" value="<?= valor($datos, 'email') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="clave" class="form-label fw-semibold">Contraseña</label>
                            <input type="password" id="clave" name="clave" class="form-control inicio-input" minlength="8" required>
                            <div class="form-text">Al menos 8 caracteres.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="clave2" class="form-label fw-semibold">Repetir contraseña</label>
                            <input type="password" id="clave2" name="clave2" class="form-control inicio-input" minlength="8" data-igual-a="clave" required>
                        </div>
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-iniciar w-100">Crear cuenta</button>
                        </div>
                    </form>

                    <p class="text-center text-secondary mt-4 mb-0">¿Ya tenés cuenta? <a href="../index.php" class="link-registro">Iniciá sesión</a></p>
                </div>
            </section>

        </div>
    </main>
    <script src="../js/validacion.js"></script>
</body>
</html>