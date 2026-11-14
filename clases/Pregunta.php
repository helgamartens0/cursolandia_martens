<?php
require_once __DIR__ . '/Usuario.php';
require_once __DIR__ . '/Respuesta.php';

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
    
    //guarda en la pregunta una de sus respuestas (en orden, de la primera a la ultima)
    public function agregarRespuesta(Respuesta $r): void{
        $this->respuestas[] = $r;
    }

    public function getRespuestas(): array{
        return $this->respuestas;
    }

    //la ultima respuesta que se escribio (null si todavia no hay ninguna)
    public function ultimaRespuesta(): ?Respuesta{
        if (count($this->respuestas) === 0) {
            return null;
        }
        return $this->respuestas[count($this->respuestas) - 1];
    }

    //quien participo ultimo. si nadie respondio todavia, es el que hizo la pregunta
    public function autorUltimaRespuesta(): Usuario{
        $ultima = $this->ultimaRespuesta();
        if ($ultima === null) {
            return $this->autor;
        }
        return $ultima->getAutor();
    }
}