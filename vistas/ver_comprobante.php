<?php
require_once __DIR__ . '/../includes/verificar_admin.php';
require_once __DIR__ . '/../conexiones/conexion.php';
require_once __DIR__ . '/../controles/ControlSolicitud.php';

$idSolicitud = (int) ($_GET['id'] ?? 0);

$controlSolicitud = new ControlSolicitud($conexion);
$solicitud = $controlSolicitud->buscarPorId($idSolicitud);

$ruta = $solicitud !== null ? __DIR__ . '/../' . $solicitud->getComprobante() : '';

if ($solicitud === null || !is_file($ruta)) {
    http_response_code(404);
    exit('No se encontró el comprobante.');
}

// Le decimos al navegador qué tipo de archivo es (PDF o imagen) y se lo mandamos
header('Content-Type: ' . mime_content_type($ruta));
header('Content-Length: ' . filesize($ruta));
readfile($ruta);
exit;