<?php
require_once __DIR__ . '/Usuario.php';

class Pregunta{
    private int $id_pregunta;
    private Usuario $autor;          
    private string $texto;
    private string $fecha_creacion;
    private bool $destacada;         
    private bool $cerrada;           
    private array $respuestas = [];

    public function __construct(int $id_pregunta, Usuario $autor, string $texto, string $fecha_creacion,
                                bool $destacada, bool $cerrada){
        $this->id_pregunta = $id_pregunta;
        $this->autor = $autor;
        $this->texto = $texto;
        $this->fecha_creacion = $fecha_creacion;
        $this->destacada = $destacada;
        $this->cerrada = $cerrada;
    }

    public function getId_pregunta(){
        return $this->id_pregunta;
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

    public function estaDestacada(): bool{
        return $this->destacada;
    }

    public function estaCerrada(): bool{
        return $this->cerrada;
    }
}