<?php

class Administrador{
    private int $id_administrador;
    private string $nombre;
    private string $email;
    private string $contrasenia;
    
    /*----------------------------------------*/
    
    public function __construct(int $id_administrador, string $nombre, string $email, string $contrasenia){
        $this->id_administrador = $id_administrador;
        $this->nombre = $nombre;
        $this->email = $email;
        $this->contrasenia = $contrasenia;
    }
    
    /*----------------------------------------*/
    
    public function getId_administrador(){
        return $this->id_administrador;
    }
    
    public function getNombre(){
        return $this->nombre;
    }
    
    public function getEmail(){
        return $this->email;
    }
    
    /*----------------------------------------*/
    //como la contraseña nunca sale de la clase, la verificamos con password_verify
    //recordatorio: password_verify sirve para comprobar si una contraseña ingresada por el usuario 
    //coinicide con una q fue guardada mediante un hash 
    public function verificarContrasenia(string $clave): bool{
        return password_verify($clave,$this->contrasenia);
    }
        
}