<?php
class Notificacion{
    private int $id_notificacion;
    private string $tipo;            //preguntaCreada, respuestaNueva, preguntaCerrada o preguntaEliminada
    private string $mensaje;         //el texto que se muestra, ya armado
    private string $fecha;
    private bool $leida;
    private int $id_curso;           //de que curso es
    private ?int $id_pregunta;       //null si la pregunta se elimino

    public function __construct(int $id_notificacion, string $tipo, string $mensaje, string $fecha,
                                bool $leida, int $id_curso, ?int $id_pregunta){
        $this->id_notificacion = $id_notificacion;
        $this->tipo = $tipo;
        $this->mensaje = $mensaje;
        $this->fecha = $fecha;
        $this->leida = $leida;
        $this->id_curso = $id_curso;
        $this->id_pregunta = $id_pregunta;
    }

    public function getId_notificacion(){
        return $this->id_notificacion;
    }

    public function getTipo(){
        return $this->tipo;
    }

    public function getMensaje(){
        return $this->mensaje;
    }

    public function getFecha(){
        return $this->fecha;
    }

    public function getId_curso(){
        return $this->id_curso;
    }

    public function getId_pregunta(){
        return $this->id_pregunta;
    }

    public function estaLeida(): bool{
        return $this->leida;
    }
}