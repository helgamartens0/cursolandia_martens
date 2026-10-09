<?php

require_once __DIR__ . '/../clases/Curso.php';
require_once __DIR__ . '/../clases/Usuario.php';
require_once __DIR__ . '/../traits/AccesoBD.php';

class ControlCurso
{
    use AccesoBD;

    private const SELECT_CURSO = "
        SELECT c.id_curso, c.titulo, c.descripcion, c.costo, c.cupo,
               c.fecha_inicio, c.fecha_fin, c.tipo_acceso,
               u.id_usuario, u.nombre, u.apellido, u.email, u.fecha_nacimiento,
               u.telefono, u.antecedentes, u.imagen_principal, u.tipo_plan, u.fecha_venc_pro
        FROM curso c
        JOIN usuario u ON u.id_usuario = c.id_creador";

  

    // Todos los cursos
    public function obtenerTodos()
    {
        $sql = self::SELECT_CURSO . " ORDER BY c.fecha_inicio";
        $resultado = $this->consultar($sql);
        return $this->armarCursos($resultado);
    }

    // Cursos de creadores con plan Pro vigente que todavía no terminaron
    public function obtenerDestacados()
    {
        $sql = self::SELECT_CURSO . "
                WHERE u.tipo_plan = 'pro' AND u.fecha_venc_pro >= CURDATE()
                  AND c.fecha_fin >= CURDATE()
                ORDER BY c.fecha_inicio";
        $resultado = $this->consultar($sql);
        return $this->armarCursos($resultado);
    }

    // Cursos activos (hoy entre inicio y fin) en los que el usuario tiene la inscripción confirmada
    public function obtenerActivosDeInscripto(int $idUsuario)
    {
        $sql = self::SELECT_CURSO . "
                JOIN inscripcion i ON i.id_curso = c.id_curso
                WHERE i.id_usuario = ?
                  AND i.estado = 'confirmada'
                  AND CURDATE() BETWEEN c.fecha_inicio AND c.fecha_fin
                ORDER BY c.fecha_fin";
       
        $resultado = $this->consultar($sql,'i',[$idUsuario]);
        return $this->armarCursos($resultado);
    }

    // Por cada fila: primero arma el Usuario creador y después el Curso, pasándole su creador.
    private function armarCursos($resultado)
    {
        $cursos = [];
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $creador = new Usuario(
                $fila['id_usuario'],
                $fila['nombre'],
                $fila['apellido'],
                $fila['email'],
                '',                       // contraseña: no se trae 
                $fila['fecha_nacimiento'],
                $fila['telefono'],
                $fila['antecedentes'],
                $fila['imagen_principal'],
                $fila['tipo_plan'],
                $fila['fecha_venc_pro']
            );

            $cursos[] = new Curso(
                $fila['id_curso'],
                $fila['titulo'],
                $fila['descripcion'],
                $fila['costo'],
                $fila['cupo'],
                $fila['fecha_inicio'],
                $fila['fecha_fin'],
                $fila['tipo_acceso'],
                $creador
            );
        }
        return $cursos;
    }
}