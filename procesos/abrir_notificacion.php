<?php
require_once __DIR__ . '/../includes/verificar_sesion.php';
require_once __DIR__ . '/../conexiones/conexion.php';
require_once __DIR__ . '/../controles/ControlNotificacion.php';

$idNotificacion = (int) ($_GET['id'] ?? 0);

$controlNotificacion = new ControlNotificacion($conexion);
$notificacion = $controlNotificacion->buscarDeUsuario($idNotificacion, $_SESSION['id_usuario']);

//si no existe o es de otra persona, vuelvo a la lista
if ($notificacion === null) {
    header('Location: ../vistas/notificaciones.php');
    exit;
}

$controlNotificacion->marcarLeida($idNotificacion, $_SESSION['id_usuario']);

//si la pregunta todavia existe voy a la pregunta, si la eliminaron voy al foro del curso
if ($notificacion->getId_pregunta() !== null) {
    header('Location: ../vistas/pregunta.php?id=' . $notificacion->getId_pregunta() . '&curso=' . $notificacion->getId_curso());
} else {
    header('Location: ../vistas/foro.php?id=' . $notificacion->getId_curso());
}
exit;