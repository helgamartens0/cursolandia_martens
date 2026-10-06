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

-- Se borran primero las tablas que dependen de otras (curso -> usuario)
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

-- Cursos de prueba: 2 de Ana (Pro) y 2 de Bruno (gratuito)
INSERT INTO curso (id_curso, titulo, descripcion, costo, cupo, fecha_inicio, fecha_fin, tipo_acceso, id_creador)
VALUES
  (1, 'Introducción a PHP', 'Aprendé lo básico de PHP desde cero.', NULL, 20, '2026-10-15', '2026-12-15', 'publico', 35123456),
  (2, 'Introducción al Diseño Gráfico', 'Aprendé a diseñar páginas web desde cero.', 1000, 50, '2026-11-10', '2026-12-20', 'publico', 40987654),
  (3, 'Arquitectura de computadoras', 'Aprendé arquitectura de computadoras como un pro.', 30000, 10, '2027-02-10', '2027-12-12', 'publico', 35123456),
  (4, 'Java para principiantes', 'Aprendé a programar en Java.', NULL, 200, '2026-10-15', '2026-12-15', 'publico', 40987654);
