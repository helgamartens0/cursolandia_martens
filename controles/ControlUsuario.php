<?php 

require_once __DIR__.'/../clases/Usuario.php';

class ControlUsuario{
    private $conexion;
    
    public function __construct($conexion){
        $this->conexion = $conexion;
    }

    public function buscarPorEmail(string $email): ?Usuario{
        $sql = "SELECT id_usuario, nombre, apellido, email, contrasenia, fecha_nacimiento,
                       telefono, antecedentes, imagen_principal, tipo_plan, fecha_venc_pro
                FROM usuario
                WHERE email = ?";
                
        $sentencia = mysqli_prepare($this->conexion,$sql);
        mysqli_stmt_bind_param($sentencia,'s',$email);
        mysqli_stmt_execute($sentencia);
        $resultado = mysqli_stmt_get_result($sentencia);
        $fila = mysqli_fetch_assoc($resultado);
        
        if($fila === null){
            return null;
        }
        
        return new Usuario(
            $fila['id_usuario'],
            $fila['nombre'],
            $fila['apellido'],
            $fila['email'],
            $fila['contrasenia'],
            $fila['fecha_nacimiento'],
            $fila['telefono'],
            $fila['antecedentes'],
            $fila['imagen_principal'],
            $fila['tipo_plan'],
            $fila['fecha_venc_pro']
        );
    }
}