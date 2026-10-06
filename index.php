<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cursolandia - Aprendé y enseñá lo que te apasiona</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
</head>
<body>
    <main class="container-fluid px-0">
        <div class="row g-0 min-vh-100">

            <!-- Izquierda: presentacion -->
            <section class="col-lg-7 inicio-presentacion d-flex flex-column justify-content-between">

                <div class="d-flex align-items-center gap-2">
                    <svg class="flex-shrink-0" width="44" height="44" viewBox="0 0 56 56" aria-hidden="true">
                        <rect width="56" height="56" rx="14" fill="#FFFFFF"/>
                        <path d="M10 18 Q19 14 27 19 V42 Q19 37 10 41 Z" fill="#2EC4B6"/>
                        <path d="M46 18 Q37 14 29 19 V42 Q37 37 46 41 Z" fill="#2EC4B6"/>
                        <path d="M33 8 H41 V26 L37 22 L33 26 Z" fill="#F4A259"/>
                    </svg>
                    <span class="inicio-marca"><span class="text-white">Curso</span>landia</span>
                </div>

                <div class="d-flex flex-column gap-4 my-5">
                    <span class="inicio-etiqueta">Cursos creados por la comunidad</span>
                    <h1 class="inicio-titulo">Aprend&eacute; y enseñ&aacute; lo que te <span class="resaltado">apasiona</span></h1>
                    <p class="inicio-texto">En Cursolandia cualquiera puede crear su propio curso o anotarse en el de otros. Gratis o pagos, p&uacute;blicos o privados: vos eleg&iacute;s.</p>

                    <ul class="list-unstyled d-flex flex-column gap-3 m-0">
                        <li class="d-flex align-items-center gap-3">
                            <span class="inicio-icono">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                            </span>
                            Cre&aacute; tus cursos y compart&iacute; material
                        </li>
                        <li class="d-flex align-items-center gap-3">
                            <span class="inicio-icono">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                            </span>
                            Inscribite y particip&aacute; en los foros
                        </li>
                        <li class="d-flex align-items-center gap-3">
                            <span class="inicio-icono">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" aria-hidden="true"><path d="M12 2l3 7 7 .6-5.3 4.6 1.6 7.1L12 17.6 5.7 21.3l1.6-7.1L2 9.6 9 9z"/></svg>
                            </span>
                            Recib&iacute; cursos recomendados seg&uacute;n tus intereses
                        </li>
                    </ul>
                </div>

                <p class="d-flex align-items-center gap-2 m-0 small">
                    <span class="badge">PRO</span>
                    Con el plan Pro, tus cursos aparecen destacados y sin l&iacute;mites.
                </p>

                <!--Barra ondulada (solo en pantallas grandes) -->
                <svg class="inicio-onda d-none d-lg-block" viewBox="0 0 160 800" preserveAspectRatio="none" aria-hidden="true">
                    <path d="M160,0 L80,0 C20,120 130,220 70,340 C10,460 120,560 60,680 C35,730 55,770 70,800 L160,800 Z" fill="#FFFFFF"/>
                    <path d="M58,0 C-2,120 108,220 48,340 C-12,460 98,560 38,680 C13,730 33,770 48,800" fill="none" stroke="#F4A259" stroke-width="7" stroke-linecap="round" vector-effect="non-scaling-stroke"/>
                    <path d="M80,0 C20,120 130,220 70,340 C10,460 120,560 60,680 C35,730 55,770 70,800" fill="none" stroke="#CBF3F0" stroke-width="3" vector-effect="non-scaling-stroke"/>
                </svg>
            </section>

            <!-- Derecha: iniciar sesion -->
            <section class="col-lg-5 d-flex align-items-center justify-content-center p-4 p-lg-5">
                <div class="inicio-login w-100">
                    <h2 class="mb-1">¡Hola de nuevo!</h2>
                    <p class="text-secondary mb-4">Inici&aacute; sesi&oacute;n para seguir aprendiendo.</p>

                    <form method="post" action="index.php" class="d-flex flex-column gap-3">
                        <div>
                            <label for="email" class="form-label fw-semibold">Email</label>
                            <input type="email" id="email" name="email" class="form-control inicio-input" placeholder="tu@email.com" required>
                        </div>
                        <div>
                            <label for="clave" class="form-label fw-semibold">Contraseña</label>
                            <input type="password" id="clave" name="clave" class="form-control inicio-input" placeholder="••••••••" required>
                        </div>
                        <button type="submit" class="btn btn-iniciar mt-2">Iniciar sesi&oacute;n</button>
                    </form>

                    <div class="d-flex align-items-center gap-3 my-4 text-secondary small">
                        <hr class="flex-grow-1 m-0">
                        ¿Todav&iacute;a no tenés cuenta?
                        <hr class="flex-grow-1 m-0">
                    </div>

                    <a href="vistas/registro.php" class="btn btn-registrarse w-100">Registrate gratis</a>
                </div>
            </section>

        </div>
    </main>
</body>
</html>