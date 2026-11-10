<?php

require_once __DIR__.'/../clases/Administrador.php';
require_once __DIR__.'/../traits/AccesoBD.php';

class ControlAdministrador{
    use AccesoBD;
    
    public function buscarPorEmail(string $email): ?Administrador{
        $sql = "SELECT id_administrador, nombre, email, contrasenia
                FROM administrador
                WHERE email = ?";
        $fila = mysqli_fetch_assoc($this->consultar($sql,'s',[$email]));
        if($fila === null){
            return null;
        }
        return new Administrador($fila['id_administrador'],$fila['nombre'],$fila['email'],$fila['contrasenia']);
    }
}