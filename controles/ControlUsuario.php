<?php

require_once __DIR__.'/../clases/Usuario.php';
require_once __DIR__.'/../traits/AccesoBD.php';

class ControlUsuario{
    use AccesoBD; // trae $conexion, el constructor y consultar()

    public function buscarPorEmail(string $email): ?Usuario{
        $sql = "SELECT id_usuario, nombre, apellido, email, contrasenia, fecha_nacimiento,
                       telefono, antecedentes, imagen_principal, tipo_plan, fecha_venc_pro
                FROM usuario
                WHERE email = ?";
                
        $resultado = $this->consultar($sql,'s',[$email]);
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
    
    /*existencia de usuario con ese DNI (el dni es el id) */
    public function existe(int $dni): bool{
        $resultado = $this->consultar("SELECT 1 FROM usuario WHERE id_usuario = ?", 'i', [$dni]);
        return mysqli_fetch_assoc($resultado) !== null;
    }
    
    //dar de alta (la contraseña pasa encriptada, plan e imagen toman valor x defecto (se editan desde el perfil))
    public function registrar(int $dni, string $nombre, string $apellido, string $email, string $clave, string $fechaNacimiento): void{
        $hash = password_hash($clave, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO usuario (id_usuario,nombre,apellido,email,contrasenia,fecha_nacimiento)
                VALUES (?,?,?,?,?,?)";
        
        $this->consultar($sql, 'isssss', [$dni, $nombre, $apellido, $email, $hash, $fechaNacimiento]);
    }
    
    
}