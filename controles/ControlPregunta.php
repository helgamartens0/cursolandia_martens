<?php
require_once __DIR__ . '/../traits/AccesoBD.php';
require_once __DIR__ . '/../clases/Pregunta.php';

class ControlPregunta
{
    use AccesoBD;

    private const SELECT_PREGUNTA = "
        SELECT p.id_pregunta, p.texto, p.fecha_creacion, p.destacada, p.cerrada,
               u.id_usuario, u.nombre, u.apellido, u.email, u.fecha_nacimiento,
               u.telefono, u.antecedentes, u.imagen_principal, u.tipo_plan, u.fecha_venc_pro
        FROM pregunta p
        JOIN usuario u ON u.id_usuario = p.id_autor";

    //primero las destacadas y despues las mas nuevas
    public function obtenerDeCurso(int $idCurso): array
    {
        $sql = self::SELECT_PREGUNTA . "
                WHERE p.id_curso = ?
                ORDER BY p.destacada DESC, p.fecha_creacion DESC";
        $resultado = $this->consultar($sql, 'i', [$idCurso]);
        return $this->armarPreguntas($resultado);
    }

    //busca una pregunta pero solo si es de ese curso (igual q con el material)
    public function buscarDeCurso(int $idPregunta, int $idCurso): ?Pregunta
    {
        $sql = self::SELECT_PREGUNTA . " WHERE p.id_pregunta = ? AND p.id_curso = ?";
        $resultado = $this->consultar($sql, 'ii', [$idPregunta, $idCurso]);
        $preguntas = $this->armarPreguntas($resultado);

        if (count($preguntas) === 0) {
            return null;
        }
        return $preguntas[0];
    }

    //por cada fila arma el autor y despues la pregunta
    private function armarPreguntas($resultado): array
    {
        $preguntas = [];
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
            $preguntas[] = new Pregunta(
                $fila['id_pregunta'],
                $autor,
                $fila['texto'],
                $fila['fecha_creacion'],
                $fila['destacada'],
                $fila['cerrada']
            );
        }
        return $preguntas;
    }

    //guarda la pregunta nueva (abierta y sin destacar) y devuelve su id
    public function crear(int $idCurso, int $idAutor, string $texto): int
    {
        $sql = "INSERT INTO pregunta (id_curso, id_autor, texto) VALUES (?, ?, ?)";
        $this->consultar($sql, 'iis', [$idCurso, $idAutor, $texto]);
        return mysqli_insert_id($this->conexion);
    }

    //cuantas respuestas tiene, para mostrar en la lista del foro
    public function contarRespuestas(int $idPregunta): int
    {
        $sql = "SELECT COUNT(*) AS cantidad FROM respuesta WHERE id_pregunta = ?";
        $fila = mysqli_fetch_assoc($this->consultar($sql, 'i', [$idPregunta]));
        return (int) $fila['cantidad'];
    }
    
    // marca la respuesta como aceptada y cierra la pregunta
    public function aceptarRespuesta(int $idPregunta, int $idRespuesta): void
    {
        $sql = "UPDATE respuesta SET aceptada = TRUE WHERE id_respuesta = ? AND id_pregunta = ?";
        $this->consultar($sql, 'ii', [$idRespuesta, $idPregunta]);

        $sql = "UPDATE pregunta SET cerrada = TRUE WHERE id_pregunta = ?";
        $this->consultar($sql, 'i', [$idPregunta]);
    }

    //la pregunta pasa a mostrarse primero en el foro
    public function destacar(int $idPregunta): void
    {
        $sql = "UPDATE pregunta SET destacada = TRUE WHERE id_pregunta = ?";
        $this->consultar($sql, 'i', [$idPregunta]);
    }

    //borra la pregunta (sus respuestas se borran solas x el ON DELETE CASCADE)
    public function eliminar(int $idPregunta): void
    {
        $sql = "DELETE FROM pregunta WHERE id_pregunta = ?";
        $this->consultar($sql, 'i', [$idPregunta]);
    }
}