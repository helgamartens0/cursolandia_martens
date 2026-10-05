<?php
    class Curso{
        private $id;
        private $titulo;
        private $descripcion;
        private $costo;
        private $cupo;
        private $fecha_inicio;
        private $fecha_fin;
        private $tipo_acceso;
        private $id_creador;
        
        /*----------------------------------------*/
        
        public function __construct($id,$titulo,$descripcion,$costo,$cupo,$fecha_inicio,$fecha_fin,$tipo_acceso,$id_creador){
            $this->id = $id;
            $this->titulo = $titulo;
            $this->descripcion = $descripcion;
            $this->costo = $costo;
            $this->cupo = $cupo;
            $this->fecha_inicio = $fecha_inicio;
            $this->fecha_fin = $fecha_fin;
            $this->tipo_acceso = $tipo_acceso;
            $this->id_creador = $id_creador;
        }
        
        /*----------------------------------------*/
        
        public function getId(){
            return $this->id;
        }
        
        public function getTitulo(){
            return $this->titulo;
        }
        
        public function getDescripcion(){
            return $this->descripcion;
        }

        public function getCosto(){
            return $this->costo;
        }

        public function getCupo(){
            return $this->cupo;
        }

        public function getFechaInicio(){
            return $this->fecha_inicio;
        }

        public function getFechaFin(){
            return $this->fecha_fin;
        }

        public function getTipoAcceso(){
            return $this->tipo_acceso;
        }

        public function getIdCreador(){
            return $this->id_creador;
        }
        
        /*----------------------------------------*/
        public function esGratuito(){
            return $this->costo === null;
        }
        
        public function esPublico(){
            return $this->tipo_acceso === 'publico';
        }
        
        public function estaActivo(){
            $hoy = date('Y-m-d');
            return $this->fecha_inicio <= $hoy && $hoy <= $this->fecha_fin;
        }
    }
