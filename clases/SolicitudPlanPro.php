<?php
require_once __DIR__.'/Usuario.php';

class SolicitudPlanPro{
    private int $id_solicitud;
    private Usuario $usuario; //quien envia la solicitud
    private string $fecha_envio; //'AAAA-MM-DD'
    private string $mensaje;
    private string $comprobante; //ruta del archivo subido
    private string $estado; //pendiente - aprobada - denegada
    private ?string $fecha_venc_asignada; //solo si esta aprobada  ? xq puede quedar vacio
    private ?string $motivo_rechazo; //solo si esta denegada       ? xq puede quedar vacio
     
    /*----------------------------------------*/
    
    public function __construct(int $id_solicitud, Usuario $usuario, string $fecha_envio, string $mensaje,
                            string $comprobante, string $estado, ?string $fecha_venc_asignada, ?string $motivo_rechazo){
        $this->id_solicitud = $id_solicitud;
        $this->usuario = $usuario;
        $this->fecha_envio = $fecha_envio;
        $this->mensaje = $mensaje;
        $this->comprobante = $comprobante;
        $this->estado = $estado;
        $this->fecha_venc_asignada= $fecha_venc_asignada;
        $this->motivo_rechazo = $motivo_rechazo;
    }
    
    /*----------------------------------------*/
    
    public function getId_solicitud(){
        return $this->id_solicitud;
    }
    
    public function getUsuario(){
        return $this->usuario;
    }
    
    public function getFecha_envio(){
        return $this->fecha_envio;
    }
    
    public function getMensaje(){
        return $this->mensaje;
    }
    
    public function getComprobante(){
        return $this->comprobante;
    }
    public function getEstado(){
        return $this->estado;
    }
    
    public function getFecha_venc_asignada(){
        return $this->fecha_venc_asignada;
    }
    
    public function getMotivo_rechazo(){
        return $this->motivo_rechazo;
    }
        
    /*----------------------------------------*/
    
    public function estaPendiente(): bool{
        return $this->estado === 'pendiente';
    }
    
    public function aprobar(string $fechaVenc): void{
        $this->estado = 'aprobada';
        $this->fecha_venc_asignada = $fechaVenc;
        $this->motivo_rechazo = null;
    }
    
    public function denegar(string $motivo): void{
        $this->estado = 'denegada';
        $this->fecha_venc_asignada = null;
        $this->motivo_rechazo = $motivo;
    }
    
    
}