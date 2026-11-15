<?php
require_once __DIR__ . '/../traits/AccesoBD.php';
require_once __DIR__ . '/../clases/Notificacion.php';

class ControlNotificacion
{
    use AccesoBD;

    //le manda la notificacion a cada usuario de la lista, menos al que hizo la accion
    public function enviar(array $usuarios, int $idCurso, ?int $idPregunta, string $tipo, string $mensaje, int $idQuienLaHizo): void
    {
        $sql = "INSERT INTO notificacion (id_usuario, id_curso, id_pregunta, tipo, mensaje) VALUES (?, ?, ?, ?, ?)";
        foreach ($usuarios as $usuario) {
            if ($usuario->getId_usuario() != $idQuienLaHizo) {
                $this->consultar($sql, 'iiiss', [$usuario->getId_usuario(), $idCurso, $idPregunta, $tipo, $mensaje]);
            }
        }
    }

    //corta el texto de la pregunta para que entre en el mensaje
    public function resumir(string $texto): string
    {
        if (mb_strlen($texto) > 60) {
            return mb_substr($texto, 0, 60) . '...';
        }
        return $texto;
    }

    //las notificaciones del usuario, las mas nuevas primero
    public function obtenerDeUsuario(int $idUsuario): array
    {
        $sql = "SELECT id_notificacion, tipo, mensaje, fecha, leida, id_curso, id_pregunta
                FROM notificacion
                WHERE id_usuario = ?
                ORDER BY fecha DESC, id_notificacion DESC";
        $resultado = $this->consultar($sql, 'i', [$idUsuario]);

        $notificaciones = [];
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $notificaciones[] = new Notificacion(
                $fila['id_notificacion'],
                $fila['tipo'],
                $fila['mensaje'],
                $fila['fecha'],
                $fila['leida'],
                $fila['id_curso'],
                $fila['id_pregunta']
            );
        }
        return $notificaciones;
    }

    //busca una notificacion pero solo si es de ese usuario
    public function buscarDeUsuario(int $idNotificacion, int $idUsuario): ?Notificacion
    {
        $sql = "SELECT id_notificacion, tipo, mensaje, fecha, leida, id_curso, id_pregunta
                FROM notificacion
                WHERE id_notificacion = ? AND id_usuario = ?";
        $fila = mysqli_fetch_assoc($this->consultar($sql, 'ii', [$idNotificacion, $idUsuario]));

        if ($fila === null) {
            return null;
        }
        return new Notificacion($fila['id_notificacion'], $fila['tipo'], $fila['mensaje'], $fila['fecha'],
                                $fila['leida'], $fila['id_curso'], $fila['id_pregunta']);
    }

    //para el numerito de la campanita en la navbar
    public function contarNoLeidas(int $idUsuario): int
    {
        $sql = "SELECT COUNT(*) AS cantidad FROM notificacion WHERE id_usuario = ? AND leida = FALSE";
        $fila = mysqli_fetch_assoc($this->consultar($sql, 'i', [$idUsuario]));
        return (int) $fila['cantidad'];
    }

    public function marcarLeida(int $idNotificacion, int $idUsuario): void
    {
        $sql = "UPDATE notificacion SET leida = TRUE WHERE id_notificacion = ? AND id_usuario = ?";
        $this->consultar($sql, 'ii', [$idNotificacion, $idUsuario]);
    }
}