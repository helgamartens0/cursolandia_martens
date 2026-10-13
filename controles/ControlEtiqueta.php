<?php
require_once __DIR__.'/../clases/Etiqueta.php';
require_once __DIR__.'/../clases/Usuario.php';
require_once __DIR__.'/../clases/Curso.php';
require_once __DIR__.'/../traits/AccesoBD.php';

class ControlEtiqueta{
    use AccesoBD;
    
    private const SELECT_ETIQUETA = "SELECT e.id_etiqueta, e.nombre FROM etiqueta e";
    
    //intereses de un usuario -> join con la tabla intermedia
    public function obtenerInteresesDeUsuario(int $idUsuario): array{
        $sql = self::SELECT_ETIQUETA . " 
                JOIN usuario_interes ui 
                ON ui.id_etiqueta = e.id_etiqueta
                WHERE ui.id_usuario = ?
                ORDER BY e.nombre";
        $resultado = $this->consultar($sql,'i',[$idUsuario]);
        
        return $this->armarEtiquetas($resultado);
    }
    
    //etiqueta de un curso -> join con la tabla intermedia
    public function obtenerEtiquetasDeCurso(int $id): array{
        $sql = self::SELECT_ETIQUETA . "
            JOIN curso_etiqueta ce
            ON ce.id_etiqueta = e.id_etiqueta
            WHERE ce.id_curso = ?
            ORDER BY e.nombre";
            
        /*recordatorio:
            $this->consultar(...): es la función del trait AccesoBD. 
            Prepara la consulta, reemplaza el ? y la ejecuta. 
            [$idUsuario]: el valor que va en el ?. Va entre corchetes porque 
            consultar siempre recibe un array, aunque haya un solo valor.*/
        $resultado = $this->consultar($sql,'i',[$id]);
        
        /*$resultado: queda con las filas que devolvió MySQL, 
        pero todavía son "crudas", no objetos. */
        return $this->armarEtiquetas($resultado);
        /*Le pasa esas filas a armarEtiquetas, que las convierte en 
        objetos Etiqueta, y devuelve eso. */
    }
    
    //armado de etiquetas
    private function armarEtiquetas($resultado): array{
        $etiquetas = [];
        while($fila = mysqli_fetch_assoc($resultado)){
            $etiquetas[] = new Etiqueta(
                $fila['id_etiqueta'],
                $fila['nombre']
            );
        }
        return $etiquetas;
    }
    
    //buscar o crear etiqueta
    public function buscarOcrear(string $nombre): Etiqueta{
        $nombre = mb_strtolower(trim($nombre));
        $sql = self::SELECT_ETIQUETA . " WHERE e.nombre = ?";
        $resultado = $this->consultar($sql,'s',[$nombre]);
        $fila = mysqli_fetch_assoc($resultado);
        if($fila!==null){
            return new Etiqueta(
                $fila['id_etiqueta'],
                $fila['nombre']
            );
        }
        
        $sql2 = "INSERT INTO etiqueta (nombre) VALUES (?)";
        $this->consultar($sql2,'s',[$nombre]);
        return new Etiqueta(mysqli_insert_id($this->conexion),$nombre);
    }
    
    //agregar interes (si ya existia una etiqueta con ese nombre no hace nada)
    public function agregarInteres(int $idUsuario, Etiqueta $etiqueta): void{
        $sql = "INSERT IGNORE INTO usuario_interes (id_usuario,id_etiqueta) VALUES (?,?)";
        $this->consultar($sql,'ii',[$idUsuario,$etiqueta->getId_etiqueta()]);
    }
    
    //borrar interes 
    public function eliminarInteres(int $idUsuario, int $idEtiqueta): void{
        $sql = "DELETE FROM usuario_interes WHERE id_usuario = ? AND id_etiqueta = ?";
        $this->consultar($sql,'ii',[$idUsuario,$idEtiqueta]);
    }
}