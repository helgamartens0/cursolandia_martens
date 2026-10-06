<?php 

require_once __DIR__.'/../clases/Curso.php';

class ControlCurso{
    private $conexion;
    
    public function __construct($conexion){
        $this->conexion = $conexion;
    }
    
    public function obtenerTodos(){
        $sql = "SELECT id_curso, titulo, descripcion, costo, cupo,
                       fecha_inicio, fecha_fin, tipo_acceso, id_creador
                FROM curso
                ORDER BY fecha_inicio";
                
        $resultado = mysqli_query($this->conexion,$sql);
        
        $cursos=[];
        
        while($fila = mysqli_fetch_assoc($resultado)){
            $cursos[] = new Curso(
                $fila['id_curso'],
                $fila['titulo'],
                $fila['descripcion'],
                $fila['costo'],
                $fila['cupo'],
                $fila['fecha_inicio'],
                $fila['fecha_fin'],
                $fila['tipo_acceso'],
                $fila['id_creador']
            ); /*por cada fila se crea un objeto curso y se agrega a la lista de cursos */
        }
        return $cursos;
    }
}