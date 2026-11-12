<?php
require_once __DIR__ . '/../traits/AccesoBD.php';

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

    //guarda la inscripcion. 
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
}