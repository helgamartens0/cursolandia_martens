-- =====================================================================
-- Cursolandia - Script de creación y carga de la base de datos
-- Programación III / Ingeniería de Software - TUW UNSL
-- Autora: Helga Elena Martens
--
-- Uso: importar este archivo desde phpMyAdmin (pestaña Importar)
--      o ejecutarlo completo en la pestaña SQL.
-- ATENCIÓN: borra y vuelve a crear las tablas (se pierden los datos).
-- =====================================================================

CREATE DATABASE IF NOT EXISTS cursolandia_martens
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE cursolandia_martens;

-- Asegura que tildes y ñ se lean bien sin importar el programa que importe el archivo
SET NAMES utf8mb4;

-- Se borran primero las tablas que dependen de otras
DROP TABLE IF EXISTS notificacion;
DROP TABLE IF EXISTS respuesta;
DROP TABLE IF EXISTS pregunta;
DROP TABLE IF EXISTS archivo;
DROP TABLE IF EXISTS solicitud_plan_pro;
DROP TABLE IF EXISTS usuario_interes;
DROP TABLE IF EXISTS curso_etiqueta;
DROP TABLE IF EXISTS etiqueta;
DROP TABLE IF EXISTS inscripcion;
DROP TABLE IF EXISTS curso;
DROP TABLE IF EXISTS administrador;
DROP TABLE IF EXISTS usuario;

-- ---------------------------------------------------------------------
-- Tabla: usuario
-- El identificador del usuario es su DNI (se ingresa en el registro).
-- La contraseña se guarda encriptada con password_hash() de PHP.
-- ---------------------------------------------------------------------
CREATE TABLE usuario (
    id_usuario        INT UNSIGNED NOT NULL COMMENT 'DNI del usuario',
    nombre            VARCHAR(50)  NOT NULL,
    apellido          VARCHAR(50)  NOT NULL,
    email             VARCHAR(100) NOT NULL UNIQUE,
    contrasenia       VARCHAR(255) NOT NULL,
    fecha_nacimiento  DATE NOT NULL,
    telefono          VARCHAR(20)  NULL,
    antecedentes      TEXT NULL,
    imagen_principal  VARCHAR(255) NOT NULL DEFAULT 'img/avatar-default.png',
    tipo_plan         ENUM('gratuito','pro') NOT NULL DEFAULT 'gratuito',
    fecha_venc_pro    DATE NULL,
    PRIMARY KEY (id_usuario),
    CONSTRAINT chk_usuario_dni CHECK (id_usuario BETWEEN 1000000 AND 99999999),
    CONSTRAINT chk_usuario_pro CHECK (tipo_plan = 'gratuito' OR fecha_venc_pro IS NOT NULL)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabla: curso
-- costo NULL = curso gratuito / cupo NULL = sin límite de inscriptos
-- id_creador = DNI del usuario que creó el curso (asociación "Crea")
-- ---------------------------------------------------------------------
CREATE TABLE curso (
    id_curso      INT UNSIGNED NOT NULL AUTO_INCREMENT,
    titulo        VARCHAR(100) NOT NULL,
    descripcion   TEXT NOT NULL,
    costo         DECIMAL(10,2) NULL,
    cupo          INT UNSIGNED NULL,
    fecha_inicio  DATE NOT NULL,
    fecha_fin     DATE NOT NULL,
    tipo_acceso   ENUM('publico','privado') NOT NULL DEFAULT 'publico',
    id_creador    INT UNSIGNED NOT NULL,
    PRIMARY KEY (id_curso),
    CONSTRAINT chk_curso_costo  CHECK (costo > 0),
    CONSTRAINT chk_curso_cupo   CHECK (cupo > 0),
    CONSTRAINT chk_curso_fechas CHECK (fecha_fin >= fecha_inicio),
    CONSTRAINT fk_curso_creador FOREIGN KEY (id_creador)
        REFERENCES usuario (id_usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabla: inscripcion
-- Un usuario (alumno) se inscribe en un curso. Asociaciones del modelo:
--   Usuario "realiza" 1 -- 0..* Inscripcion  /  Inscripcion 0..* -- 1 Curso
-- estado: pendiente (curso pago o privado, RN06) o confirmada
-- fecha_confirmacion solo tiene valor cuando está confirmada
-- RN04 (no inscribirse en un curso propio) y RN02 (máximo 3 activas en plan
-- gratuito) involucran otras tablas, así que se validan desde PHP.
-- ---------------------------------------------------------------------
CREATE TABLE inscripcion (
    id_inscripcion      INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_usuario          INT UNSIGNED NOT NULL,
    id_curso            INT UNSIGNED NOT NULL,
    fecha_solicitud     DATE NOT NULL DEFAULT (CURRENT_DATE),
    estado              ENUM('pendiente','confirmada') NOT NULL DEFAULT 'pendiente',
    fecha_confirmacion  DATE NULL,
    PRIMARY KEY (id_inscripcion),
    CONSTRAINT uq_inscripcion_usuario_curso UNIQUE (id_usuario, id_curso),
    CONSTRAINT chk_inscripcion_estado CHECK (
        (estado = 'pendiente'  AND fecha_confirmacion IS NULL) OR
        (estado = 'confirmada' AND fecha_confirmacion IS NOT NULL)
    ),
    CONSTRAINT chk_inscripcion_fechas CHECK (fecha_confirmacion >= fecha_solicitud),
    CONSTRAINT fk_inscripcion_usuario FOREIGN KEY (id_usuario)
        REFERENCES usuario (id_usuario)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_inscripcion_curso FOREIGN KEY (id_curso)
        REFERENCES curso (id_curso)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabla: etiqueta
-- Palabras clave que se usan como intereses de los usuarios y para
-- etiquetar cursos. Cada nombre existe una sola vez (se guarda en minúsculas).
-- ---------------------------------------------------------------------
CREATE TABLE etiqueta (
    id_etiqueta  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre       VARCHAR(30)  NOT NULL,
    PRIMARY KEY (id_etiqueta),
    CONSTRAINT uq_etiqueta_nombre UNIQUE (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabla: usuario_interes
-- Asociación N:M "tiene intereses" entre Usuario y Etiqueta
-- ---------------------------------------------------------------------
CREATE TABLE usuario_interes (
    id_usuario   INT UNSIGNED NOT NULL,
    id_etiqueta  INT UNSIGNED NOT NULL,
    PRIMARY KEY (id_usuario, id_etiqueta),
    CONSTRAINT fk_interes_usuario FOREIGN KEY (id_usuario)
        REFERENCES usuario (id_usuario)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_interes_etiqueta FOREIGN KEY (id_etiqueta)
        REFERENCES etiqueta (id_etiqueta)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabla: curso_etiqueta
-- Asociación N:M "etiquetado con" entre Curso y Etiqueta
-- ---------------------------------------------------------------------
CREATE TABLE curso_etiqueta (
    id_curso     INT UNSIGNED NOT NULL,
    id_etiqueta  INT UNSIGNED NOT NULL,
    PRIMARY KEY (id_curso, id_etiqueta),
    CONSTRAINT fk_cursoetiq_curso FOREIGN KEY (id_curso)
        REFERENCES curso (id_curso)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_cursoetiq_etiqueta FOREIGN KEY (id_etiqueta)
        REFERENCES etiqueta (id_etiqueta)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
-- Tabla: administrador
-- El administrador solo inicia sesion y activa el Plan Pro (CU-02, CU-06).
-- Va en una tabla aparte porque no comparte datos ni acciones con el usuario.
-- ---------------------------------------------------------------------
CREATE TABLE administrador (
    id_administrador  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre            VARCHAR(50)  NOT NULL,
    email             VARCHAR(100) NOT NULL,
    contrasenia       VARCHAR(255) NOT NULL,
    PRIMARY KEY (id_administrador),
    CONSTRAINT uq_administrador_email UNIQUE (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabla: solicitud_plan_pro
-- Mensaje + comprobante que manda el usuario para pasarse a Pro (CU-05).
-- El administrador la aprueba (con fecha de vencimiento) o la deniega
-- (con motivo) en el CU-06.
-- ---------------------------------------------------------------------
CREATE TABLE solicitud_plan_pro (
    id_solicitud         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_usuario           INT UNSIGNED NOT NULL,
    fecha_envio          DATE NOT NULL DEFAULT (CURRENT_DATE),
    mensaje              TEXT NOT NULL,
    comprobante          VARCHAR(255) NOT NULL COMMENT 'ruta del archivo subido',
    estado               ENUM('pendiente','aprobada','denegada') NOT NULL DEFAULT 'pendiente',
    fecha_venc_asignada  DATE NULL,
    motivo_rechazo       VARCHAR(255) NULL,
    PRIMARY KEY (id_solicitud),
    CONSTRAINT chk_solicitud_aprobada CHECK (
        (estado = 'aprobada' AND fecha_venc_asignada IS NOT NULL) OR
        (estado <> 'aprobada' AND fecha_venc_asignada IS NULL)
    ),
    CONSTRAINT chk_solicitud_venc CHECK (fecha_venc_asignada > fecha_envio),
    CONSTRAINT fk_solicitud_usuario FOREIGN KEY (id_usuario)
        REFERENCES usuario (id_usuario)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabla: archivo
-- Material que sube el creador a su curso (CU-10, RN03).
-- nombre = nombre original del archivo / url = donde quedo guardado.
-- ---------------------------------------------------------------------
CREATE TABLE archivo (
    id_archivo   INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_curso     INT UNSIGNED NOT NULL,
    nombre       VARCHAR(255) NOT NULL,
    tamanio_mb   DECIMAL(6,2) NOT NULL,
    fecha_carga  DATE NOT NULL DEFAULT (CURRENT_DATE),
    url          VARCHAR(255) NOT NULL,
    PRIMARY KEY (id_archivo),
    CONSTRAINT fk_archivo_curso FOREIGN KEY (id_curso)
        REFERENCES curso (id_curso)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabla: pregunta
-- Preguntas del foro de cada curso (CU-13). Las destacadas (las marca el
-- creador del curso, CU-17) se muestran primero. Se cierra cuando se
-- acepta una respuesta (CU-16).
-- ---------------------------------------------------------------------
CREATE TABLE pregunta (
    id_pregunta     INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_curso        INT UNSIGNED NOT NULL,
    id_autor        INT UNSIGNED NOT NULL,
    texto           VARCHAR(1000) NOT NULL,
    fecha_creacion  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    destacada       BOOLEAN NOT NULL DEFAULT FALSE,
    cerrada         BOOLEAN NOT NULL DEFAULT FALSE,
    PRIMARY KEY (id_pregunta),
    CONSTRAINT fk_pregunta_curso FOREIGN KEY (id_curso)
        REFERENCES curso (id_curso)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_pregunta_autor FOREIGN KEY (id_autor)
        REFERENCES usuario (id_usuario)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabla: respuesta
-- Respuestas a una pregunta (CU-14). Si se borra la pregunta, se borran
-- sus respuestas. fecha_ult_edicion queda en NULL hasta que el autor la
-- edita (CU-15). aceptada = la marco el docente o el autor de la pregunta.
-- ---------------------------------------------------------------------
CREATE TABLE respuesta (
    id_respuesta       INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_pregunta        INT UNSIGNED NOT NULL,
    id_autor           INT UNSIGNED NOT NULL,
    texto              VARCHAR(1000) NOT NULL,
    fecha_creacion     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_ult_edicion  DATETIME NULL,
    aceptada           BOOLEAN NOT NULL DEFAULT FALSE,
    PRIMARY KEY (id_respuesta),
    CONSTRAINT chk_respuesta_edicion CHECK (fecha_ult_edicion >= fecha_creacion),
    CONSTRAINT fk_respuesta_pregunta FOREIGN KEY (id_pregunta)
        REFERENCES pregunta (id_pregunta)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_respuesta_autor FOREIGN KEY (id_autor)
        REFERENCES usuario (id_usuario)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabla: notificacion
-- Avisos que recibe cada usuario por lo que pasa en los foros (RN11, CU-19).
-- El mensaje se guarda ya armado, asi se sigue leyendo aunque despues se
-- elimine la pregunta (por eso id_pregunta queda en NULL).
-- El indice (id_usuario, leida) acelera el conteo de la campanita.
-- ---------------------------------------------------------------------
CREATE TABLE notificacion (
    id_notificacion  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_usuario       INT UNSIGNED NOT NULL,
    id_curso         INT UNSIGNED NOT NULL,
    id_pregunta      INT UNSIGNED NULL,
    tipo             ENUM('preguntaCreada','respuestaNueva','preguntaCerrada','preguntaEliminada') NOT NULL,
    mensaje          VARCHAR(300) NOT NULL,
    fecha            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    leida            BOOLEAN NOT NULL DEFAULT FALSE,
    PRIMARY KEY (id_notificacion),
    INDEX idx_notificacion_no_leidas (id_usuario, leida),
    CONSTRAINT fk_notificacion_usuario FOREIGN KEY (id_usuario)
        REFERENCES usuario (id_usuario)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_notificacion_curso FOREIGN KEY (id_curso)
        REFERENCES curso (id_curso)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_notificacion_pregunta FOREIGN KEY (id_pregunta)
        REFERENCES pregunta (id_pregunta)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- DATOS DE PRUEBA
-- Las fechas estan pensadas para probar el sistema entre noviembre de 2026
-- y marzo de 2027 (hay cursos en curso, por empezar y terminados).
-- Si se prueba mas adelante, correr fecha_inicio / fecha_fin de los cursos.
--
-- Contraseñas (tambien en el README):
--   usuarios Pro       -> Pro12345
--   usuarios gratuitos -> Gratis12345
--   administrador      -> Admin12345
-- =====================================================================

-- Administrador (CU-02, CU-06)
INSERT INTO administrador (id_administrador, nombre, email, contrasenia) VALUES
  (1, 'Administrador', 'admin@cursolandia.com', '$2y$10$Reb..sESw1oaBn.MYiauwuewT3LKoyjd9llymMHZZy8oIzCWztk1u');

-- Usuarios: 4 Pro y 4 gratuitos (el id es el DNI)
INSERT INTO usuario (id_usuario, nombre, apellido, email, contrasenia, fecha_nacimiento, telefono, antecedentes, imagen_principal, tipo_plan, fecha_venc_pro) VALUES
  (35123456, 'Ana', 'Pérez', 'ana@cursolandia.com',
   '$2y$12$0iaB91/KjRhYBk7DgbaK0OQXi6J0wv740a6rlb2UYBux287mhB3Nm', '1995-04-12', NULL,
   'Docente de informática. Enseño programación y desarrollo web a personas que arrancan de cero.',
   'img/ejemplos/ana.jpg', 'pro', '2027-12-31'),
  (40987654, 'Bruno', 'Gómez', 'bruno@cursolandia.com',
   '$2y$12$WsSZetQ7WHq5LV3wtixvx./5e2lMkDyPh4sGWit4rmwAIMmmRfN1q', '1998-09-03', '2664501010',
   'Estudiante de la Tecnicatura en Web. Me gusta el diseño y estoy aprendiendo a programar.',
   'img/ejemplos/bruno.jpg', 'gratuito', NULL),
  (28456789, 'Carla', 'Rodríguez', 'carla@cursolandia.com',
   '$2y$10$47pqcTdyhXVxavkhhaxsCO/X3icxUMP8oMaTVrfJL4tAr0pjQ3fnO', '1988-06-21', '2664112233',
   'Fotógrafa y diseñadora gráfica. Hace diez años que trabajo con marcas y emprendimientos.',
   'img/avatar-default.png', 'pro', '2027-09-28'),
  (31222333, 'Diego', 'Fernández', 'diego@cursolandia.com',
   '$2y$10$47pqcTdyhXVxavkhhaxsCO/X3icxUMP8oMaTVrfJL4tAr0pjQ3fnO', '1985-02-14', '2664223344',
   'Desarrollador web full stack. Doy clases de programación desde 2015.',
   'img/avatar-default.png', 'pro', '2027-06-30'),
  (33444555, 'Lucía', 'Benítez', 'lucia@cursolandia.com',
   '$2y$10$47pqcTdyhXVxavkhhaxsCO/X3icxUMP8oMaTVrfJL4tAr0pjQ3fnO', '1990-11-03', NULL,
   'Licenciada en Administración. Ayudo a emprendedores con el marketing y los números de su negocio.',
   'img/avatar-default.png', 'pro', '2027-03-31'),
  (42111222, 'Martín', 'Sosa', 'martin@cursolandia.com',
   '$2y$10$6w6hMvFJJaX5SB53ABT0YO1XwlCFUg48uNApjWpgoe5ym40ZNx6zy', '2000-08-09', '2664334455',
   'Músico y profesor de guitarra. En mis ratos libres estudio programación.',
   'img/avatar-default.png', 'gratuito', NULL),
  (43555666, 'Sofía', 'Álvarez', 'sofia@cursolandia.com',
   '$2y$10$6w6hMvFJJaX5SB53ABT0YO1XwlCFUg48uNApjWpgoe5ym40ZNx6zy', '2001-01-27', NULL,
   'Cocinera. Me encanta enseñar recetas fáciles, ricas y económicas.',
   'img/avatar-default.png', 'gratuito', NULL),
  (44777888, 'Tomás', 'Herrera', 'tomas@cursolandia.com',
   '$2y$10$6w6hMvFJJaX5SB53ABT0YO1XwlCFUg48uNApjWpgoe5ym40ZNx6zy', '2002-05-15', '2664445566',
   'Estudiante de Ingeniería. Doy clases de apoyo de matemática y física.',
   'img/avatar-default.png', 'gratuito', NULL);

-- Solicitudes de Plan Pro (los comprobantes son PDF de prueba, de bancos que no existen)
--   4 aprobadas (los usuarios Pro), 3 denegadas con motivo y 3 pendientes para
--   que el administrador tenga algo para revisar. Martín y Tomás muestran el
--   caso de una solicitud denegada y vuelta a enviar.
INSERT INTO solicitud_plan_pro (id_solicitud, id_usuario, fecha_envio, mensaje, comprobante, estado, fecha_venc_asignada, motivo_rechazo) VALUES
  (1, 35123456, '2026-01-10', 'Hola, adjunto la transferencia por el plan Pro. ¡Gracias!',
   'uploads/comprobantes/ejemplo_comprobante_01.pdf', 'aprobada', '2027-12-31', NULL),
  (2, 33444555, '2026-03-25', 'Buenas, les mando el comprobante del plan Pro anual.',
   'uploads/comprobantes/ejemplo_comprobante_02.pdf', 'aprobada', '2027-03-31', NULL),
  (3, 31222333, '2026-06-25', 'Adjunto el pago del plan Pro para seguir publicando cursos.',
   'uploads/comprobantes/ejemplo_comprobante_03.pdf', 'aprobada', '2027-06-30', NULL),
  (4, 28456789, '2026-09-28', 'Hola, adjunto el comprobante de la transferencia por el plan Pro anual. ¡Gracias!',
   'uploads/comprobantes/ejemplo_comprobante_04.pdf', 'aprobada', '2027-09-28', NULL),
  (5, 40987654, '2026-10-20', 'Quiero pasarme a Pro, adjunto el comprobante.',
   'uploads/comprobantes/ejemplo_comprobante_05.pdf', 'denegada', NULL,
   'El importe transferido no corresponde al plan Pro ($ 15.000).'),
  (6, 44777888, '2026-10-30', 'Hola, les mando la transferencia del plan Pro.',
   'uploads/comprobantes/ejemplo_comprobante_06.pdf', 'denegada', NULL,
   'La transferencia todavía no figura acreditada. Volvé a enviarla cuando se acredite.'),
  (7, 42111222, '2026-11-08', 'Te mando el comprobante del pago del plan Pro.',
   'uploads/comprobantes/ejemplo_comprobante_07.pdf', 'denegada', NULL,
   'El comprobante no se lee bien. Volvé a enviarlo con mejor calidad.'),
  (8, 42111222, '2026-11-14', 'Lo vuelvo a mandar, ahora se tiene que ver bien.',
   'uploads/comprobantes/ejemplo_comprobante_08.pdf', 'pendiente', NULL, NULL),
  (9, 43555666, '2026-11-14', 'Hola, quiero pasarme a Pro para poder crear más cursos de cocina. Adjunto el comprobante.',
   'uploads/comprobantes/ejemplo_comprobante_09.pdf', 'pendiente', NULL, NULL),
  (10, 44777888, '2026-11-15', 'Ya se acreditó, adjunto de nuevo el comprobante.',
   'uploads/comprobantes/ejemplo_comprobante_10.pdf', 'pendiente', NULL, NULL);

-- Cursos
--   Los gratuitos (Bruno, Martín, Sofía y Tomás) tienen como máximo 3 cursos
--   activos (RN01): el cuarto ya terminó o todavía no empezó... salvo Bruno,
--   que tiene 3 y está justo en el límite.
--   costo NULL = gratis / cupo NULL = sin límite
INSERT INTO curso (id_curso, titulo, descripcion, costo, cupo, fecha_inicio, fecha_fin, tipo_acceso, id_creador) VALUES
  -- Ana (Pro)
  (1, 'Introducción a PHP', 'Aprendé lo básico de PHP desde cero: variables, condicionales, formularios y conexión a una base de datos.', NULL, 20, '2026-10-01', '2027-03-31', 'publico', 35123456),
  (3, 'Arquitectura de computadoras', 'Cómo funciona una computadora por dentro: procesador, memoria, buses y dispositivos de entrada y salida.', 30000.00, 10, '2026-09-01', '2027-07-31', 'publico', 35123456),
  (5, 'Excel para el trabajo', 'Fórmulas, tablas dinámicas y gráficos para el día a día en la oficina.', NULL, 30, '2026-03-01', '2026-06-30', 'publico', 35123456),
  (7, 'Pintura acrílica para principiantes', 'Materiales, mezcla de colores y técnicas básicas para pintar tus primeros cuadros.', NULL, NULL, '2026-11-12', '2027-02-02', 'publico', 35123456),
  (8, 'Bases de datos', '¿Qué vas a aprender?\r\nLos conceptos fundamentales de las bases de datos, cómo crear y organizar tablas, y cómo consultar información con SQL.\r\n\r\n¿A quién está orientado?\r\nA personas sin conocimientos previos que quieran iniciarse en el mundo de las bases de datos.', NULL, 25, '2026-11-13', '2027-03-31', 'publico', 35123456),
  (9, 'Introducción a la Programación con Python', '¿Qué vas a aprender?\r\nLos fundamentos de la programación: variables, tipos de datos, condicionales, ciclos y funciones, con ejercicios prácticos.\r\n\r\n¿A quién está orientado?\r\nA personas sin conocimientos previos que quieran aprender a programar desde cero.', 45000.00, NULL, '2026-11-13', '2027-03-24', 'publico', 35123456),
  (10, 'Introducción al Desarrollo Web con HTML y CSS', '¿Qué vas a aprender?\r\nA crear páginas web desde cero usando HTML para la estructura y CSS para el diseño.\r\n\r\n¿A quién está orientado?\r\nA personas que quieran iniciarse en el desarrollo web y construir sus propios sitios.', NULL, NULL, '2026-11-14', '2027-03-31', 'privado', 35123456),
  (11, 'Introducción a JavaScript', '¿Qué vas a aprender?\r\nVariables, condicionales, ciclos y funciones en JavaScript, y cómo darle interactividad a una página web.\r\n\r\n¿A quién está orientado?\r\nA quienes ya conocen HTML y CSS y quieren dar el siguiente paso.', 50000.00, 20, '2026-11-17', '2027-03-31', 'privado', 35123456),
  -- Bruno (gratuito, 3 activos: en el límite)
  (2, 'Introducción al Diseño Gráfico', 'Principios de diseño, tipografía y composición para crear piezas gráficas y páginas web.', 15000.00, 50, '2026-11-10', '2027-03-20', 'publico', 40987654),
  (4, 'Java para principiantes', 'Aprendé a programar en Java: clases, objetos y los primeros programas de consola.', NULL, 40, '2026-09-15', '2027-02-28', 'publico', 40987654),
  (6, 'Matemática aplicada a la programación', 'Lógica, conjuntos, funciones y algo de álgebra, todo con ejemplos de programación.', 20000.00, 15, '2026-11-30', '2027-05-26', 'publico', 40987654),
  -- Carla (Pro)
  (12, 'Fotografía digital para principiantes', 'Conocé tu cámara (o tu celular), la exposición, el enfoque y la composición para sacar mejores fotos.', NULL, 30, '2026-10-05', '2027-02-28', 'publico', 28456789),
  (13, 'Diseño de logos e identidad visual', 'Cómo pensar y diseñar la identidad visual de una marca: logo, colores y tipografías.', 25000.00, 20, '2026-11-01', '2027-03-15', 'publico', 28456789),
  (14, 'Edición de fotos con Lightroom', 'Revelado digital, ajustes de color y flujo de trabajo para editar tus fotos de forma profesional.', 18000.00, 15, '2026-12-01', '2027-04-30', 'privado', 28456789),
  (15, 'Teoría del color', 'El círculo cromático, armonías y contrastes, y cómo usar el color en diseño y en arte.', NULL, NULL, '2026-04-01', '2026-07-31', 'publico', 28456789),
  -- Diego (Pro)
  (16, 'Desarrollo web con PHP y MySQL', 'Armá una aplicación web completa: formularios, sesiones, base de datos y buenas prácticas de seguridad.', 40000.00, 25, '2026-09-20', '2027-03-20', 'publico', 31222333),
  (17, 'Git y GitHub desde cero', 'Control de versiones para no perder nunca más tu trabajo: commits, ramas y repositorios en GitHub.', NULL, NULL, '2026-11-02', '2027-01-31', 'publico', 31222333),
  (18, 'Python para análisis de datos', 'Usá Python para leer, limpiar y graficar datos reales paso a paso.', 35000.00, 20, '2026-10-15', '2027-04-15', 'privado', 31222333),
  (19, 'Introducción a las redes', 'Cómo se conectan las computadoras: direcciones IP, routers, wifi y los protocolos más usados.', NULL, 40, '2027-01-11', '2027-05-28', 'publico', 31222333),
  (20, 'Lógica de programación', 'Aprendé a pensar como programador: algoritmos, diagramas y pseudocódigo antes de escribir código.', NULL, NULL, '2026-03-02', '2026-06-26', 'publico', 31222333),
  -- Lucía (Pro)
  (21, 'Marketing digital para emprendedores', 'Cómo dar a conocer tu emprendimiento en internet: público objetivo, contenidos y publicidad.', 30000.00, 30, '2026-10-01', '2027-02-15', 'publico', 33444555),
  (22, 'Excel avanzado: tablas dinámicas y macros', 'Llevá tus planillas al siguiente nivel con tablas dinámicas, funciones avanzadas y macros.', 22000.00, 25, '2026-11-10', '2027-03-10', 'publico', 33444555),
  (23, 'Community manager', 'Planificá y gestioná las redes sociales de una marca: calendario de publicaciones y métricas.', NULL, 15, '2026-11-05', '2027-01-29', 'privado', 33444555),
  (24, 'Finanzas personales', 'Organizá tus gastos, armá un presupuesto y aprendé a ahorrar con objetivos claros.', NULL, NULL, '2026-12-01', '2027-03-31', 'publico', 33444555),
  -- Martín (gratuito: 3 activos + 1 terminado)
  (25, 'Guitarra para principiantes', 'Tus primeros acordes, ritmos y canciones en la guitarra, sin necesidad de saber música.', NULL, 12, '2026-10-01', '2027-02-26', 'publico', 42111222),
  (26, 'Teoría musical básica', 'Notas, escalas, acordes y ritmo explicados de forma simple y con ejemplos.', 8000.00, 20, '2026-11-03', '2027-01-29', 'publico', 42111222),
  (27, 'Ukelele en 8 clases', 'Un curso corto y divertido para tocar tus primeras canciones con el ukelele.', NULL, 10, '2026-11-23', '2027-01-15', 'privado', 42111222),
  (28, 'Canto inicial', 'Respiración, afinación y cuidado de la voz para empezar a cantar.', NULL, NULL, '2026-02-02', '2026-05-29', 'publico', 42111222),
  -- Sofía (gratuito: 3 activos + 1 terminado)
  (29, 'Cocina saludable y económica', 'Recetas ricas, sanas y baratas para cocinar en casa toda la semana.', NULL, 25, '2026-10-12', '2027-02-12', 'publico', 43555666),
  (30, 'Repostería: tortas y budines', 'Masas, rellenos y decoraciones para tortas y budines caseros.', 12000.00, 15, '2026-11-07', '2027-01-23', 'publico', 43555666),
  (31, 'Panadería casera', 'Pan, prepizzas y facturas hechos en casa, paso a paso.', 10000.00, 10, '2026-11-20', '2027-03-19', 'privado', 43555666),
  (32, 'Cocina vegetariana', 'Platos completos y nutritivos sin carne, con ingredientes fáciles de conseguir.', NULL, NULL, '2026-04-06', '2026-08-28', 'publico', 43555666),
  -- Tomás (gratuito: 3 activos + 1 terminado)
  (33, 'Matemática para el ingreso universitario', 'Repaso de los temas del ingreso: números, ecuaciones, funciones y trigonometría.', NULL, 50, '2026-09-01', '2027-02-26', 'publico', 44777888),
  (34, 'Física básica', 'Cinemática, dinámica y energía explicadas con ejercicios resueltos.', 9000.00, 30, '2026-10-19', '2027-03-12', 'publico', 44777888),
  (35, 'Estadística sin miedo', 'Promedios, gráficos y probabilidad para entender los datos que nos rodean.', NULL, 20, '2026-11-09', '2027-02-05', 'privado', 44777888),
  (36, 'Álgebra paso a paso', 'Expresiones algebraicas, factoreo y ecuaciones, desde lo más básico.', NULL, NULL, '2026-03-09', '2026-07-03', 'publico', 44777888);

-- Etiquetas (se guardan en minúsculas)
INSERT INTO etiqueta (id_etiqueta, nombre) VALUES
  (1, 'programación'), (2, 'php'), (3, 'java'), (4, 'principiantes'), (5, 'diseño'),
  (6, 'diseño web'), (7, 'hardware'), (8, 'excel'), (9, 'oficina'), (10, 'redes'),
  (11, 'fotografia'), (12, 'cocina'), (13, 'veterinaria'), (14, 'pintura'), (15, 'arte'),
  (16, 'colores'), (17, 'sql'), (18, 'bases de datos'), (19, 'desarrollo de software'), (20, 'informática'),
  (21, 'consultas sql'), (22, 'python'), (23, 'lógica de programación'), (24, 'algoritmos'), (25, 'código'),
  (26, 'tecnología'), (27, 'programación desde cero'), (28, 'html'), (29, 'css'), (30, 'desarrollo web'),
  (31, 'javascript'), (32, 'frontend'), (33, 'funciones'), (34, 'interactividad web'), (35, 'branding'),
  (36, 'edición de fotos'), (37, 'git'), (38, 'análisis de datos'), (39, 'marketing'), (40, 'emprendimiento'),
  (41, 'redes sociales'), (42, 'finanzas'), (43, 'música'), (44, 'guitarra'), (45, 'repostería'),
  (46, 'panadería'), (47, 'vegetariana'), (48, 'matemática'), (49, 'ingreso universitario'), (50, 'física'),
  (51, 'estadística');

-- Etiquetas de cada curso
INSERT INTO curso_etiqueta (id_curso, id_etiqueta) VALUES
  (1, 1), (1, 2), (1, 4),
  (2, 5), (2, 6),
  (3, 7), (3, 20),
  (4, 1), (4, 3), (4, 4),
  (5, 8), (5, 9),
  (6, 48), (6, 1),
  (7, 14), (7, 15), (7, 16),
  (8, 1), (8, 4), (8, 17), (8, 18), (8, 19), (8, 20), (8, 21),
  (9, 1), (9, 4), (9, 19), (9, 20), (9, 22), (9, 23), (9, 24), (9, 25), (9, 26), (9, 27),
  (10, 1), (10, 6), (10, 28), (10, 29), (10, 30),
  (11, 1), (11, 4), (11, 23), (11, 28), (11, 29), (11, 30), (11, 31), (11, 32), (11, 33), (11, 34),
  (12, 11), (12, 15), (12, 4),
  (13, 5), (13, 35),
  (14, 11), (14, 36),
  (15, 16), (15, 5), (15, 15),
  (16, 2), (16, 17), (16, 30), (16, 1),
  (17, 1), (17, 4), (17, 37),
  (18, 22), (18, 38), (18, 1),
  (19, 10), (19, 20),
  (20, 23), (20, 4),
  (21, 39), (21, 40), (21, 41),
  (22, 8), (22, 9),
  (23, 41), (23, 39),
  (24, 42), (24, 40),
  (25, 43), (25, 44), (25, 4),
  (26, 43),
  (27, 43), (27, 4),
  (28, 43),
  (29, 12),
  (30, 12), (30, 45),
  (31, 12), (31, 46),
  (32, 12), (32, 47),
  (33, 48), (33, 49),
  (34, 50), (34, 48),
  (35, 48), (35, 51), (35, 38),
  (36, 48);

-- Intereses de cada usuario (sirven para los recomendados, CU-08)
INSERT INTO usuario_interes (id_usuario, id_etiqueta) VALUES
  (35123456, 11), (35123456, 39), (35123456, 43),
  (40987654, 5), (40987654, 1), (40987654, 12),
  (28456789, 6), (28456789, 39),
  (31222333, 48), (31222333, 43),
  (33444555, 11), (33444555, 12),
  (42111222, 1), (42111222, 11),
  (43555666, 39), (43555666, 40), (43555666, 8),
  (44777888, 22), (44777888, 38), (44777888, 1);

-- Inscripciones
--   Gratis y público -> confirmada al instante / pago o privado -> pendiente
--   hasta que el creador la confirma (RN06). Los gratuitos tienen como máximo
--   3 inscripciones en cursos que no terminaron (RN02): Martín, Sofía y Tomás
--   están justo en el límite.
INSERT INTO inscripcion (id_inscripcion, id_usuario, id_curso, fecha_solicitud, estado, fecha_confirmacion) VALUES
  (1, 40987654, 1, '2026-10-02', 'confirmada', '2026-10-02'),
  (2, 40987654, 5, '2026-03-05', 'confirmada', '2026-03-05'),
  (3, 40987654, 10, '2026-11-14', 'confirmada', '2026-11-14'),
  (4, 35123456, 4, '2026-09-16', 'confirmada', '2026-09-16'),
  (5, 35123456, 2, '2026-11-11', 'confirmada', '2026-11-12'),
  (6, 35123456, 16, '2026-11-14', 'pendiente', NULL),
  (7, 35123456, 25, '2026-10-10', 'confirmada', '2026-10-10'),
  (8, 28456789, 17, '2026-11-03', 'confirmada', '2026-11-03'),
  (9, 28456789, 21, '2026-10-05', 'confirmada', '2026-10-06'),
  (10, 28456789, 30, '2026-11-13', 'pendiente', NULL),
  (11, 31222333, 33, '2026-09-10', 'confirmada', '2026-09-10'),
  (12, 31222333, 26, '2026-11-04', 'confirmada', '2026-11-05'),
  (13, 31222333, 12, '2026-10-20', 'confirmada', '2026-10-20'),
  (14, 33444555, 12, '2026-10-08', 'confirmada', '2026-10-08'),
  (15, 33444555, 13, '2026-11-12', 'pendiente', NULL),
  (16, 33444555, 29, '2026-10-15', 'confirmada', '2026-10-15'),
  (17, 42111222, 16, '2026-10-01', 'confirmada', '2026-10-02'),
  (18, 42111222, 17, '2026-11-05', 'confirmada', '2026-11-05'),
  (19, 42111222, 12, '2026-10-12', 'confirmada', '2026-10-12'),
  (20, 42111222, 20, '2026-03-10', 'confirmada', '2026-03-10'),
  (21, 43555666, 21, '2026-11-10', 'pendiente', NULL),
  (22, 43555666, 23, '2026-11-06', 'confirmada', '2026-11-07'),
  (23, 43555666, 22, '2026-11-11', 'confirmada', '2026-11-12'),
  (24, 44777888, 18, '2026-10-16', 'confirmada', '2026-10-18'),
  (25, 44777888, 1, '2026-10-05', 'confirmada', '2026-10-05'),
  (26, 44777888, 10, '2026-11-15', 'pendiente', NULL);

-- Material de ejemplo (el PDF si se sube al repositorio, ver .gitignore)
INSERT INTO archivo (id_archivo, id_curso, nombre, tamanio_mb, fecha_carga, url) VALUES
  (1, 10, 'Propiedades_css.pdf', 0.14, '2026-11-14', 'uploads/material/ejemplo_material.pdf');

-- Foro: preguntas
--   1: destacada y cerrada (Martín aceptó la respuesta de Carla)
--   2, 3 y 4: abiertas
INSERT INTO pregunta (id_pregunta, id_curso, id_autor, texto, fecha_creacion, destacada, cerrada) VALUES
  (1, 12, 42111222, '¿Qué cámara me recomiendan para empezar? Tengo poco presupuesto y no sé si conviene una réflex usada o seguir practicando con el celular.', '2026-11-10 10:15:00', TRUE, TRUE),
  (2, 12, 31222333, '¿La tarea de la semana 2 se entrega por mail o la traemos impresa a la clase?', '2026-11-13 20:30:00', FALSE, FALSE),
  (3, 10, 40987654, '¿Cuál es la diferencia entre un div y un span?', '2026-11-14 16:12:00', FALSE, FALSE),
  (4, 16, 42111222, 'No me conecta a la base de datos, me sale "Access denied for user". ¿Qué puede ser?', '2026-11-12 19:00:00', FALSE, FALSE);

-- Foro: respuestas (nadie responde dos veces seguidas, RN07)
INSERT INTO respuesta (id_respuesta, id_pregunta, id_autor, texto, fecha_creacion, fecha_ult_edicion, aceptada) VALUES
  (1, 1, 33444555, 'Yo arranqué con el celular y aprendí mucho de composición antes de comprarme una cámara.', '2026-11-10 12:40:00', NULL, FALSE),
  (2, 1, 42111222, 'Buena idea, mientras tanto voy practicando con el celular.', '2026-11-10 14:05:00', NULL, FALSE),
  (3, 1, 28456789, 'Para empezar alcanza con una réflex o mirrorless de entrada usada. Lo importante es aprender a usar el modo manual, que lo vemos en la clase 3.', '2026-11-10 18:05:00', NULL, TRUE),
  (4, 2, 28456789, 'Mandámela por mail antes del viernes, así la comentamos en la clase.', '2026-11-14 09:00:00', NULL, FALSE),
  (5, 3, 35123456, 'El div es un bloque: ocupa todo el ancho y empieza en una línea nueva. El span va en línea, dentro del texto, y ocupa solo lo que necesita.', '2026-11-14 18:30:00', NULL, FALSE),
  (6, 4, 31222333, 'Revisá el usuario y la contraseña de config.php, y que ese usuario tenga permisos sobre la base (GRANT).', '2026-11-12 21:10:00', NULL, FALSE),
  (7, 4, 42111222, '¡Era eso! Le faltaban los permisos al usuario. Ya anda, gracias.', '2026-11-13 08:30:00', '2026-11-13 08:45:00', FALSE);

-- Notificaciones que generaron esas acciones (RN11). Algunas sin leer para
-- que se vea la campanita: entrar como Bruno, Diego, Carla, Lucía o Martín.
INSERT INTO notificacion (id_usuario, id_curso, id_pregunta, tipo, mensaje, fecha, leida) VALUES
  -- pregunta 1
  (28456789, 12, 1, 'preguntaCreada', 'Martín hizo una pregunta en Fotografía digital para principiantes: "¿Qué cámara me recomiendan para empezar? Tengo poco presupue..."', '2026-11-10 10:15:00', TRUE),
  (31222333, 12, 1, 'preguntaCreada', 'Martín hizo una pregunta en Fotografía digital para principiantes: "¿Qué cámara me recomiendan para empezar? Tengo poco presupue..."', '2026-11-10 10:15:00', TRUE),
  (33444555, 12, 1, 'preguntaCreada', 'Martín hizo una pregunta en Fotografía digital para principiantes: "¿Qué cámara me recomiendan para empezar? Tengo poco presupue..."', '2026-11-10 10:15:00', TRUE),
  (42111222, 12, 1, 'respuestaNueva', 'Lucía respondió la pregunta "¿Qué cámara me recomiendan para empezar? Tengo poco presupue..." en Fotografía digital para principiantes', '2026-11-10 12:40:00', TRUE),
  (33444555, 12, 1, 'respuestaNueva', 'Martín respondió la pregunta "¿Qué cámara me recomiendan para empezar? Tengo poco presupue..." en Fotografía digital para principiantes', '2026-11-10 14:05:00', TRUE),
  (42111222, 12, 1, 'respuestaNueva', 'Carla respondió la pregunta "¿Qué cámara me recomiendan para empezar? Tengo poco presupue..." en Fotografía digital para principiantes', '2026-11-10 18:05:00', TRUE),
  (33444555, 12, 1, 'respuestaNueva', 'Carla respondió la pregunta "¿Qué cámara me recomiendan para empezar? Tengo poco presupue..." en Fotografía digital para principiantes', '2026-11-10 18:05:00', TRUE),
  (33444555, 12, 1, 'preguntaCerrada', 'Se cerró la pregunta "¿Qué cámara me recomiendan para empezar? Tengo poco presupue..." en Fotografía digital para principiantes: ya tiene una respuesta aceptada', '2026-11-10 20:00:00', FALSE),
  (28456789, 12, 1, 'preguntaCerrada', '¡Aceptaron tu respuesta a la pregunta "¿Qué cámara me recomiendan para empezar? Tengo poco presupue..."! (Fotografía digital para principiantes)', '2026-11-10 20:00:00', FALSE),
  -- pregunta 2
  (28456789, 12, 2, 'preguntaCreada', 'Diego hizo una pregunta en Fotografía digital para principiantes: "¿La tarea de la semana 2 se entrega por mail o la traemos im..."', '2026-11-13 20:30:00', TRUE),
  (33444555, 12, 2, 'preguntaCreada', 'Diego hizo una pregunta en Fotografía digital para principiantes: "¿La tarea de la semana 2 se entrega por mail o la traemos im..."', '2026-11-13 20:30:00', FALSE),
  (42111222, 12, 2, 'preguntaCreada', 'Diego hizo una pregunta en Fotografía digital para principiantes: "¿La tarea de la semana 2 se entrega por mail o la traemos im..."', '2026-11-13 20:30:00', FALSE),
  (31222333, 12, 2, 'respuestaNueva', 'Carla respondió la pregunta "¿La tarea de la semana 2 se entrega por mail o la traemos im..." en Fotografía digital para principiantes', '2026-11-14 09:00:00', FALSE),
  -- pregunta 3
  (35123456, 10, 3, 'preguntaCreada', 'Bruno hizo una pregunta en Introducción al Desarrollo Web con HTML y CSS: "¿Cuál es la diferencia entre un div y un span?"', '2026-11-14 16:12:00', TRUE),
  (40987654, 10, 3, 'respuestaNueva', 'Ana respondió la pregunta "¿Cuál es la diferencia entre un div y un span?" en Introducción al Desarrollo Web con HTML y CSS', '2026-11-14 18:30:00', FALSE),
  -- pregunta 4
  (31222333, 16, 4, 'preguntaCreada', 'Martín hizo una pregunta en Desarrollo web con PHP y MySQL: "No me conecta a la base de datos, me sale "Access denied for..."', '2026-11-12 19:00:00', TRUE),
  (42111222, 16, 4, 'respuestaNueva', 'Diego respondió la pregunta "No me conecta a la base de datos, me sale "Access denied for..." en Desarrollo web con PHP y MySQL', '2026-11-12 21:10:00', TRUE),
  (31222333, 16, 4, 'respuestaNueva', 'Martín respondió la pregunta "No me conecta a la base de datos, me sale "Access denied for..." en Desarrollo web con PHP y MySQL', '2026-11-13 08:30:00', FALSE);
