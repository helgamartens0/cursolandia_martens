<?php 
    class Usuario{
        private $id_usuario;
        private $nombre;
        private $apellido;
        private $email;
        private $contrasenia;
        private $fecha_nacimiento;
        private $telefono;
        private $antecedentes;
        private $imagen_principal;
        private $tipo_plan;
        private $fecha_venc_pro;
        
        /*----------------------------------------*/
        
        public function __construct($id_usuario,$nombre,$apellido,$email,$contrasenia,$fecha_nacimiento,$telefono,$antecedentes,$imagen_principal,$tipo_plan,$fecha_venc_pro){
            $this->id_usuario = $id_usuario;
            $this->nombre = $nombre;
            $this->apellido = $apellido;
            $this->email = $email;
            $this->contrasenia = $contrasenia;
            $this->fecha_nacimiento = $fecha_nacimiento;
            $this->telefono = $telefono;
            $this->antecedentes = $antecedentes;
            $this->imagen_principal = $imagen_principal;
            $this->tipo_plan = $tipo_plan;
            $this->fecha_venc_pro = $fecha_venc_pro;
        }
   
        /*----------------------------------------*/
   
        public function getId_usuario(){
            return $this->id_usuario;
        }
        
        public function getNombre(){
            return $this->nombre;
        }
        
        public function getApellido(){
            return $this->apellido;
        }
        
        public function getEmail(){
            return $this->email;
        }
        
        public function getFecha_nacimiento(){
            return $this->fecha_nacimiento;
        }
        
        public function getTelefono(){
            return $this->telefono;
        }
        
        public function getAntecedentes(){
            return $this->antecedentes;
        }
        
        public function getImagen_principal(){
            return $this->imagen_principal;
        }
        
        public function getTipo_plan(){
            return $this->tipo_plan;
        }
        
        public function getFecha_venc_pro(){
            return $this->fecha_venc_pro;
        }
        
        /*----------------------------------------*/
        
        /*encapsulamiento para verificar contraseña sin q salga de aca, x eso no la pongo con el getter */
        public function verificarContrasenia($clave){
            return password_verify($clave,$this->contrasenia);
        }
        
        public function esPro(){
            if($this->tipo_plan === 'pro' && date('Y-m-d')<=$this->fecha_venc_pro){
                return true;
            }
            return false;
        }
        
        //esta es para el avatar arriba con las iniciales
        public function getIniciales(): string{
            $primera = mb_strtoupper(mb_substr($this->nombre,0,1));
            $ultima = mb_strtoupper(mb_substr($this->apellido,0,1));
            return $primera.$ultima;
        }
    }