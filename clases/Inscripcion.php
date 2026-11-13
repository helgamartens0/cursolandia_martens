<?php
require_once __DIR__ . '/Usuario.php';

class Inscripcion{
    private int $id_inscripcion;
    private Usuario $usuario;              
    private string $fecha_solicitud;
    private string $estado;                //pendiente - confirmada
    private ?string $fecha_confirmacion;   //solo si esta confirmada
//-------------------------------------------------------------------------
    public function __construct(int $id_inscripcion, Usuario $usuario, string $fecha_solicitud,
                                string $estado, ?string $fecha_confirmacion){
        $this->id_inscripcion = $id_inscripcion;
        $this->usuario = $usuario;
        $this->fecha_solicitud = $fecha_solicitud;
        $this->estado = $estado;
        $this->fecha_confirmacion = $fecha_confirmacion;
    }
//---------------------------------------------------------------------------
    public function getId_inscripcion(){ 
        return $this->id_inscripcion; 
    }
    
    public function getUsuario(){
         return $this->usuario; 
    }
    
    public function getFecha_solicitud(){ 
        return $this->fecha_solicitud; 
    }
    
    public function getEstado(){ 
        return $this->estado; 
    }
    
    public function getFecha_confirmacion(){ 
        return $this->fecha_confirmacion; 
    }
//-----------------------------------------------------------------
    public function estaPendiente(): bool{
        return $this->estado === 'pendiente';
    }

    public function estaConfirmada(): bool{
        return $this->estado === 'confirmada';
    }
}