<?php
require_once __DIR__ . '/../traits/AccesoBD.php';
require_once __DIR__ . '/../clases/Inscripcion.php';

class ControlInscripcion
{
    use AccesoBD;

    //devuelve 'pendiente' o 'confirmada' si el usuario ya esta inscripto en el curso, o null si todavia no
    public function obtenerEstado(int $idUsuario, int $idCurso): ?string
    {
        $sql = "SELECT estado FROM inscripcion WHERE id_usuario = ? AND id_curso = ?";
        $fila = mysqli_fetch_assoc($this->consultar($sql, 'ii', [$idUsuario, $idCurso]));

        if ($fila === null) {
            return null;
        }
        return $fila['estado'];
    }

    //cuantas personas estan inscriptas en el curso (pendientes y confirmadas)
    public function contarInscriptos(int $idCurso): int
    {
        $sql = "SELECT COUNT(*) AS cantidad FROM inscripcion WHERE id_curso = ?";
        $fila = mysqli_fetch_assoc($this->consultar($sql, 'i', [$idCurso]));
        return (int) $fila['cantidad'];
    }

    //guarda la inscripcion
    public function inscribir(int $idUsuario, int $idCurso, bool $confirmada): void
    {
        if ($confirmada) {
            $sql = "INSERT INTO inscripcion (id_usuario, id_curso, estado, fecha_confirmacion)
                    VALUES (?, ?, 'confirmada', CURDATE())";
        } else {
            $sql = "INSERT INTO inscripcion (id_usuario, id_curso, estado)
                    VALUES (?, ?, 'pendiente')";
        }
        $this->consultar($sql, 'ii', [$idUsuario, $idCurso]);
    }
    
    //todas las inscripciones de un curso, con los datos de quien se inscribio
    public function obtenerDeCurso(int $idCurso): array
    {
        $sql = "SELECT i.id_inscripcion, i.fecha_solicitud, i.estado, i.fecha_confirmacion,
                       u.id_usuario, u.nombre, u.apellido, u.email, u.fecha_nacimiento,
                       u.telefono, u.antecedentes, u.imagen_principal, u.tipo_plan, u.fecha_venc_pro
                FROM inscripcion i
                JOIN usuario u ON u.id_usuario = i.id_usuario
                WHERE i.id_curso = ?
                ORDER BY i.fecha_solicitud";
        $resultado = $this->consultar($sql, 'i', [$idCurso]);

        $inscripciones = [];
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $usuario = new Usuario(
                $fila['id_usuario'], $fila['nombre'], $fila['apellido'], $fila['email'],
                '',                       //contraseña: no se trae
                $fila['fecha_nacimiento'], $fila['telefono'], $fila['antecedentes'],
                $fila['imagen_principal'], $fila['tipo_plan'], $fila['fecha_venc_pro']
            );
            $inscripciones[] = new Inscripcion(
                $fila['id_inscripcion'], $usuario, $fila['fecha_solicitud'],
                $fila['estado'], $fila['fecha_confirmacion']
            );
        }
        return $inscripciones;
    }

    
    public function confirmar(int $idInscripcion, int $idCurso): void
    {
        $sql = "UPDATE inscripcion
                SET estado = 'confirmada', fecha_confirmacion = CURDATE()
                WHERE id_inscripcion = ? AND id_curso = ? AND estado = 'pendiente'";
        $this->consultar($sql, 'ii', [$idInscripcion, $idCurso]);
    }

    
    public function rechazar(int $idInscripcion, int $idCurso): void
    {
        $sql = "DELETE FROM inscripcion
                WHERE id_inscripcion = ? AND id_curso = ? AND estado = 'pendiente'";
        $this->consultar($sql, 'ii', [$idInscripcion, $idCurso]);
    }
}