<?php
require_once __DIR__ . '/../traits/AccesoBD.php';
require_once __DIR__ . '/../clases/Archivo.php';

class ControlArchivo
{
    use AccesoBD;

    //material de un curso
    public function obtenerDeCurso(int $idCurso): array
    {
        $sql = "SELECT id_archivo, nombre, tamanio_mb, fecha_carga, url
                FROM archivo
                WHERE id_curso = ?
                ORDER BY id_archivo";
        $resultado = $this->consultar($sql, 'i', [$idCurso]);

        $archivos = [];
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $archivos[] = new Archivo(
                $fila['id_archivo'],
                $fila['nombre'],
                $fila['tamanio_mb'],
                $fila['fecha_carga'],
                $fila['url']
            );
        }
        return $archivos;
    }

    //busca un archivo de un curso, si no lo encuentra devuelve null
    public function buscarDeCurso(int $idArchivo, int $idCurso): ?Archivo
    {
        $sql = "SELECT id_archivo, nombre, tamanio_mb, fecha_carga, url
                FROM archivo
                WHERE id_archivo = ? AND id_curso = ?";
        $fila = mysqli_fetch_assoc($this->consultar($sql, 'ii', [$idArchivo, $idCurso]));

        if ($fila === null) {
            return null;
        }
        return new Archivo($fila['id_archivo'], $fila['nombre'], $fila['tamanio_mb'], $fila['fecha_carga'], $fila['url']);
    }

    //guarda el archivo nuevo (la fecha la pone la base)
    public function crear(int $idCurso, string $nombre, float $tamanioMB, string $url): void
    {
        $sql = "INSERT INTO archivo (id_curso, nombre, tamanio_mb, url) VALUES (?, ?, ?, ?)";
        $this->consultar($sql, 'isds', [$idCurso, $nombre, $tamanioMB, $url]);
    }

    public function eliminar(int $idArchivo, int $idCurso): void
    {
        $sql = "DELETE FROM archivo WHERE id_archivo = ? AND id_curso = ?";
        $this->consultar($sql, 'ii', [$idArchivo, $idCurso]);
    }
}