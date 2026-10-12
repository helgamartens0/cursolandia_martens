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
}