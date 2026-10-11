<?php 
    class Etiqueta{
        private  $id_etiqueta;
        private $nombre;
        
        /*----------------------------------------*/
        
        public function __construct($id_etiqueta,$nombre){
            $this->id_etiqueta = $id_etiqueta;
            $this->nombre = $nombre;
        }
        
        /*----------------------------------------*/
        
        public function getId_etiqueta(){
            return $this->id_etiqueta;
        }
        
        public function getNombre(){
            return $this->nombre;
        }
        
        
        
        
    }