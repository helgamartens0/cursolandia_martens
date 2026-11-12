<?php
/*

Guardar una solicitud nueva
Saber si el usuario ya tiene una pendiente, para no dejarlo mandar otra
Traer su última solicitud, para mostrar en el perfil “pendiente” o “rechazada, motivo: ...”

*/
require_once __DIR__.'/../clases/SolicitudPlanPro.php';
require_once __DIR__.'/../traits/AccesoBD.php';

class ControlSolicitud{
    
    use AccesoBD;
    
    //trae las solicitudes y datos de quien la mando
    private const SELECT_SOLICITUD = "
        SELECT s.id_solicitud, s.fecha_envio, s.mensaje, s.comprobante, s.estado,
            s.fecha_venc_asignada, s.motivo_rechazo,
            u.id_usuario, u.nombre, u.apellido, u.email, u.fecha_nacimiento,
            u.telefono, u.antecedentes, u.imagen_principal, u.tipo_plan, u.fecha_venc_pro
        FROM solicitud_plan_pro s
        JOIN usuario u ON u.id_usuario = s.id_usuario";

    private function armarSolicitudes($resultado): array{
        $solicitudes = [];
        while($fila = mysqli_fetch_assoc($resultado)){
            
            $usuario = new Usuario(
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
            $solicitud = new SolicitudPlanPro(
                $fila['id_solicitud'],
                $usuario,
                $fila['fecha_envio'],
                $fila['mensaje'],
                $fila['comprobante'],
                $fila['estado'],
                $fila['fecha_venc_asignada'],
                $fila['motivo_rechazo']
            );
            $solicitudes[] = $solicitud;   // adentro del while: la agrego al array
        }
        return $solicitudes;               // AFUERA del while: devuelvo todas

    }

    //guardo en la bd una nueva solicitud, y la fecha y el estado (de hoy y pendiente) las pone la base en su valor por defecto
    public function crear(int $idUsuario, string $mensaje, string $comprobante): void{
        $sql = "INSERT INTO solicitud_plan_pro(id_usuario,mensaje,comprobante)
                VALUES (?,?,?)";
        $this->consultar($sql,'iss',[$idUsuario,$mensaje,$comprobante]);
    }

    // true si el usuario ya mando una solicitud q todavia no se hizo nada tipo no se puso rechazada ni aprobada
    public function tienePendiente(int $idUsuario): bool{
        $sql = "SELECT 1 FROM solicitud_plan_pro
                WHERE id_usuario = ? AND estado = 'pendiente'";
        $resultado = $this->consultar($sql, 'i', [$idUsuario]);
        return mysqli_fetch_assoc($resultado) !== null;
    }
    
    //para la tarjetita de las solicitudes en el perfil
    public function obtenerUltimaDeUsuario(int $idUsuario) :?SolicitudPlanPro{
        $sql = self::SELECT_SOLICITUD." WHERE u.id_usuario = ? ORDER BY s.id_solicitud DESC LIMIT 1";
        
        $resultado = $this->consultar($sql,'i',[$idUsuario]);
        $solicitudes = $this->armarSolicitudes($resultado);
        
        if(count($solicitudes)===0){
            return null;
        }
        return $solicitudes[0];
        
    }
        
    // Panel del administradorr: todas las solicitudes con un estado ('pendiente', 'aprobada' o 'rechazad')
    public function obtenerPorEstado(string $estado): array{
        $sql = self::SELECT_SOLICITUD . " WHERE s.estado = ? ORDER BY s.id_solicitud DESC";
        $resultado = $this->consultar($sql, 's', [$estado]);
        return $this->armarSolicitudes($resultado);
    }

    // Una solicitud por su id (para ver el comprobante y para aprobar/rechazar)
    public function buscarPorId(int $idSolicitud): ?SolicitudPlanPro{
        $sql = self::SELECT_SOLICITUD . " WHERE s.id_solicitud = ?";
        $resultado = $this->consultar($sql, 'i', [$idSolicitud]);
        $solicitudes = $this->armarSolicitudes($resultado);

        if (count($solicitudes) === 0) {
            return null;
        }
        return $solicitudes[0];
    }
       
    
    public function guardarResolucion(SolicitudPlanPro $solicitud): void{
        // 1) Actualizo la solicitud con su nuevo estado
        $sql = "UPDATE solicitud_plan_pro
                SET estado = ?, fecha_venc_asignada = ?, motivo_rechazo = ?
                WHERE id_solicitud = ?";
        $this->consultar($sql, 'sssi', [
            $solicitud->getEstado(),
            $solicitud->getFecha_venc_asignada(),
            $solicitud->getMotivo_rechazo(),
            $solicitud->getId_solicitud()
        ]);

        // 2) Si se aprobo, el usuario pasa a pro
        if ($solicitud->getEstado() === 'aprobada') {
            $sql = "UPDATE usuario SET tipo_plan = 'pro', fecha_venc_pro = ? WHERE id_usuario = ?";
            $this->consultar($sql, 'si', [
                $solicitud->getFecha_venc_asignada(),
                $solicitud->getUsuario()->getId_usuario()
            ]);
        }
    }
}

