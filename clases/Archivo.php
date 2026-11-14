<?php
class Archivo{
    public const MAX_MB = 5;   //cada archivo hasta 5 MB 

    private int $id_archivo;
    private string $nombre;       
    private float $tamanio_mb;
    private string $fecha_carga;
    private string $url;          

    public function __construct(int $id_archivo, string $nombre, float $tamanio_mb, string $fecha_carga, string $url){
        $this->id_archivo = $id_archivo;
        $this->nombre = $nombre;
        $this->tamanio_mb = $tamanio_mb;
        $this->fecha_carga = $fecha_carga;
        $this->url = $url;
    }

    public function getId_archivo(){
        return $this->id_archivo;
    }

    public function getNombre(){
        return $this->nombre;
    }

    public function getTamanio_mb(){
        return $this->tamanio_mb;
    }

    public function getFecha_carga(){
        return $this->fecha_carga;
    }

    public function getUrl(){
        return $this->url;
    }

    //true si pesa mas de lo q deja el plan gratuito
    public function superaLimite(): bool{
        return $this->tamanio_mb > self::MAX_MB;
    }

    //le manda el archivo al navegador para que se descargue con su nombre original
    public function descargar(): void{
        $ruta = __DIR__ . '/../' . $this->url;
        header('Content-Type: ' . mime_content_type($ruta));
        header('Content-Length: ' . filesize($ruta));
        header('Content-Disposition: attachment; filename="' . $this->nombre . '"');
        readfile($ruta);
    }
}