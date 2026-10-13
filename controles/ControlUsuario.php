<?php

require_once __DIR__.'/../clases/Usuario.php';
require_once __DIR__.'/../traits/AccesoBD.php';

class ControlUsuario{
    use AccesoBD; // trae $conexion, el constructor y consultar()

    public function buscarPorEmail(string $email): ?Usuario{
        $sql = "SELECT id_usuario, nombre, apellido, email, contrasenia, fecha_nacimiento,
                       telefono, antecedentes, imagen_principal, tipo_plan, fecha_venc_pro
                FROM usuario
                WHERE email = ?";
                
        $resultado = $this->consultar($sql,'s',[$email]);
        return $this->armarUsuario(mysqli_fetch_assoc($resultado));
    }
    
    
    public function buscarPorId(int $id): ?Usuario{
        $sql = "SELECT id_usuario, nombre, apellido, email, contrasenia, fecha_nacimiento,
                       telefono, antecedentes, imagen_principal, tipo_plan, fecha_venc_pro
                FROM usuario
                WHERE id_usuario = ?";
        $resultado = $this->consultar($sql,'i',[$id]);
        return $this->armarUsuario(mysqli_fetch_assoc($resultado));
    }
    
    
    /*existencia de usuario con ese DNI (el dni es el id) */
    public function existe(int $dni): bool{
        $resultado = $this->consultar("SELECT 1 FROM usuario WHERE id_usuario = ?", 'i', [$dni]);
        return mysqli_fetch_assoc($resultado) !== null;
    }
    
    //dar de alta (la contraseña pasa encriptada, plan e imagen toman valor x defecto (se editan desde el perfil))
    public function registrar(int $dni, string $nombre, string $apellido, string $email, string $clave, string $fechaNacimiento): void{
        $hash = password_hash($clave, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO usuario (id_usuario,nombre,apellido,email,contrasenia,fecha_nacimiento)
                VALUES (?,?,?,?,?,?)";
        
        $this->consultar($sql, 'isssss', [$dni, $nombre, $apellido, $email, $hash, $fechaNacimiento]);
    }
    
    //arma el objeto Usuario con una fila de la consulta (o null si no vino ninguna)
    private function armarUsuario(?array $fila): ?Usuario{
        if($fila === null){
            return null;
        }

        return new Usuario(
            $fila['id_usuario'],
            $fila['nombre'],
            $fila['apellido'],
            $fila['email'],
            $fila['contrasenia'],
            $fila['fecha_nacimiento'],
            $fila['telefono'],
            $fila['antecedentes'],
            $fila['imagen_principal'],
            $fila['tipo_plan'],
            $fila['fecha_venc_pro']
        );
    }
    
    //para la parte de "MI ACTIVIDAD" (cursos activos CREADOS, cursos activos INSCRIPTO)
    public function contarCursosCreados(int $id): int{
        $sql = "SELECT COUNT(*) AS cantidad
                FROM curso
                WHERE id_creador = ? AND fecha_fin >= CURDATE()";
        
        $fila = mysqli_fetch_assoc($this->consultar($sql,'i',[$id]));
        
        return (int) $fila['cantidad'];
    }
    
    public function contarInscripcionesActivas(int $id): int{
        $sql = "SELECT COUNT(*) AS cantidad
                FROM inscripcion i
                JOIN curso c ON c.id_curso = i.id_curso
                WHERE i.id_usuario = ? AND c.fecha_fin >= CURDATE()";
        
        $fila = mysqli_fetch_assoc($this->consultar($sql,'i',[$id]));
        return (int) $fila['cantidad'];
    }
    
    public function actualizarPerfil(int $id, string $email, string $fechaNacimiento, ?string $telefono, ?string $antecedentes): void{
        $sql = "UPDATE usuario
            SET email = ?, fecha_nacimiento = ?, telefono = ?, antecedentes = ?
            WHERE id_usuario = ?";
        $this->consultar($sql,'ssssi',[$email,$fechaNacimiento,$telefono,$antecedentes,$id]);
    }
    
    
}
