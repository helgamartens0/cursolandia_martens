<?php
require_once __DIR__ . '/../traits/AccesoBD.php';
require_once __DIR__ . '/../clases/Respuesta.php';

class ControlRespuesta
{
    use AccesoBD;

    //las respuestas de una pregunta, de la primera a la ultima
    public function obtenerDePregunta(int $idPregunta): array
    {
        $sql = "SELECT r.id_respuesta, r.texto, r.fecha_creacion, r.fecha_ult_edicion, r.aceptada,
                       u.id_usuario, u.nombre, u.apellido, u.email, u.fecha_nacimiento,
                       u.telefono, u.antecedentes, u.imagen_principal, u.tipo_plan, u.fecha_venc_pro
                FROM respuesta r
                JOIN usuario u ON u.id_usuario = r.id_autor
                WHERE r.id_pregunta = ?
                ORDER BY r.fecha_creacion, r.id_respuesta";
        $resultado = $this->consultar($sql, 'i', [$idPregunta]);

        $respuestas = [];
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $autor = new Usuario(
                $fila['id_usuario'],
                $fila['nombre'],
                $fila['apellido'],
                $fila['email'],
                '',                       //contraseña: no se trae
                $fila['fecha_nacimiento'],
                $fila['telefono'],
                $fila['antecedentes'],
                $fila['imagen_principal'],
                $fila['tipo_plan'],
                $fila['fecha_venc_pro']
            );
            $respuestas[] = new Respuesta(
                $fila['id_respuesta'],
                $autor,
                $fila['texto'],
                $fila['fecha_creacion'],
                $fila['fecha_ult_edicion'],
                $fila['aceptada']
            );
        }
        return $respuestas;
    }

    //respuesta nueva
    public function crear(int $idPregunta, int $idAutor, string $texto): void
    {
        $sql = "INSERT INTO respuesta (id_pregunta, id_autor, texto) VALUES (?, ?, ?)";
        $this->consultar($sql, 'iis', [$idPregunta, $idAutor, $texto]);
    }

    //edita la ultima preg de el mismo y anota cuando se edito 
    public function editar(int $idRespuesta, int $idAutor, string $texto): void
    {
        $sql = "UPDATE respuesta
                SET texto = ?, fecha_ult_edicion = NOW()
                WHERE id_respuesta = ? AND id_autor = ?";
        $this->consultar($sql, 'sii', [$texto, $idRespuesta, $idAutor]);
    }
}