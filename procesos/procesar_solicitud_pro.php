<?php
require_once __DIR__ . '/../includes/verificar_sesion.php';

// 0) Solo se procesa si llega desde el formulario
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../vistas/solicitar_pro.php');
    exit;
}

// 1) Conexión y control
require_once __DIR__ . '/../conexiones/conexion.php';
require_once __DIR__ . '/../controles/ControlSolicitud.php';
$controlSolicitud = new ControlSolicitud($conexion);
$idUsuario = $_SESSION['id_usuario'];

// 2) Si ya tiene una pendiente, no se guarda otra (aunque reenvíe el formulario)
if ($controlSolicitud->tienePendiente($idUsuario)) {
    header('Location: ../vistas/perfil.php');
    exit;
}

// 3) El mensaje
$errores = [];
$mensaje = trim($_POST['mensaje'] ?? '');

if ($mensaje === '' || mb_strlen($mensaje) > 500) {
    $errores[] = 'Escribí un mensaje para el administrador (hasta 500 caracteres).';
}

// 4) El archivo: llegó bien, pesa hasta 2 MB, extensión permitida y contenido real de PDF/imagen
$archivo = $_FILES['comprobante'] ?? null;
$extensionesPermitidas = ['pdf', 'jpg', 'jpeg', 'png'];
$tiposPermitidos       = ['application/pdf', 'image/jpeg', 'image/png'];
$extension = '';

if ($archivo === null || $archivo['error'] !== UPLOAD_ERR_OK) {
    $errores[] = 'Tenés que adjuntar el comprobante (hasta 2 MB).';
} elseif ($archivo['size'] > 2 * 1024 * 1024) {
    $errores[] = 'El comprobante no puede pesar más de 2 MB.';
} else {
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    $tipoReal  = (new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);

    if (!in_array($extension, $extensionesPermitidas) || !in_array($tipoReal, $tiposPermitidos)) {
        $errores[] = 'El comprobante tiene que ser un PDF, JPG o PNG.';
    }
}

// 5) Si hubo errores, vuelvo al formulario con los mensajes
if (count($errores) > 0) {
    $_SESSION['errores_solicitud'] = $errores;
    header('Location: ../vistas/solicitar_pro.php');
    exit;
}

// 6) Guardo el archivo con un nombre nuevo: DNI_hora.extension (ej: 40987654_1794400000.pdf)
$carpeta = __DIR__ . '/../uploads/comprobantes';
if (!is_dir($carpeta)) {
    mkdir($carpeta, 0755, true);
}
$nombreArchivo = $idUsuario . '_' . time() . '.' . $extension;

if (!move_uploaded_file($archivo['tmp_name'], $carpeta . '/' . $nombreArchivo)) {
    $_SESSION['errores_solicitud'] = ['No se pudo guardar el comprobante. Probá de nuevo.'];
    header('Location: ../vistas/solicitar_pro.php');
    exit;
}

// 7) Guardo la solicitud (en la base va la ruta relativa al proyecto, no la del disco)
$controlSolicitud->crear($idUsuario, $mensaje, 'uploads/comprobantes/' . $nombreArchivo);

// 8) Aviso para mostrar en el perfil y vuelvo
$_SESSION['aviso_perfil'] = '¡Tu solicitud fue enviada! Te avisamos acá cuando el administrador la revise.';
header('Location: ../vistas/perfil.php');
exit;
