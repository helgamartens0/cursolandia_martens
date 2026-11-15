# Cursolandia

Aplicación web de cursos propuestos por sus usuarios: se pueden crear y tomar cursos, subir material, participar en foros y recibir notificaciones.
Trabajo Práctico Integrador de Programación III — Tecnicatura Universitaria en Web, UNSL.

Alumna: Helga Elena Martens

Sitio online: https://cursolandiamartens.ifree.page


## Estructura del proyecto

```
cursolandia_martens/
├── index.php            pantalla de inicio y login
├── cursolandia.sql      script de creación y carga de la base (con datos de prueba)
├── clases/              entidades del modelo de diseño (Usuario, Curso, Pregunta, ...)
├── controles/           acceso a la base de cada entidad (ControlCurso, ControlPregunta, ...)
├── traits/AccesoBD.php  conexión y consultas preparadas que comparten todos los controles
├── procesos/            reciben los formularios (POST), validan y redirigen
├── vistas/              pantallas de la aplicación
├── includes/            navbar, verificación de sesión, tarjeta de curso, avatar
├── conexiones/          conexion.php + config.php (este último NO está en el repositorio)
├── css/  js/
├── img/ejemplos/        fotos de perfil de los usuarios de prueba
├── img/perfiles/        fotos que suben los usuarios (no se sube al repositorio)
└── uploads/             comprobantes y material (protegido con .htaccess)
```

## Funcionalidades (casos de uso)

| CU | Funcionalidad |
|---|---|
| CU-01 / CU-02 | Registrarse e iniciar sesión (usuarios y administrador con el mismo login) |
| CU-03 / CU-04 | Actualizar perfil (datos, intereses y foto) y cambiar contraseña |
| CU-05 / CU-06 | Solicitar el Plan Pro con comprobante; el administrador lo aprueba (con vencimiento) o lo rechaza (con motivo) |
| CU-07 / CU-08 | Ver y buscar cursos (por título, descripción o etiqueta) y ver recomendados según los intereses; los cursos Pro aparecen primero |
| CU-09 / CU-10 | Crear cursos y subir material |
| CU-11 / CU-12 | Inscribirse; el creador confirma o rechaza las inscripciones pendientes |
| CU-13 a CU-18 | Foro de cada curso: preguntar, responder, editar la última respuesta, aceptar una respuesta (cierra la pregunta), destacar y eliminar preguntas |
| CU-19 | Notificaciones del foro (campanita con las no leídas) |

### Reglas de negocio implementadas

- **RN01 / RN02:** el plan gratuito permite hasta 3 cursos activos creados y 3 inscripciones activas.
- **RN03:** el plan gratuito permite hasta 5 archivos por curso, de 5 MB cada uno y 10 MB en total.
- **RN04 / RN05:** no se puede inscribir en un curso propio, y solo se puede inscribir y participar del foro con el curso activo.
- **RN06:** en los cursos pagos o privados la inscripción queda pendiente hasta que el creador la confirma.
- **RN07:** no se puede responder dos veces seguidas la misma pregunta; hay que editar la última respuesta o esperar a otra persona. La pregunta cuenta como la primera participación, así que quien pregunta no puede responder primero.
- **RN08:** los cursos de usuarios Pro se muestran destacados y primero en los listados y recomendados.
- **RN09 / RN10:** solo el creador sube material, confirma inscripciones y destaca o elimina preguntas. Al foro entran el creador y los inscriptos confirmados, y solo los inscriptos inician preguntas.
- **RN11:** notificaciones al crear una pregunta (a todos los del curso), al responder (a los participantes), al cerrarla (a los participantes, con un mensaje especial para el autor de la respuesta aceptada) y al eliminarla (al autor).

---

## Instalación en un entorno local (XAMPP)

1. Clonar o copiar el proyecto en `C:\xampp\htdocs\cursolandia_martens`.
2. Abrir el panel de XAMPP e iniciar **Apache** y **MySQL**.
3. Entrar a **phpMyAdmin** (`http://localhost/phpmyadmin`) → pestaña **Importar** → elegir `cursolandia.sql` → **Importar**.
   El script crea la base `cursolandia_martens`, las tablas y los datos de prueba (si ya existían, las borra y las vuelve a crear).
4. Crear el usuario de la base que usa la aplicación (pestaña **SQL**). Se le dan solo los permisos que necesita:
   ```sql
   CREATE USER IF NOT EXISTS 'user_cursolandia'@'localhost' IDENTIFIED BY 'una_contraseña';
   GRANT SELECT, INSERT, UPDATE, DELETE ON cursolandia_martens.* TO 'user_cursolandia'@'localhost';
   FLUSH PRIVILEGES;
   ```
5. Crear el archivo `conexiones/config.php` (no está en el repositorio porque tiene la contraseña):
   ```php
   <?php
       $db_host = 'localhost';
       $db_usuario = 'user_cursolandia';
       $db_password = 'una_contraseña';
       $db_nombre = 'cursolandia_martens';
   ```
6. Abrir `http://localhost/cursolandia_martens/` e iniciar sesión con alguno de los usuarios de prueba.

> Los datos de prueba tienen cursos activos entre noviembre de 2026 y marzo de 2027. Si se prueba más adelante, hay que correr las fechas de los cursos para que estén activos.

## Despliegue en el servidor (InfinityFree)

1. Crear la cuenta y el sitio con un subdominio gratuito.
2. En **Bases de datos MySQL**, crear la base. El hosting le agrega un prefijo (`if0_XXXXXXXX_cursolandia`) y da el host, el usuario y la contraseña.
3. En el **phpMyAdmin** del hosting, elegir esa base e importar `cursolandia.sql` **sin las líneas `CREATE DATABASE` y `USE`**, porque en el hosting la base se crea desde el panel y tiene otro nombre.
4. Subir los archivos a `htdocs/` con el administrador de archivos (sin `.git`, sin `cursolandia.sql` y sin `config.php`).
5. Crear `htdocs/conexiones/config.php` con los datos del hosting. El host es del tipo `sqlXXX.infinityfree.com`, no `localhost`.

---

## Usuarios de prueba

| Rol | Nombre | Email | Contraseña |
|---|---|---|---|
| Administrador | Administrador | admin@cursolandia.com | Admin12345 |
| Usuario Pro | Ana Pérez | ana@cursolandia.com | Pro12345 |
| Usuario Pro | Carla Rodríguez | carla@cursolandia.com | Pro12345 |
| Usuario Pro | Diego Fernández | diego@cursolandia.com | Pro12345 |
| Usuario Pro | Lucía Benítez | lucia@cursolandia.com | Pro12345 |
| Usuario gratuito | Bruno Gómez | bruno@cursolandia.com | Gratis12345 |
| Usuario gratuito | Martín Sosa | martin@cursolandia.com | Gratis12345 |
| Usuario gratuito | Sofía Álvarez | sofia@cursolandia.com | Gratis12345 |
| Usuario gratuito | Tomás Herrera | tomas@cursolandia.com | Gratis12345 |

Para probar cada parte:

- **Bruno:** ya tiene 3 cursos activos creados (límite del plan gratuito, RN01) y notificaciones sin leer.
- **Martín, Sofía o Tomás:** ya están en el límite de 3 inscripciones activas (RN02).
- **Ana:** tiene una inscripción pendiente de Tomás para confirmar en "Introducción al Desarrollo Web con HTML y CSS" (CU-12).
- **Administrador:** tiene 3 solicitudes de Plan Pro pendientes (CU-06). Los comprobantes son PDF de prueba de bancos que no existen.
- **Foro:** el curso "Fotografía digital para principiantes" tiene una pregunta destacada y cerrada con respuesta aceptada.

---

## Base de datos

Tablas: `usuario`, `administrador`, `solicitud_plan_pro`, `curso`, `inscripcion`, `etiqueta`, `usuario_interes`, `curso_etiqueta`, `archivo`, `pregunta`, `respuesta` y `notificacion`.

- Está normalizada en **3FN**: las relaciones N:M (intereses y etiquetas de los cursos) van en tablas intermedias, y cada dato se guarda una sola vez.
- **Claves primarias** en todas las tablas. El id del usuario es su DNI.
- **Integridad referencial** con claves foráneas y `ON DELETE CASCADE` / `SET NULL` / `RESTRICT` según el caso. Por ejemplo, no se puede borrar un usuario que tiene cursos creados, y si se borra una pregunta, sus notificaciones quedan sin el enlace a la pregunta.
- **Restricciones:** `NOT NULL`, `DEFAULT`, `UNIQUE` (email, etiqueta, una inscripción por usuario y curso), `ENUM` para los estados y `CHECK`. Por ejemplo: DNI válido, costo y cupo mayores a 0, fecha de fin posterior a la de inicio, una inscripción confirmada tiene fecha de confirmación, y una solicitud aprobada tiene vencimiento.
- **Índices:** además de los de las claves, un índice `(id_usuario, leida)` en `notificacion` para contar rápido las no leídas de la campanita.
- La aplicación se conecta con un usuario que solo tiene permisos `SELECT, INSERT, UPDATE, DELETE` sobre su base.

## Seguridad

- Contraseñas encriptadas con `password_hash()` y verificadas con `password_verify()`.
- Todas las consultas con **sentencias preparadas** (sin concatenar datos del usuario en el SQL).
- Todo lo que se muestra en pantalla pasa por `htmlspecialchars()`.
- Cada proceso vuelve a verificar los permisos (por ejemplo, que quien confirma inscripciones sea el creador del curso), aunque la vista ya oculte el botón.
- Archivos subidos: se controla el tamaño, la extensión y el **tipo real** del archivo (`finfo`), y se guardan con un nombre nuevo.
- `uploads/.htaccess` (`Require all denied`) impide abrir los comprobantes y el material desde el navegador. Solo se descargan por `ver_comprobante.php` (administrador) y `descargar_material.php` (creador o inscriptos confirmados).
- `conexiones/config.php` está en el `.gitignore`: la contraseña de la base no está en el repositorio.
- La aplicación usa solo la cookie de sesión de PHP, que es técnica y necesaria para el login. No usa cookies de seguimiento ni de terceros, por eso no hace falta pedir consentimiento.

---

## Problemas encontrados y soluciones

1. **La exportación de phpMyAdmin no incluía los `CHECK`.** Las tablas con restricciones `CHECK` se exportaban incompletas. Solución: el script `cursolandia.sql` se mantiene escrito a mano, verificando cada tabla con `SHOW CREATE TABLE`.


2. **Los comprobantes de pago se podían abrir escribiendo su dirección en el navegador.** Solución: `uploads/.htaccess` con `Require all denied`, y los archivos se entregan por PHP después de verificar quién los pide.

3. **Las fotos de perfil no se veían.** Estaban dentro de `uploads/`, que quedó bloqueada. Solución: las fotos, que son públicas, se guardan en `img/perfiles/`.

4. **Los archivos de ejemplo (fotos, comprobantes y material de los datos de prueba) no se subían al repositorio**, porque esas carpetas están en el `.gitignore`. Solución: fotos de ejemplo en `img/ejemplos/`, y excepciones en el `.gitignore` (`uploads/comprobantes/*` y `!uploads/comprobantes/ejemplo_comprobante_*.pdf`). Además, al cambiar la foto solo se borra la anterior si está en `img/perfiles/`, para no borrar las de ejemplo.

5. **Un archivo se llamaba `Controlnscripcion.php` en vez de `ControlInscripcion.php`.** En Windows funcionaba igual, pero en el servidor (Linux) distingue mayúsculas y no lo encontraba. Solución: renombrarlo y revisar que todos los `require` coincidan exactamente con los nombres.

6. **Después de crear un curso no aparecían las etiquetas ni la barra de navegación.** El proceso recorría con `foreach` un texto como si fuera un arreglo, y el *warning* que salía antes del `header()` impedía la redirección. Solución: leer las etiquetas como texto y separarlas con `explode()`.

7. **Elegir el hosting.** Se eligió InfinityFree (PHP y MySQL/MariaDB con phpMyAdmin y soporte de `.htaccess`). Ahí aparecieron dos problemas:
    - El hosting no permite `CREATE DATABASE` y la base tiene otro nombre. Solución: importar el script sin las líneas `CREATE DATABASE` y `USE`.
    - "Connection timed out" al conectar. El host de la base estaba mal. Solución: usar el host que da el panel (`sqlXXX.infinityfree.com`).
