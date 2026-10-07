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

-- Se borran primero las tablas que dependen de otras (inscripcion -> curso -> usuario)
DROP TABLE IF EXISTS inscripcion;
DROP TABLE IF EXISTS curso;
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

-- =====================================================================
-- Datos de prueba
-- =====================================================================

-- Usuarios de prueba (contraseñas en el README):
--   Ana Pérez   (plan Pro)      ana@cursolandia.com   / Pro12345
--   Bruno Gómez (plan gratuito) bruno@cursolandia.com / Gratis12345
INSERT INTO usuario (id_usuario, nombre, apellido, email, contrasenia, fecha_nacimiento, tipo_plan, fecha_venc_pro)
VALUES
  (35123456, 'Ana', 'Pérez', 'ana@cursolandia.com',
   '$2y$12$0iaB91/KjRhYBk7DgbaK0OQXi6J0wv740a6rlb2UYBux287mhB3Nm',
   '1995-04-12', 'pro', '2027-12-31'),
  (40987654, 'Bruno', 'Gómez', 'bruno@cursolandia.com',
   '$2y$12$WsSZetQ7WHq5LV3wtixvx./5e2lMkDyPh4sGWit4rmwAIMmmRfN1q',
   '1998-09-03', 'gratuito', NULL);

-- Cursos de prueba
-- OJO: las fechas están pensadas para que haya cursos activos hasta 2027.
-- Si se prueba más adelante, ajustar fecha_inicio / fecha_fin.
--   1, 3 y 5 son de Ana (Pro)  -> aparecen destacados (5 ya terminó)
--   2 y 4 son de Bruno (gratuito)
INSERT INTO curso (id_curso, titulo, descripcion, costo, cupo, fecha_inicio, fecha_fin, tipo_acceso, id_creador)
VALUES
  (1, 'Introducción a PHP', 'Aprendé lo básico de PHP desde cero.', NULL, 20, '2026-10-01', '2027-03-31', 'publico', 35123456),
  (2, 'Introducción al Diseño Gráfico', 'Aprendé a diseñar páginas web desde cero.', 1000, 50, '2026-11-10', '2026-12-20', 'publico', 40987654),
  (3, 'Arquitectura de computadoras', 'Aprendé arquitectura de computadoras como un pro.', 30000, 10, '2026-09-01', '2027-07-31', 'publico', 35123456),
  (4, 'Java para principiantes', 'Aprendé a programar en Java.', NULL, 200, '2026-09-15', '2027-02-28', 'publico', 40987654),
  (5, 'Excel para el trabajo', 'Fórmulas, tablas dinámicas y gráficos.', NULL, 30, '2026-03-01', '2026-06-30', 'publico', 35123456);

-- Inscripciones de prueba
--   Bruno: PHP confirmada (activo) / Arquitectura pendiente (pago) / Excel confirmada (ya terminó)
--   Ana:   Java confirmada (activo) / Diseño confirmada (todavía no empezó)
INSERT INTO inscripcion (id_usuario, id_curso, fecha_solicitud, estado, fecha_confirmacion)
VALUES
  (40987654, 1, '2026-09-28', 'confirmada', '2026-09-28'),
  (40987654, 3, '2026-10-02', 'pendiente',  NULL),
  (40987654, 5, '2026-02-20', 'confirmada', '2026-02-20'),
  (35123456, 4, '2026-09-10', 'confirmada', '2026-09-10'),
  (35123456, 2, '2026-10-03', 'confirmada', '2026-10-04');
