<?php
require_once __DIR__ . '/../includes/verificar_admin.php';

// Solo se procesa si llega desde el formulario
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../vistas/panel_admin.php');
    exit;
}

// Para no repetir: guarda el error y vuelve al panel
function volverConError(string $mensaje): void {
    $_SESSION['errores_admin'] = [$mensaje];
    header('Location: ../vistas/panel_admin.php');
    exit;
}

require_once __DIR__ . '/../conexiones/conexion.php';
require_once __DIR__ . '/../controles/ControlSolicitud.php';
$controlSolicitud = new ControlSolicitud($conexion);

// 1) Buscar la solicitud: tiene que existir y seguir pendiente
$idSolicitud = (int) ($_POST['id_solicitud'] ?? 0);
$accion      = $_POST['accion'] ?? '';
$solicitud   = $controlSolicitud->buscarPorId($idSolicitud);

if ($solicitud === null || !$solicitud->estaPendiente()) {
    volverConError('La solicitud no existe o ya fue resuelta.');
}

$nombre = $solicitud->getUsuario()->getNombre() . ' ' . $solicitud->getUsuario()->getApellido();


if ($accion === 'aprobar') {
    $fechaVenc = $_POST['fecha_venc'] ?? '';
    $fecha = DateTime::createFromFormat('Y-m-d', $fechaVenc);

    if (!$fecha || $fecha->format('Y-m-d') !== $fechaVenc || $fechaVenc <= date('Y-m-d')) {
        volverConError('La fecha de vencimiento tiene que ser posterior a hoy.');
    }
    $solicitud->aprobar($fechaVenc);
    $mensaje = "Solicitud aprobada: $nombre ya es Pro hasta el " . date('d/m/Y', strtotime($fechaVenc)) . '.';

} elseif ($accion === 'rechazar') {
    $motivo = trim($_POST['motivo'] ?? '');

    if ($motivo === '' || mb_strlen($motivo) > 255) {
        volverConError('Escribí el motivo del rechazo (hasta 255 caracteres).');
    }
    $solicitud->denegar($motivo);
    $mensaje = "Solicitud de $nombre rechazada.";

} else {
    volverConError('Acción no válida.');
}


$controlSolicitud->guardarResolucion($solicitud);

$_SESSION['mensaje_admin'] = $mensaje;
header('Location: ../vistas/panel_admin.php');
exit;