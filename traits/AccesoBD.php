<?php
trait AccesoBD{
    private $conexion;

    public function __construct($conexion){
        $this->conexion = $conexion;
    }

    private function consultar(string $sql, string $tipos='', array $param=[]){
        $sentencia = mysqli_prepare($this->conexion,$sql);
        if($tipos!==''){
            mysqli_stmt_bind_param($sentencia,$tipos,...$param);
        }
        mysqli_stmt_execute($sentencia);
        return mysqli_stmt_get_result($sentencia);
    }
}
