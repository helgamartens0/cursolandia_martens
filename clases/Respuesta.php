<?php
require_once __DIR__ . '/Usuario.php';

class Respuesta{
    private int $id_respuesta;
    private Usuario $autor;               //quien respondio
    private string $texto;
    private string $fecha_creacion;
    private ?string $fecha_ult_edicion;   //null si nunca la edito
    private bool $aceptada;              

    public function __construct(int $id_respuesta, Usuario $autor, string $texto, string $fecha_creacion,
                                ?string $fecha_ult_edicion, bool $aceptada){
        $this->id_respuesta = $id_respuesta;
        $this->autor = $autor;
        $this->texto = $texto;
        $this->fecha_creacion = $fecha_creacion;
        $this->fecha_ult_edicion = $fecha_ult_edicion;
        $this->aceptada = $aceptada;
    }

    public function getId_respuesta(){
        return $this->id_respuesta;
    }

    public function getAutor(){
        return $this->autor;
    }

    public function getTexto(){
        return $this->texto;
    }

    public function getFecha_creacion(){
        return $this->fecha_creacion;
    }

    public function getFecha_ult_edicion(){
        return $this->fecha_ult_edicion;
    }

    public function estaAceptada(): bool{
        return $this->aceptada;
    }
}